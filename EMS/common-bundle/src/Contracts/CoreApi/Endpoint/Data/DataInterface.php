<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Data\Index;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface DataInterface
{
    public const int MODE_UPDATE = 1;
    public const int MODE_REPLACE = 2;

    /** @param array<string, mixed> $rawData */
    public function autoSave(int $revisionId, array $rawData): bool;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function create(array $rawData, ?string $ouuid = null): DraftInterface;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function delete(string $ouuid): bool;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function discard(int $revisionId): bool;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function finalize(int $revisionId, array $rawData = []): string;

    /** @return array{'id': int, 'data': array<string, mixed>} */
    public function getDraft(int $revisionId): array;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function index(?string $ouuid, array $rawData, bool $merge = false, bool $refresh = false): Index;

    /**
     * @param array<string, mixed> $rawData
     */
    public function indexAsync(?string $ouuid, array $rawData, bool $merge = false, bool $refresh = false): ResponseInterface;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function get(string $ouuid): RevisionInterface;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function head(string $ouuid): bool;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function replace(string $ouuid, array $rawData): DraftInterface;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function update(string $ouuid, array $rawData): DraftInterface;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function save(string $ouuid, array $rawData, int $mode = self::MODE_UPDATE, bool $discardDraft = true): int;

    public function publish(string $ouuid, string $environment, ?string $revisionId = null): bool;
}
