<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Aggregate\AppScriptCondition;

use Cicada\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionCollection;
use Cicada\Core\Framework\App\Aggregate\AppScriptConditionTranslation\AppScriptConditionTranslationCollection;
use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\CustomFieldType;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\Validator\Constraint;

/**
 * @phpstan-import-type CustomFieldTypeArray from CustomFieldType
 */
#[Package('core')]
class AppScriptConditionEntity extends Entity
{
    use EntityIdTrait;

    protected string $appId;

    protected ?AppEntity $app = null;

    protected string $identifier;

    protected ?string $name = null;

    protected bool $active;

    protected ?string $group = null;

    protected ?string $script = null;

    /**
     * @internal
     *
     * @var string|array<string, list<Constraint>>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $constraints;

    /**
     * @var CustomFieldTypeArray|null
     */
    protected ?array $config;

    protected ?RuleConditionCollection $ruleConditions = null;

    protected ?AppScriptConditionTranslationCollection $translations = null;

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function getApp(): ?AppEntity
    {
        return $this->app;
    }

    public function setApp(?AppEntity $app): void
    {
        $this->app = $app;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): void
    {
        $this->group = $group;
    }

    public function getScript(): ?string
    {
        return $this->script;
    }

    public function setScript(?string $script): void
    {
        $this->script = $script;
    }

    /**
     * @internal
     *
     * @return string|array<string, list<Constraint>>|null
     */
    public function getConstraints()
    {
        $this->checkIfPropertyAccessIsAllowed('constraints');

        return $this->constraints;
    }

    /**
     * @internal
     *
     * @param string|array<string, list<Constraint>>|null $constraints
     */
    public function setConstraints($constraints): void
    {
        $this->constraints = $constraints;
    }

    /**
     * @return CustomFieldTypeArray|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * @param CustomFieldTypeArray|null $config
     */
    public function setConfig(?array $config): void
    {
        $this->config = $config;
    }

    public function getRuleConditions(): ?RuleConditionCollection
    {
        return $this->ruleConditions;
    }

    public function setRuleConditions(RuleConditionCollection $conditions): void
    {
        $this->ruleConditions = $conditions;
    }

    public function getTranslations(): ?AppScriptConditionTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(AppScriptConditionTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }
}
