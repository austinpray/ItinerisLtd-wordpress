<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use Illuminate\Support\Collection;
use RuntimeException;
use Composer\Semver;
use Traversable;

/**
 * Class ReleaseRepo
 * @package Composer\Itineris\WordPress
 */
class ReleaseRepo implements \IteratorAggregate, \JsonSerializable
{
    /**
     * @var Collection
     */
    protected $releases;

    /**
     * ReleaseRepo constructor.
     * @param Release[] $releases
     */
    public function __construct(array $releases)
    {
        $this->setReleases($releases);
    }

    /**
     * @param Release[] $releases
     */
    public function setReleases(array $releases): void
    {
        $this->releases = Collection::make($releases)
            ->sort(function (Release $a, Release $b) {
                $aVer = $a->getVersion();
                $bVer = $b->getVersion();
                if (Semver\Comparator::lessThan($aVer, $bVer)) {
                    return -1;
                }
                if (Semver\Comparator::equalTo($aVer, $bVer)) {
                    return 0;
                }
                if (Semver\Comparator::greaterThan($aVer, $bVer)) {
                    return 1;
                }

                throw new RuntimeException('unable to sort versions');
            })
            ->values();
    }

    public function filter(callable $fn): self
    {
        return new self($this->releases->filter($fn)->toArray());
    }

    public function count(): int
    {
        return $this->releases->count();
    }

    public function isEmpty(): bool
    {
        return $this->releases->isEmpty();
    }

    public function getLatestStableVersion(): Release
    {
        return $this->releases
            ->filter(function (Release $r) {
                return Semver\VersionParser::parseStability($r->getVersion()) === 'stable';
            })
            ->last();
    }

    public function getLatestVersion(): Release
    {
        return $this->releases->last();
    }

    /**
     * @param string $html
     * @return ReleaseRepo
     */
    public static function fromReleasesPage(string $html): ReleaseRepo
    {
        if (!$html) {
            throw new \InvalidArgumentException('blank html');
        }

        $dom = new \DOMDocument('1.0', 'UTF-8'); // infers encoding from html but set encoding for safety
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);


        $zipLinks = $dom->getElementsByTagName('a');
        if ($zipLinks->length < 1) {
            return new self([]);
        }

        return new self(
            Collection::make($zipLinks)
                ->map(function ($zipLink): ?string {
                    if ($zipLink instanceof \DOMElement) {
                        $href = $zipLink->getAttribute('href');
                        if (Release::isValidReleaseUrl($href)) {
                            return $href;
                        }
                    }
                    return null;
                })
                ->filter()
                ->unique()
                ->map(function (string $releaseUrl) {
                    return Release::parse($releaseUrl);
                })
                ->toArray()
        );
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->releases
            ->map(function (Release $r) {
                return $r->jsonSerialize();
            })
            ->toArray();
    }

    /**
     * @return Collection
     */
    public function getReleases(): Collection
    {
        return $this->releases;
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return $this->getReleases();
    }

}
