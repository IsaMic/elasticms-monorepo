<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Service;

use EMS\CommonBundle\Entity\EntityInterface;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\Release;
use EMS\CoreBundle\Entity\ReleaseRevision;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Event\RevisionFinalizeDraftEvent;
use EMS\CoreBundle\Repository\ReleaseRevisionRepository;
use EMS\CoreBundle\Repository\RevisionRepository;
use Psr\Log\LoggerInterface;

final readonly class ReleaseRevisionService implements QueryServiceInterface, EntityServiceInterface
{
    public function __construct(
        private ReleaseRevisionRepository $releaseRevisionRepository,
        private RevisionRepository $revisionRepository,
        private LoggerInterface $logger,
        private ContentTypeService $contentTypeService,
    ) {
    }

    #[\Override]
    public function isSortable(): bool
    {
        return false;
    }

    public function remove(ReleaseRevision $releaseRevision): void
    {
        $this->releaseRevisionRepository->delete($releaseRevision);
    }

    /**
     * @param mixed $context
     *
     * @return Revision[]
     */
    #[\Override]
    public function query(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, $context = null): array
    {
        if (!$context instanceof Release) {
            throw new \RuntimeException('Unexpected release object');
        }
        $contentTypes = $this->contentTypeService->getAllGrantedForPublication();
        $contentTypeNames = \array_map(static fn (ContentType $contentType) => $contentType->getName(), $contentTypes);

        return $this->revisionRepository->getAvailableRevisionsForRelease($from, $size, $context, $contentTypeNames, $orderField, $orderDirection, $searchValue);
    }

    #[\Override]
    public function getEntityName(): string
    {
        return 'release_revision';
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getAliasesName(): array
    {
        return [];
    }

    /**
     * @param mixed $context
     */
    #[\Override]
    public function countQuery(string $searchValue = '', $context = null): int
    {
        if (!$context instanceof Release) {
            throw new \RuntimeException('Unexpected release object');
        }
        $contentTypes = $this->contentTypeService->getAllGrantedForPublication();
        $contentTypeNames = \array_map(static fn (ContentType $contentType) => $contentType->getName(), $contentTypes);

        return $this->revisionRepository->countAvailableRevisionsForRelease($context, $contentTypeNames, $searchValue);
    }

    public function findToRemove(Release $release, string $ouuid, ContentType $contentType): ReleaseRevision
    {
        return $this->releaseRevisionRepository->findByReleaseByRevisionOuuidAndContentType($release, $ouuid, $contentType);
    }

    public function finalizeDraftEvent(RevisionFinalizeDraftEvent $event): void
    {
        $revision = $event->getRevision();
        $releaseRevisions = $this->releaseRevisionRepository->getRevisionsLinkedToReleasesByOuuid($revision->giveOuuid(), $revision->giveContentType());
        foreach ($releaseRevisions as $releaseRevision) {
            $this->logger->warning('log.service.release_revision.preceding.revision.in.release', [
                'name' => $releaseRevision->getRelease()->getName(),
            ]);
        }
    }

    #[\Override]
    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, mixed $context = null): array
    {
        if (!$context instanceof Release) {
            throw new \RuntimeException('Unexpected non Release context');
        }

        return $this->releaseRevisionRepository->findByRelease($context, $from, $size, $orderField, $orderDirection, $searchValue);
    }

    #[\Override]
    public function count(string $searchValue = '', mixed $context = null): int
    {
        if (!$context instanceof Release) {
            throw new \RuntimeException('Unexpected non Release context');
        }

        return $this->releaseRevisionRepository->countByRelease($context, $searchValue);
    }

    /**
     * @param string[] $ids
     *
     * @return ReleaseRevision[]
     */
    public function getByIds(array $ids): array
    {
        return $this->releaseRevisionRepository->getByIds($ids);
    }

    #[\Override]
    public function getByItemName(string $name): EntityInterface
    {
        return $this->releaseRevisionRepository->getById($name);
    }

    #[\Override]
    public function updateEntityFromJson(EntityInterface $entity, string $json): EntityInterface
    {
        throw new \RuntimeException('updateEntityFromJson method not yet implemented');
    }

    #[\Override]
    public function createEntityFromJson(string $json, ?string $name = null): EntityInterface
    {
        throw new \RuntimeException('createEntityFromJson method not yet implemented');
    }

    #[\Override]
    public function deleteByItemName(string $name): string
    {
        throw new \RuntimeException('deleteByItemName method not yet implemented');
    }
}
