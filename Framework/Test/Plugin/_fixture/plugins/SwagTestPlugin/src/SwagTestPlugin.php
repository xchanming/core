<?php declare(strict_types=1);

namespace SwagTestPlugin;

use Cicada\Core\Content\Category\CategoryCollection;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\Parameter\AdditionalBundleParameters;
use Cicada\Core\Framework\Plugin;
use Cicada\Core\Framework\Plugin\Context\ActivateContext;
use Cicada\Core\Framework\Plugin\Context\DeactivateContext;
use Cicada\Core\Framework\Plugin\Context\UninstallContext;
use Cicada\Core\Framework\Plugin\Context\UpdateContext;
use Cicada\Core\Framework\Test\Plugin\_fixture\bundles\FooBarBundle;
use Cicada\Core\Framework\Test\Plugin\_fixture\bundles\GizmoBundle;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\Service\Attribute\Required;

class SwagTestPlugin extends Plugin
{
    final public const PLUGIN_LABEL = 'English plugin name';

    final public const PLUGIN_VERSION = '1.0.1';

    final public const PLUGIN_OLD_VERSION = '1.0.0';

    final public const PLUGIN_GERMAN_LABEL = 'Deutscher Pluginname';

    final public const THROW_ERROR_ON_UPDATE = 'throw-error-on-update';
    final public const THROW_ERROR_ON_DEACTIVATE = 'throw-error-on-deactivate';

    public ?SystemConfigService $systemConfig = null;

    /**
     * @var EntityRepository<CategoryCollection>|null
     */
    public ?EntityRepository $categoryRepository = null;

    public ?ActivateContext $preActivateContext = null;

    public ?ActivateContext $postActivateContext = null;

    public ?DeactivateContext $preDeactivateContext = null;

    public ?DeactivateContext $postDeactivateContext = null;

    #[Required]
    public function requiredSetterOfPrivateService(SystemConfigService $systemConfig): void
    {
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param EntityRepository<CategoryCollection> $categoryRepository
     */
    public function manualSetter(EntityRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
    }

    public function update(UpdateContext $updateContext): void
    {
        if ($updateContext->getContext()->hasExtension(self::THROW_ERROR_ON_UPDATE)) {
            throw new \BadMethodCallException('Update throws an error');
        }

        parent::update($updateContext);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        if ($deactivateContext->getContext()->hasExtension(self::THROW_ERROR_ON_DEACTIVATE)) {
            throw new \BadFunctionCallException('Deactivate throws an error');
        }
        parent::deactivate($deactivateContext);
    }

    public function getMigrationNamespace(): string
    {
        return $_SERVER['FAKE_MIGRATION_NAMESPACE'] ?? parent::getMigrationNamespace();
    }

    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        require_once __DIR__ . '/../../../bundles/FooBarBundle.php';
        require_once __DIR__ . '/../../../bundles/GizmoBundle.php';

        return [
            new FooBarBundle(),
            -10 => new GizmoBundle(),
        ];
    }
}
