<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Api\Context;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class AdminSalesChannelApiSource extends SalesChannelApiSource
{
    public string $type = 'admin-sales-channel-api';

    /**
     * @var Context
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $originalContext;

    public function __construct(
        string $salesChannelId,
        Context $originalContext
    ) {
        parent::__construct($salesChannelId);

        $this->originalContext = $originalContext;
    }

    public function getOriginalContext(): Context
    {
        return $this->originalContext;
    }
}
