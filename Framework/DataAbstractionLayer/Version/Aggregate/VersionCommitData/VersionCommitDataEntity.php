<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Version\Aggregate\VersionCommitData;

use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\DataAbstractionLayer\Version\Aggregate\VersionCommit\VersionCommitEntity;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class VersionCommitDataEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $autoIncrement;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $versionCommitId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $entityName;

    /**
     * @var array{id: string, versionId: string}
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $entityId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $action;

    /**
     * @var array<string, mixed>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $payload;

    /**
     * @var VersionCommitEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $commit;

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

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement): void
    {
        $this->autoIncrement = $autoIncrement;
    }

    public function getVersionCommitId(): string
    {
        return $this->versionCommitId;
    }

    public function setVersionCommitId(string $versionCommitId): void
    {
        $this->versionCommitId = $versionCommitId;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }

    /**
     * @return array{id: string, versionId: string}
     */
    public function getEntityId(): array
    {
        return $this->entityId;
    }

    /**
     * @param array{id: string, versionId: string} $entityId
     */
    public function setEntityId(array $entityId): void
    {
        $this->entityId = $entityId;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    public function getCommit(): ?VersionCommitEntity
    {
        return $this->commit;
    }

    public function setCommit(VersionCommitEntity $commit): void
    {
        $this->commit = $commit;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
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
