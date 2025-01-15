<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class PluginDownloadDataStruct extends Struct
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $location;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $type;

    protected ?string $binaryVersion = null;

    protected ?string $manifestLocation = null;

    protected ?string $name = null;

    protected ?string $sha1 = null;

    protected ?int $size = null;

    protected ?string $region = null;

    protected ?string $bucket = null;

    /**
     * @param array<string, mixed> $arr
     */
    public static function fromArray(array $arr): self
    {
        return (new self())->assign($arr);
    }

    public function getApiAlias(): string
    {
        return 'store_download_data';
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getManifestLocation(): ?string
    {
        return $this->manifestLocation;
    }

    public function getBucket(): ?string
    {
        return $this->bucket;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getBinaryVersion(): ?string
    {
        return $this->binaryVersion;
    }

    public function getSha1(): ?string
    {
        return $this->sha1;
    }
}
