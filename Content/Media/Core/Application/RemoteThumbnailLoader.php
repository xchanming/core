<?php declare(strict_types=1);

namespace Cicada\Core\Content\Media\Core\Application;

use Cicada\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Cicada\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Cicada\Core\Content\Media\Core\Params\UrlParams;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\Connection;
use League\Flysystem\FilesystemOperator;
use Symfony\Contracts\Service\ResetInterface;

/**
 * The remote thumbnail loader is responsible for generating the urls for media entities, and it's thumbnails.
 *
 * @final
 */
#[Package('discovery')]
class RemoteThumbnailLoader implements ResetInterface
{
    /**
     * @var array<string, array<array{width: string, height: string}>>
     */
    private array $mediaFolderThumbnailSizes = [];

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractMediaUrlGenerator $generator,
        private readonly Connection $connection,
        private readonly FilesystemOperator $filesystem,
        private readonly string $pattern = ''
    ) {
    }

    /**
     * Collects all urls of the media entities and triggers the AbstractMediaUrlGenerator to generate the urls.
     * The generated urls will be assigned to the entities afterward.
     *
     * Generates the thumbnails for the media entities according to the provided pattern and media thumbnail sizes.
     * The generated thumbnails will be assigned to the entities afterward.
     *
     * @param iterable<Entity> $media
     */
    public function load(iterable $media): void
    {
        $mapping = $this->map($media);

        if (empty($mapping)) {
            return;
        }

        $urls = $this->generator->generate($mapping);

        $mediaThumbnailSizes = $this->getMediaThumbnailSizes();
        $baseUrl = $this->getBaseUrl();

        foreach ($media as $mediaEntity) {
            if (!isset($urls[$mediaEntity->getUniqueIdentifier()])) {
                continue;
            }

            $mediaEntity->assign(['url' => $urls[$mediaEntity->getUniqueIdentifier()]]);

            $thumbnailSizes = $mediaThumbnailSizes[$mediaEntity->get('mediaFolderId')] ?? [];

            if (empty($thumbnailSizes)) {
                $mediaEntity->assign(['thumbnails' => new MediaThumbnailCollection()]);

                continue;
            }

            $path = $mediaEntity->get('path');
            $updatedAt = $mediaEntity->get('updatedAt') ?? $mediaEntity->get('createdAt');

            if (!($updatedAt instanceof \DateTimeInterface)) {
                $updatedAt = null;
            }

            $thumbnails = new MediaThumbnailCollection();
            foreach ($thumbnailSizes as $size) {
                $url = $this->getUrl($baseUrl, $path, $size['width'], $size['height'], $updatedAt);

                $thumbnail = new MediaThumbnailEntity();
                $thumbnail->assign([
                    'id' => Uuid::randomHex(),
                    'width' => (int) $size['width'],
                    'height' => (int) $size['height'],
                    'url' => $url,
                ]);

                $thumbnails->add($thumbnail);
            }

            $mediaEntity->assign(['thumbnails' => $thumbnails]);
        }
    }

    public function reset(): void
    {
        $this->mediaFolderThumbnailSizes = [];
    }

    /**
     * @param iterable<Entity> $entities
     *
     * @return array<string, UrlParams>
     */
    private function map(iterable $entities): array
    {
        $mapped = [];

        foreach ($entities as $entity) {
            if (!$entity->has('path') || empty($entity->get('path'))) {
                continue;
            }
            // don't generate private urls
            if (!$entity->has('private') || $entity->get('private')) {
                continue;
            }

            $mapped[$entity->getUniqueIdentifier()] = UrlParams::fromMedia($entity);
        }

        return $mapped;
    }

    /**
     * @return array<string, array<array{width: string, height: string}>>
     */
    private function getMediaThumbnailSizes(): array
    {
        if (!empty($this->mediaFolderThumbnailSizes)) {
            return $this->mediaFolderThumbnailSizes;
        }

        $entities = $this->connection->fetchAllAssociative(
            '
            SELECT LOWER(HEX(mf.id)) as media_folder_id, mts.width, mts.height
            FROM media_folder mf
            INNER JOIN media_folder_configuration mfc ON mf.media_folder_configuration_id = mfc.id
            INNER JOIN media_folder_configuration_media_thumbnail_size mfcmts ON mfcmts.media_folder_configuration_id = mfc.id
            INNER JOIN media_thumbnail_size mts ON mfcmts.media_thumbnail_size_id = mts.id'
        );

        if (empty($entities)) {
            return [];
        }

        $grouped = [];

        /** @var array{media_folder_id: string, width: string, height: string} $entity */
        foreach ($entities as $entity) {
            $grouped[$entity['media_folder_id']][] = ['width' => $entity['width'], 'height' => $entity['height']];
        }

        return $this->mediaFolderThumbnailSizes = $grouped;
    }

    private function getBaseUrl(): string
    {
        return \rtrim($this->filesystem->publicUrl(''), '/');
    }

    private function getUrl(string $mediaUrl, string $mediaPath, string $width, string $height, ?\DateTimeInterface $mediaUpdatedAt): string
    {
        $replacements = [
            str_starts_with($mediaPath, 'http') ? '' : $mediaUrl,
            $mediaPath,
            $width,
            $height,
            $mediaUpdatedAt?->getTimestamp() ?: '',
        ];

        $url = str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{height}', '{mediaUpdatedAt}'],
            $replacements,
            $this->pattern
        );

        return str_starts_with($mediaPath, 'http') ? ltrim($url, '/') : $url;
    }
}
