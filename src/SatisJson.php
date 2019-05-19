<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use Symfony\Component\Filesystem\Filesystem;

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
        $satis = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $packages = array_map(function (Release $release): array {
            return $release->toPackageArray();
        }, $this->releaseRepo->all());

        $satis['repositories'][] = [
            'type' => 'package',
            'package' => $packages,
        ];

        $this->filesystem->dumpFile(
            $destinationPath,
            json_encode($satis, JSON_PRETTY_PRINT)
        );
    }
}
