<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Common\CoreApi\Exception\BaseUrlNotDefinedException;
use EMS\CommonBundle\Common\CoreApi\Exception\NotAuthenticatedException;
use EMS\CommonBundle\Common\CoreApi\Exception\NotSuccessfulException;
use EMS\Helpers\File\File;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Response\StreamableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Client
{
    /** @var array<string, string> */
    private array $headers = [];
    private ?string $baseUrl = null;
    private LoggerInterface $logger;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function getBaseUrl(): string
    {
        if ('' === $this->baseUrl || null === $this->baseUrl) {
            throw new BaseUrlNotDefinedException();
        }

        return $this->baseUrl;
    }

    public function getHeader(string $name): string
    {
        return $this->headers[$name];
    }

    /**
     * @param array<mixed> $options
     */
    public function asyncRequest(string $method, string $resource, array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, $resource, [
            ...['headers' => $this->headers, 'base_uri' => $this->getBaseUrl()],
            ...$options,
        ]);
    }

    /**
     * @param array<mixed> $query
     */
    public function get(string $resource, array $query = []): Result
    {
        return $this->getResult(Request::METHOD_GET, $resource, ['query' => $query]);
    }

    /**
     * @param array<mixed> $query
     */
    public function download(string $resource, array $query = []): StreamInterface
    {
        $response = $this->request(Request::METHOD_GET, $resource, ['query' => $query]);

        if (!$response instanceof StreamableInterface) {
            throw new \RuntimeException('no stream response');
        }

        return new Stream($response->toStream());
    }

    /**
     * @param array<mixed> $query
     */
    public function getResponse(string $resource, array $query = []): ResponseInterface
    {
        return $this->request(Request::METHOD_GET, $resource, ['query' => $query]);
    }

    /**
     * @param array<mixed> $query
     */
    public function forwardResponse(string $resource, array $query = []): StreamedResponse
    {
        $request = $this->get($resource, $query);
        $response = $request->response;
        if (!$response instanceof StreamableInterface) {
            throw new \RuntimeException('no stream response');
        }
        $stream = $response->toStream();
        $streamResponse = new StreamedResponse(function () use ($stream) {
            while (!\feof($stream)) {
                echo \fread($stream, File::DEFAULT_CHUNK_SIZE);
                \flush();
            }
            \fclose($stream);
        }, $request->response->getStatusCode());

        $headers = $request->response->getHeaders();

        $streamResponse->headers->set('Content-Type', $headers['content-type']);
        $streamResponse->headers->set('Content-Disposition', $headers['content-disposition']);

        return $streamResponse;
    }

    /**
     * @param array<string|int, mixed> $body
     * @param array<string, mixed>     $options
     */
    public function post(string $resource, array $body = [], array $options = []): Result
    {
        return $this->getResult(Request::METHOD_POST, $resource, [...$options, ...['json' => $body]]);
    }

    public function delete(string $resource): Result
    {
        return $this->getResult(Request::METHOD_DELETE, $resource);
    }

    public function head(string $resource): bool
    {
        return 200 === $this->asyncRequest(Request::METHOD_HEAD, $resource)->getStatusCode();
    }

    public function postBody(string $resource, string $body): Result
    {
        return $this->getResult(Request::METHOD_POST, $resource, ['body' => $body]);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;

        if ($this->httpClient instanceof LoggerAwareInterface) {
            $this->httpClient->setLogger($logger);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function request(string $method, string $resource, array $options): ResponseInterface
    {
        $response = $this->asyncRequest($method, $resource, $options);

        if (Response::HTTP_UNAUTHORIZED === $response->getStatusCode()) {
            throw new NotAuthenticatedException($response);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getResult(string $method, string $resource, array $options = []): Result
    {
        $response = $this->request($method, $resource, $options);
        $result = new Result($response, $this->logger);

        if (!$result->isSuccess()) {
            throw new NotSuccessfulException($result);
        }

        return $result;
    }
}
