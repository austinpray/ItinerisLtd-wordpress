<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use Symfony\Component\Filesystem\Filesystem;

class GenerateSatisJson
{
    public static function run(): void
    {
        $html = file_get_contents('https://wordpress.org/download/releases/');
        $repo = ReleaseRepo::fromReleasesPage($html);
        if ($repo->isEmpty()) {
            throw new \RuntimeException('no releases in repo');
        }
        $repo = $repo->filter(function (Release $r) {
            return $r->greaterThanOrEqualTo('4');
        });
        $filesystem = new Filesystem();
        $satisJson = new SatisJson($repo, $filesystem);

        $satisJson->generate(
            'satis.json',
            'satis.base.json'
        );
    }
}
