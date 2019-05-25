<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnexpectedValueException;

class Release implements \JsonSerializable
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $distUrl;
    /** @var string */
    protected $version;
    /** @var string */
    protected $distType;

    public function __construct(string $name, string $distType, string $distUrl, string $version)
    {
        $this->name = $name;
        $this->distType = $distType;
        $this->distUrl = $distUrl;
        $this->version = $version;
    }

    protected static function getVersionFromBasename(string $basename): ?string
    {
        if (!preg_match('/^wordpress-(?<version>\S+)\.zip$/', $basename, $matches)) {
            return null;
        }
        [,$version] = $matches;

        return $version;
    }

    protected static function getVersionFromUrl(string $url): ?string
    {
        $httpUrl = Collection::make(parse_url($url));
        return self::getVersionFromBasename(self::getBasename($httpUrl));
    }

    protected static function getBasename(Collection $urlParts): string
    {
        return Collection::make(explode('/', $urlParts->get('path', '')))->last();
    }

    public static function isValidReleaseUrl(string $url): bool
    {
        $httpUrl = Collection::make(parse_url($url));

        if ($httpUrl->get('scheme') !== 'https') {
            return false;
        }
        if ($httpUrl->get('host') !== 'wordpress.org') {
            return false;
        }

        $basename = self::getBasename($httpUrl);

        if (!$basename) {
            return false;
        }

        // whitelist
        if (!Str::startsWith($basename, 'wordpress-')) {
            return false;
        }
        if (!Str::endsWith($basename, '.zip')) {
            return false;
        }
        // blacklist
        if (Str::startsWith($basename, 'wordpress-mu')) {
            return false;
        }
        if (Str::endsWith($basename, ['-IIS.zip'])) {
            return false;
        }

        $version = self::getVersionFromBasename($basename);

        return $version && self::isVersion($version);
    }

    public static function parse(string $url): ?self
    {
        if (!self::isValidReleaseUrl($url)) {
            return null;
        }
        return new static(
            'itinerisltd/wordpress',
            'zip',
            $url,
            self::getVersionFromUrl($url)
        );
    }

    protected static function isVersion(string $string): bool
    {
        try {
            return (bool)(new VersionParser())->normalize($string);
        } catch (UnexpectedValueException $exception) {
            return false;
        }
    }

    public function jsonSerialize(): object
    {
        return (object) [
            'name' => $this->name,
            'version' => $this->version,
            'dist' => (object) [
                'url' => $this->distUrl,
                'type' => $this->distType,
            ],
            'require' => (object) [
                'roots/wordpress-core-installer' => '>=1.0.0',
            ],
            'type' => 'wordpress-core',
            'description' => 'WordPress is web software you can use to create a beautiful website or blog.',
            'keywords' => (object) [
                'wordpress',
                'blog',
                'cms',
            ],
            'homepage' => 'http://wordpress.org/',
            'license' => 'GPL-2.0-or-later',
            'authors' => [
                (object) [
                    'name' => 'WordPress Community',
                    'homepage' => 'http://wordpress.org/about/',
                ],
            ],
            'support' => (object) [
                'issues' => 'http://core.trac.wordpress.org/',
                'forum' => 'http://wordpress.org/support/',
                'wiki' => 'http://codex.wordpress.org/',
                'irc' => 'irc://irc.freenode.net/wordpress',
                'source' => 'http://core.trac.wordpress.org/browser',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    public function greaterThanOrEqualTo(string $version): bool
    {
        return Comparator::greaterThanOrEqualTo($this->version, $version);
    }

    /**
     * @return string
     */
    public function getDistUrl(): string
    {
        return $this->distUrl;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDistType(): string
    {
        return $this->distType;
    }
}
