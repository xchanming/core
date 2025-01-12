<?php declare(strict_types=1);

namespace Cicada\Core\Content\Media;

use Cicada\Core\Content\Media\Event\UnusedMediaSearchEvent;
use Cicada\Core\Content\Media\Event\UnusedMediaSearchStartEvent;
use Cicada\Core\Defaults;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Cicada\Core\Framework\Log\Package;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @final
 */
#[Package('discovery')]
class UnusedMediaPurger
{
    private const VALID_ASSOCIATIONS = [
        ManyToManyAssociationField::class,
        OneToManyAssociationField::class,
        OneToOneAssociationField::class,
    ];

    /**
     * @internal
     *
     * @param EntityRepository<MediaCollection> $mediaRepo
     */
    public function __construct(
        private readonly EntityRepository $mediaRepo,
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @internal This method is used only by the media:delete-unused command and is subject to change
     *
     * @return \Generator<array<MediaEntity>>
     */
    public function getNotUsedMedia(?int $limit = 50, ?int $offset = null, ?int $gracePeriodDays = null, ?string $folderEntity = null): \Generator
    {
        $limit ??= 50;
        $gracePeriodDays ??= 0;

        $context = Context::createDefaultContext();

        $criteria = $this->createFilterForNotUsedMedia($folderEntity);
        $criteria->addSorting(new FieldSorting('media.createdAt', FieldSorting::ASCENDING));
        $criteria->setLimit($limit);

        // if we provided an offset, then just grab that batch based on the limit
        if ($offset !== null) {
            $criteria->setOffset($offset);

            /** @var array<string> $ids */
            $ids = $this->mediaRepo->searchIds($criteria, $context)->getIds();
            $ids = $this->filterOutNewMedia($ids, $gracePeriodDays, $context);
            $ids = $this->dispatchEvent($ids);

            return yield $this->searchMedia($ids, $context);
        }

        // otherwise, we need to iterate over the entire result set in batches
        $iterator = new RepositoryIterator($this->mediaRepo, $context, $criteria);
        while (($ids = $iterator->fetchIds()) !== null) {
            $ids = $this->filterOutNewMedia($ids, $gracePeriodDays, $context);
            $unusedIds = $this->dispatchEvent($ids);

            if (empty($unusedIds)) {
                continue;
            }

            yield $this->searchMedia($unusedIds, $context);
        }
    }

    public function deleteNotUsedMedia(
        ?int $limit = 50,
        ?int $offset = null,
        ?int $gracePeriodDays = null,
        ?string $folderEntity = null,
    ): int {
        $limit ??= 50;
        $gracePeriodDays ??= 0;

        $context = Context::createDefaultContext();

        $totalMedia = $this->getTotal(new Criteria(), $context);
        $totalCandidates = $this->getTotal($this->createFilterForNotUsedMedia($folderEntity), $context);

        $this->eventDispatcher->dispatch(new UnusedMediaSearchStartEvent($totalMedia, $totalCandidates));

        $idsToDelete = [];
        foreach ($this->getUnusedMediaIds($context, $limit, $offset, $folderEntity) as $idBatch) {
            $idBatch = $this->filterOutNewMedia($idBatch, $gracePeriodDays, $context);

            $idsToDelete = [...$idsToDelete, ...$idBatch];
        }

        if (!empty($idsToDelete)) {
            $this->mediaRepo->delete(
                array_map(static fn ($id) => ['id' => $id], $idsToDelete),
                $context
            );
        }

        return \count($idsToDelete);
    }

    /**
     * @param array<string> $ids
     *
     * @return array<MediaEntity>
     */
    public function searchMedia(array $ids, Context $context): array
    {
        $media = $this->mediaRepo->search(new Criteria($ids), $context)->getEntities()->getElements();

        return array_values($media);
    }

    private function getTotal(Criteria $criteria, Context $context): int
    {
        $criteria->setLimit(1);
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        return $this->mediaRepo->search($criteria, $context)->getTotal();
    }

    /**
     * @param array<string> $mediaIds
     *
     * @return array<string>
     */
    private function filterOutNewMedia(array $mediaIds, int $gracePeriodDays, Context $context): array
    {
        if ($gracePeriodDays === 0) {
            return $mediaIds;
        }

        $threeDaysAgo = (new \DateTime())->sub(new \DateInterval(\sprintf('P%dD', $gracePeriodDays)));
        $rangeFilter = new RangeFilter('uploadedAt', ['lt' => $threeDaysAgo->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $criteria = new Criteria($mediaIds);
        $criteria->addFilter($rangeFilter);

        /** @var array<string> $ids */
        $ids = $this->mediaRepo->searchIds($criteria, $context)->getIds();

        return $ids;
    }

    /**
     * @return \Generator<int, array<string>>
     */
    private function getUnusedMediaIds(Context $context, int $limit, ?int $offset = null, ?string $folderEntity = null): \Generator
    {
        $criteria = $this->createFilterForNotUsedMedia($folderEntity);
        $criteria->addSorting(new FieldSorting('id', FieldSorting::ASCENDING));
        $criteria->setLimit($limit);
        $criteria->setOffset(0);

        // if we provided an offset, then just grab that batch based on the limit
        if ($offset !== null) {
            $criteria->setOffset($offset);

            /** @var array<string> $ids */
            $ids = $this->mediaRepo->searchIds($criteria, $context)->getIds();

            return yield $this->dispatchEvent($ids);
        }

        while (!empty($ids = $this->mediaRepo->searchIds($criteria, $context)->getIds())) {
            /** @var array<string> $ids */
            $unusedIds = $this->dispatchEvent($ids);

            yield $unusedIds;

            $criteria->setOffset($criteria->getOffset() + $limit);
        }
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string>
     */
    private function dispatchEvent(array $ids): array
    {
        $event = new UnusedMediaSearchEvent(array_values($ids));
        $this->eventDispatcher->dispatch($event);

        return $event->getUnusedIds();
    }

    /**
     * Here we attempt to exclude entity associations that are extending the behaviour of the media entity rather than
     * referencing media. For example, `MediaThumbnailDefinition` adds thumbnail support, whereas `ProductMediaDefinition`
     * adds support for images to products.
     */
    private function isInsideTopLevelDomain(string $domain, EntityDefinition $definition): bool
    {
        if ($definition->getParentDefinition() === null) {
            return false;
        }

        if ($definition->getParentDefinition()->getEntityName() === $domain) {
            return true;
        }

        return $this->isInsideTopLevelDomain($domain, $definition->getParentDefinition());
    }

    private function createFilterForNotUsedMedia(?string $folderEntity = null): Criteria
    {
        $criteria = new Criteria();

        foreach ($this->mediaRepo->getDefinition()->getFields() as $field) {
            if (!$field instanceof AssociationField) {
                continue;
            }

            if (!\in_array($field::class, self::VALID_ASSOCIATIONS, true)) {
                continue;
            }

            $definition = $field->getReferenceDefinition();

            if ($field instanceof ManyToManyAssociationField) {
                $definition = $field->getToManyReferenceDefinition();
            }

            if ($this->isInsideTopLevelDomain(MediaDefinition::ENTITY_NAME, $definition)) {
                continue;
            }

            $fkey = $definition->getFields()->getByStorageName($field->getReferenceField());

            if ($fkey === null) {
                continue;
            }

            $criteria->addFilter(
                new EqualsFilter(\sprintf('media.%s.%s', $field->getPropertyName(), $fkey->getPropertyName()), null)
            );
        }

        if ($folderEntity) {
            $rootMediaFolderId = $this->connection->fetchOne(
                <<<'SQL'
                SELECT HEX(media_folder.id) FROM media_default_folder
                INNER JOIN media_folder ON (media_default_folder.id = media_folder.default_folder_id)
                WHERE entity = :entity
                SQL,
                ['entity' => $folderEntity]
            )
            ;

            if (!$rootMediaFolderId) {
                throw MediaException::defaultMediaFolderWithEntityNotFound($folderEntity);
            }

            /** @var array<string, array{id: string, parent_id: string}> $folders */
            $folders = $this->connection->fetchAllAssociativeIndexed(
                'SELECT HEX(id), HEX(id) as id, HEX(parent_id) as parent_id, name FROM media_folder WHERE id != :id',
                ['id' => $rootMediaFolderId],
            );

            $ids = [$rootMediaFolderId, ...$this->getChildFolderIds($rootMediaFolderId, $folders)];

            $criteria->addFilter(
                new EqualsAnyFilter('media.mediaFolder.id', $ids)
            );
        }

        return $criteria;
    }

    /**
     * @param array<string, array{id: string, parent_id: string}> $folders
     *
     * @return array<string>
     */
    private function getChildFolderIds(string $parentId, array $folders): array
    {
        $ids = [];

        foreach ($folders as $folder) {
            if ($folder['parent_id'] === $parentId) {
                $ids = [...$ids, $folder['id'], ...$this->getChildFolderIds($folder['id'], $folders)];
            }
        }

        return $ids;
    }
}
