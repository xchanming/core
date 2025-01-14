<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Version\Aggregate\VersionCommit;

use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\DataAbstractionLayer\Version\Aggregate\VersionCommitData\VersionCommitDataCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Version\VersionEntity;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class VersionCommitEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $autoIncrement;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $message;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $userId;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $integrationId;

    /**
     * @var VersionCommitDataCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $data;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $isMerge;

    /**
     * @var VersionEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $version;

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement): void
    {
        $this->autoIncrement = $autoIncrement;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getVersionId(): ?string
    {
        return $this->versionId;
    }

    public function getData(): VersionCommitDataCollection
    {
        return $this->data;
    }

    public function setData(VersionCommitDataCollection $data): void
    {
        $this->data = $data;
    }

    public function getIsMerge(): bool
    {
        return $this->isMerge;
    }

    public function setIsMerge(bool $isMerge): void
    {
        $this->isMerge = $isMerge;
    }

    public function getVersion(): ?VersionEntity
    {
        return $this->version;
    }

    public function setVersion(VersionEntity $version): void
    {
        $this->version = $version;
    }

    public function getIntegrationId(): ?string
    {
        return $this->integrationId;
    }

    public function setIntegrationId(string $integrationId): void
    {
        $this->integrationId = $integrationId;
    }
}
