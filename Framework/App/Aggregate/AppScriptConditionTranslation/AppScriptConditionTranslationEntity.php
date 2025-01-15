<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Aggregate\AppScriptConditionTranslation;

use Cicada\Core\Framework\App\Aggregate\AppScriptCondition\AppScriptConditionEntity;
use Cicada\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class AppScriptConditionTranslationEntity extends TranslationEntity
{
    protected ?string $name = null;

    protected ?AppScriptConditionEntity $appScriptCondition = null;

    protected string $appScriptConditionId;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAppScriptCondition(): ?AppScriptConditionEntity
    {
        return $this->appScriptCondition;
    }

    public function setAppScriptCondition(?AppScriptConditionEntity $appScriptCondition): void
    {
        $this->appScriptCondition = $appScriptCondition;
    }

    public function getAppScriptConditionId(): string
    {
        return $this->appScriptConditionId;
    }

    public function setAppScriptConditionId(string $appScriptConditionId): void
    {
        $this->appScriptConditionId = $appScriptConditionId;
    }
}
