<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class PluginRegionStruct extends Struct
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $label;

    /**
     * @var PluginCategoryCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $categories;

    public function __construct(
        string $name,
        string $label,
        iterable $categories
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->categories = new PluginCategoryCollection($categories);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCategories(): PluginCategoryCollection
    {
        return $this->categories;
    }

    public function getApiAlias(): string
    {
        return 'store_plugin_region';
    }
}
