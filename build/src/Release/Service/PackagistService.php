<?php

declare(strict_types=1);

namespace Build\Release\Service;

use Build\Release\Config;
use GuzzleHttp\Client as HttpClient;
use Packagist\Api\Client;

class PackagistService
{
    private Client $api;

    public function __construct()
    {
        $this->api = new Client(httpClient: new HttpClient(['verify' => false]));
    }

    public function getReference(GithubRelease $release): ?string
    {
        $packageName = Config::COMPOSER_PACKAGES[$release->repository];
        $package = $this->api->getComposerReleases($packageName)[$packageName];
        $versions = $package->getVersions();

        $version = $versions[$release->version->getTag()] ?? null;

        return $version?->getDist()?->getReference();
    }
}
