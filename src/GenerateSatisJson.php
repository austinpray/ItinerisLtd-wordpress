<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use Symfony\Component\Filesystem\Filesystem;

class GenerateSatisJson
{
    public static function run(): void
    {
        $repo = new ReleaseRepo();
        $filesystem = new Filesystem();
        $satisJson = new SatisJson($repo, $filesystem);

        $satisJson->generate(
            'satis.json',
            'satis.base.json'
        );
    }
}
