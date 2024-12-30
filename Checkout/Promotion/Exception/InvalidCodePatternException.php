<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\Exception;

use Cicada\Core\Checkout\Promotion\PromotionException;
use Cicada\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class InvalidCodePatternException extends PromotionException
{
}
