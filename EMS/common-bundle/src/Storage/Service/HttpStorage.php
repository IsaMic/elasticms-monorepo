<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use EMS\CommonBundle\Common\HttpClientFactory;
use EMS\Helpers\Standard\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpStorage extends AbstractUrlStorage
{
    final public const string INIT_URL = '/api/file/init-upload';

    public function __construct(LoggerInterface $logger, private readonly string $baseUrl, private readonly string $getUrl, int $usage, private readonly ?string $authKey = null, int $hotSynchronizeLimit = 0)
    {
        parent::__construct($logger, $usage, $hotSynchronizeLimit);
    }

    public static function addChunkUrl(string $hash): string
    {
        return '/api/file/upload-chunk/'.\urlencode($hash);
    }

    /**
     * @return array<string, int|string>
     */
    public static function initBody(string $hash, int $size, string $name, string $type): array
    {
        return [
            'name' => $name,
            'type' => $type,
            'hash' => $hash,
            'size' => $size,
        ];
    }

    private function getClient(): Client
    {
        static $client = null;
        if (null === $client) {
            $client = HttpClientFactory::create($this->baseUrl, [], 30, true);
        }

        return $client;
    }

    #[\Override]
    protected function getBaseUrl(): string
    {
        return $this->baseUrl.$this->getUrl;
    }

    #[\Override]
    protected function getPath(string $hash, string $ds = '/'): string
    {
        return $this->baseUrl.$this->getUrl.$hash;
    }

    #[\Override]
    public function health(): bool
    {
        try {
            $result = $this->getClient()->get('/status.json');
            if (200 == $result->getStatusCode()) {
                $status = Json::decode($result->getBody()->getContents());
                if (isset($status['status']) && \in_array($status['status'], ['green', 'yellow'])) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable) {
            return false;
        }
    }

    #[\Override]
    public function read(string $hash, bool $confirmed = true): StreamInterface
    {
        try {
            return $this->getClient()->get($this->getUrl.$hash)->getBody();
        } catch (\Throwable) {
            throw new NotFoundHttpException($hash);
        }
    }

    #[\Override]
    public function initUpload(string $hash, int $size, string $name, string $type): bool
    {
        try {
            $result = $this->getClient()->post(self::INIT_URL, [
                'headers' => [
                    'X-Auth-Token' => $this->authKey,
                ],
                'body' => self::initBody($hash, $size, $name, $type),
            ]);

            return 200 === $result->getStatusCode();
        } catch (\Throwable) {
            throw new NotFoundHttpException($hash);
        }
    }

    #[\Override]
    public function addChunk(string $hash, string $chunk): bool
    {
        try {
            $result = $this->getClient()->post(self::addChunkUrl($hash), [
                'headers' => [
                    'X-Auth-Token' => $this->authKey,
                ],
                'body' => $chunk,
            ]);

            return 200 === $result->getStatusCode();
        } catch (\Throwable) {
            throw new NotFoundHttpException($hash);
        }
    }

    #[\Override]
    public function finalizeUpload(string $hash): bool
    {
        return $this->head($hash);
    }

    #[\Override]
    public function head(string $hash): bool
    {
        try {
            return Response::HTTP_OK === $this->getClient()->head($this->getUrl.$hash)->getStatusCode();
        } catch (\Throwable $e) {
            if (Response::HTTP_NOT_FOUND === $e->getCode()) {
                return false;
            }
            throw $e;
        }
    }

    #[\Override]
    public function create(string $hash, string $filename): bool
    {
        try {
            $this->getClient()->request('POST', '/api/file', [
                'multipart' => [
                    [
                        'name' => 'upload',
                        'contents' => \fopen($filename, 'r'),
                    ],
                ],
                'headers' => [
                    'X-Auth-Token' => $this->authKey,
                ],
            ]);

            return true;
        } catch (GuzzleException) {
            return false;
        }
    }

    #[\Override]
    public function getSize(string $hash): int
    {
        try {
            $context = \stream_context_create(['http' => ['method' => 'HEAD']]);
            $fd = \fopen($this->baseUrl.$this->getUrl.$hash, 'rb', false, $context);

            if (false === $fd) {
                throw new NotFoundHttpException($hash);
            }

            $metas = \stream_get_meta_data($fd);
            foreach ($metas['wrapper_data'] ?? [] as $meta) {
                if (\preg_match('/^content\-length: (.*)$/i', (string) $meta, $matches, PREG_OFFSET_CAPTURE)) {
                    return (int) $matches[1][0];
                }
            }
        } catch (\Throwable) {
        }
        throw new NotFoundHttpException($hash);
    }

    #[\Override]
    public function __toString(): string
    {
        return HttpStorage::class." ($this->baseUrl)";
    }

    #[\Override]
    public function remove(string $hash): bool
    {
        return false;
    }

    #[\Override]
    public function removeUpload(string $hash): void
    {
    }

    /**
     * @return null
     */
    #[\Override]
    protected function getContext()
    {
        return null;
    }
}
