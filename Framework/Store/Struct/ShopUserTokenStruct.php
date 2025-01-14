<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class ShopUserTokenStruct extends Struct
{
    public function __construct(
        protected string $token,
        protected \DateTimeInterface $expirationDate,
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpirationDate(): \DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function getApiAlias(): string
    {
        return 'store_shop_user_token';
    }
}
