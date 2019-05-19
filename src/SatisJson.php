<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use UnexpectedValueException;

class SatisJson
{
    /** @var ReleaseRepo */
    protected $releaseRepo;
    /** @var Filesystem */
    protected $filesystem;

    public function __construct(ReleaseRepo $releaseRepo, Filesystem $filesystem)
    {
        $this->releaseRepo = $releaseRepo;
        $this->filesystem = $filesystem;
    }

    public function generate(string $destinationPath, string $templatePath): void
    {
        $json = file_get_contents($templatePath);
        if (! is_string($json)) {
            throw new UnexpectedValueException('Failed to read ' . $templatePath);
        }

        $satis = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $packages = array_map(function (Release $release): array {
            return $release->toPackageArray();
        }, $this->releaseRepo->all());

        $satis['repositories'][] = [
            'type' => 'package',
            'package' => $packages,
        ];

        $content = json_encode($satis, JSON_PRETTY_PRINT);
        if (false === $content) {
            throw new RuntimeException('Failed to encode satis json');
        }

        $this->filesystem->dumpFile($destinationPath, $content);
    }
}
