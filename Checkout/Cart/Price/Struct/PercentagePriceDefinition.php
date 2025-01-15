<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Price\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Rule\Rule;
use Cicada\Core\Framework\Struct\Struct;
use Cicada\Core\Framework\Util\FloatComparator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * A PercentagePriceDefinition calculate a percentual sum of all previously calculated prices and returns it as its own
 * price. This can be used for percentual discounts.
 */
#[Package('checkout')]
class PercentagePriceDefinition extends Struct implements PriceDefinitionInterface, FilterableInterface
{
    final public const TYPE = 'percentage';
    final public const SORTING_PRIORITY = 50;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $percentage;

    /**
     * Allows to define a filter rule which line items should be considered for percentage discount/surcharge
     *
     * @var Rule|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $filter;

    public function __construct(
        float $percentage,
        ?Rule $filter = null
    ) {
        $this->percentage = FloatComparator::cast($percentage);
        $this->filter = $filter;
    }

    public function getPercentage(): float
    {
        return FloatComparator::cast($this->percentage);
    }

    public function getFilter(): ?Rule
    {
        return $this->filter;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getPriority(): int
    {
        return self::SORTING_PRIORITY;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['type'] = $this->getType();

        return $data;
    }

    /**
     * @return array<string, Constraint[]>
     */
    public static function getConstraints(): array
    {
        return [
            'percentage' => [new NotBlank(), new Type('numeric')],
        ];
    }

    public function getApiAlias(): string
    {
        return 'cart_price_percentage';
    }
}
