<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use Composer\Package\Version\VersionParser;
use UnexpectedValueException;

class Release
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

    public static function parse(string $url): ?self
    {
        $versionParser = new VersionParser();

        preg_match('/\S+\/wordpress-(?<version>\S+)\.zip/', $url, $matches);
        $version = (string) ($matches['version'] ?? '');

        return static::isVersion($version, $versionParser)
            ? new static('itinerisltd/wordpress', 'zip', $url, $version)
            : null;
    }

    protected static function isVersion(string $string, VersionParser $versionParser): bool
    {
        try {
            $versionParser->normalize($string);

            return true;
        } catch (UnexpectedValueException $exception) {
            return false;
        }
    }

    public function toPackageArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'dist' => [
                'url' => $this->distUrl,
                'type' => $this->distType,
            ],
            'require' => [
                'roots/wordpress-core-installer' => '>=1.0.0',
            ],
            'type' => 'wordpress-core',
            'description' => 'WordPress is web software you can use to create a beautiful website or blog.',
            'keywords' => [
                'wordpress',
                'blog',
                'cms',
            ],
            'homepage' => 'http://wordpress.org/',
            'license' => 'GPL-2.0-or-later',
            'authors' => [
                [
                    'name' => 'WordPress Community',
                    'homepage' => 'http://wordpress.org/about/',
                ],
            ],
            'support' => [
                'issues' => 'http://core.trac.wordpress.org/',
                'forum' => 'http://wordpress.org/support/',
                'wiki' => 'http://codex.wordpress.org/',
                'irc' => 'irc://irc.freenode.net/wordpress',
                'source' => 'http://core.trac.wordpress.org/browser',
            ],
        ];
    }
}
