<?php declare(strict_types=1);

namespace Cicada\Core\Content\Flow\Aggregate\FlowTemplate;

use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;

#[Package('after-sales')]
class FlowTemplateEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
