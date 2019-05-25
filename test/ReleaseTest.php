<?php

namespace Composer\Itineris\WordPress;

use PHPUnit\Framework\TestCase;

class ReleaseTest extends TestCase
{

    public function testIsValidReleaseUrl(): void
    {
        $this->assertFalse(Release::isValidReleaseUrl('https://www.itineris.co.uk/'));
        $this->assertFalse(Release::isValidReleaseUrl('https://wordpress.org/'));
        $this->assertFalse(Release::isValidReleaseUrl('https://wordpress.org/completely-unrelated.zip'));

        // force http
        $this->assertFalse(Release::isValidReleaseUrl('http://wordpress.org/wordpress-5.2.1.zip'));

        $this->assertTrue(Release::isValidReleaseUrl('https://wordpress.org/wordpress-5.2.1.zip'));
        $this->assertTrue(Release::isValidReleaseUrl('https://wordpress.org/future/proof/wordpress-5.2.1.zip'));

        // smoke test
        $everything = json_decode(
            file_get_contents(__DIR__ . '/resources/wordpress-download-urls.json'),
            true
        );
        foreach($everything as $url) {
            Release::isValidReleaseUrl($url);
        }

        $validUrls = json_decode(
            file_get_contents(__DIR__ . '/resources/wordpress-valid-download-urls.json'),
            true
        );
        foreach($validUrls as $valid) {
            $this->assertTrue(
                Release::isValidReleaseUrl($valid),
                "expected '$valid' to be valid"
            );
        }

        $invalidUrls = json_decode(
            file_get_contents(__DIR__ . '/resources/wordpress-invalid-download-urls.json'),
            true
        );
        foreach($invalidUrls as $invalid) {
            $this->assertFalse(
                Release::isValidReleaseUrl($invalid),
                "expected '$invalid' to be invalid"
            );
        }
    }
}
