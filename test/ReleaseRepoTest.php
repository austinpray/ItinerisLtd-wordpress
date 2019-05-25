<?php

namespace Composer\Itineris\WordPress;

use PHPUnit\Framework\TestCase;

class ReleaseRepoTest extends TestCase
{

    public function testFromReleasesPage(): void
    {
        /** @var ReleaseRepo[] $repos */
        $repos = array_map(
            function (string $path): ReleaseRepo {
                return ReleaseRepo::fromReleasesPage(file_get_contents($path));
            },
            [
                __DIR__ . '/resources/repo-fail-1.html',
                __DIR__ . '/resources/repo-fail-2.html',
                __DIR__ . '/resources/repo-pass-1.html',
                __DIR__ . '/resources/repo-pass-2.html',
            ]
        );
        [$fail1, $fail2, $pass1, $pass2] = $repos;

        $this->assertEquals(0, $fail1->count());
        $this->assertEquals(0, $fail2->count());

        $this->assertEquals(1, $pass2->count());
        $this->assertEquals('5.2.1', $pass2->getLatestStableVersion()->getVersion());
        $this->assertEquals(
            'https://wordpress.org/wordpress-5.2.1.zip',
            $pass2->getLatestStableVersion()->getDistUrl()
        );

        $validUrls = json_decode(
            file_get_contents(__DIR__ . '/resources/wordpress-valid-download-urls.json'),
            true
        );

        $pass1 = $pass1->filter(function (Release $r) {
            return $r->greaterThanOrEqualTo('4');
        });

        $releaseUrls = $pass1->getReleases()
            ->map(function (Release $r) {
                return $r->getDistUrl();
            })
            ->toArray();

        $this->assertEqualsCanonicalizing($validUrls, $releaseUrls);

        $this->assertEquals('5.2.1', $pass1->getLatestStableVersion()->getVersion());

    }
}
