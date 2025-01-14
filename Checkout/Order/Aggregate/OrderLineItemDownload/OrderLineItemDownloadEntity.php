<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Order\Aggregate\OrderLineItemDownload;

use Cicada\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Cicada\Core\Content\Media\MediaEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderLineItemDownloadEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $orderLineItemId;

    protected string $orderLineItemVersionId;

    protected string $mediaId;

    protected int $position;

    protected ?MediaEntity $media = null;

    protected ?OrderLineItemEntity $orderLineItem = null;

    protected bool $accessGranted = false;

    public function getOrderLineItemId(): string
    {
        return $this->orderLineItemId;
    }

    public function setOrderLineItemId(string $orderLineItemId): void
    {
        $this->orderLineItemId = $orderLineItemId;
    }

    public function getOrderLineItemVersionId(): string
    {
        return $this->orderLineItemVersionId;
    }

    public function setOrderLineItemVersionId(string $orderLineItemVersionId): void
    {
        $this->orderLineItemVersionId = $orderLineItemVersionId;
    }

    public function getMediaId(): string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getOrderLineItem(): ?OrderLineItemEntity
    {
        return $this->orderLineItem;
    }

    public function setOrderLineItem(OrderLineItemEntity $orderLineItem): void
    {
        $this->orderLineItem = $orderLineItem;
    }

    public function isAccessGranted(): bool
    {
        return $this->accessGranted;
    }

    public function setAccessGranted(bool $accessGranted): void
    {
        $this->accessGranted = $accessGranted;
    }
}
