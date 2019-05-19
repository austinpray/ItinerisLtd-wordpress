<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use RuntimeException;

class ReleaseRepo
{
    protected const RELEASES_URL = 'https://wordpress.org/download/releases/';
    protected const KNOWN_RELEASES = 304; // As of 18 May 2019.

    public function all(): array
    {
        $html = file_get_contents(static::RELEASES_URL);
        if (false === $html) {
            throw new RuntimeException('Failed to download ' . static::RELEASES_URL);
        }

        preg_match_all(
            '/<a[^>]*href="(?<releaseUrl>https:\/\/[\S]+\/wordpress-[4-9]\S+[^IIS]\.zip)\.sha1"[^>]*>/',
            $html,
            $matches
        );
        $releaseUrls = $matches['releaseUrl'] ?? [];

        static::failIfReleaseUrlsNotFound($releaseUrls);

        $releases = array_map(function (string $releaseUrl): ?Release {
            return Release::parse($releaseUrl);
        }, $releaseUrls);
        $releases = array_filter($releases);

        $this->failIfReleaseCannotBeParsed($releases);

        return $releases;
    }

    protected static function failIfReleaseUrlsNotFound(array $urls): void
    {
        if (count($urls) >= static::KNOWN_RELEASES) {
            return;
        }

        $message = sprintf(
            'Only %1$d release URL(s) found on %2$s',
            count($urls),
            static::RELEASES_URL
        );
        throw new RuntimeException($message);
    }

    /**
     * @param array $releases
     */
    protected function failIfReleaseCannotBeParsed(array $releases): void
    {
        if (count($releases) >= static::KNOWN_RELEASES) {
            return;
        }

        $message = sprintf(
            'Only %1$d release(s) parsed from %2$s',
            count($releases),
            static::RELEASES_URL
        );
        throw new RuntimeException($message);
    }
}
