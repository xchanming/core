<?php declare(strict_types=1);

namespace Cicada\Core\Content\Sitemap\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('services-settings')]
class Sitemap extends Struct
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $filename;

    /**
     * @var \DateTimeInterface
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $created;

    /**
     * @throws \Exception
     */
    public function __construct(
        string $filename,
        private int $urlCount,
        ?\DateTimeInterface $created = null
    ) {
        $this->filename = $filename;
        $this->created = $created ?: new \DateTime('NOW', new \DateTimeZone('UTC'));
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getUrlCount(): int
    {
        return $this->urlCount;
    }

    public function setUrlCount(int $urlCount): void
    {
        $this->urlCount = $urlCount;
    }

    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): void
    {
        $this->created = $created;
    }

    public function getApiAlias(): string
    {
        return 'sitemap';
    }
}
