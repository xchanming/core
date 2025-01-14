<?php declare(strict_types=1);

namespace Cicada\Core\Content\ImportExport\Aggregate\ImportExportLog;

use Cicada\Core\Content\ImportExport\Aggregate\ImportExportFile\ImportExportFileEntity;
use Cicada\Core\Content\ImportExport\ImportExportProfileEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\User\UserEntity;

#[Package('services-settings')]
class ImportExportLogEntity extends Entity
{
    use EntityIdTrait;

    final public const ACTIVITY_IMPORT = 'import';
    final public const ACTIVITY_EXPORT = 'export';
    final public const ACTIVITY_DRYRUN = 'dryrun';
    final public const ACTIVITY_INVALID_RECORDS_EXPORT = 'invalid_records_export';

    final public const ACTIVITY_TEMPLATE = 'template';

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $activity;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $state;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $records = 0;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $username;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $profileName;

    /**
     * @var UserEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $user;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $userId;

    /**
     * @var ImportExportProfileEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $profile;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $profileId;

    /**
     * @var ImportExportFileEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $file;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $fileId;

    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $config = [];

    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $result = [];

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $invalidRecordsLogId;

    /**
     * @var self|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $invalidRecordsLog;

    /**
     * @var self|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $failedImportLog;

    public function getActivity(): string
    {
        return $this->activity;
    }

    public function setActivity(string $activity): void
    {
        $this->activity = $activity;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getRecords(): int
    {
        return $this->records;
    }

    public function setRecords(int $records): void
    {
        $this->records = $records;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getProfileName(): ?string
    {
        return $this->profileName;
    }

    public function setProfileName(string $profileName): void
    {
        $this->profileName = $profileName;
    }

    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $userEntity): void
    {
        $this->user = $userEntity;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getProfile(): ?ImportExportProfileEntity
    {
        return $this->profile;
    }

    public function setProfile(ImportExportProfileEntity $profile): void
    {
        $this->profile = $profile;
    }

    public function getProfileId(): ?string
    {
        return $this->profileId;
    }

    public function setProfileId(string $profileId): void
    {
        $this->profileId = $profileId;
    }

    public function getFile(): ?ImportExportFileEntity
    {
        return $this->file;
    }

    public function setFile(ImportExportFileEntity $file): void
    {
        $this->file = $file;
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): void
    {
        $this->fileId = $fileId;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    public function getInvalidRecordsLogId(): ?string
    {
        return $this->invalidRecordsLogId;
    }

    public function setInvalidRecordsLogId(?string $invalidRecordsLogId): void
    {
        $this->invalidRecordsLogId = $invalidRecordsLogId;
    }

    public function getInvalidRecordsLog(): ?ImportExportLogEntity
    {
        return $this->invalidRecordsLog;
    }

    public function setInvalidRecordsLog(?ImportExportLogEntity $invalidRecordsLog): void
    {
        $this->invalidRecordsLog = $invalidRecordsLog;
    }

    public function getFailedImportLog(): ?ImportExportLogEntity
    {
        return $this->failedImportLog;
    }

    public function setFailedImportLog(?ImportExportLogEntity $failedImportLog): void
    {
        $this->failedImportLog = $failedImportLog;
    }
}
