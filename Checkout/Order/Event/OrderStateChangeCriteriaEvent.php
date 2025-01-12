<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Order\Event;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Event\CicadaEvent;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

if (Feature::isActive('v6.7.0.0')) {
    #[Package('checkout')]
    class OrderStateChangeCriteriaEvent extends Event implements CicadaEvent
    {
        public function __construct(
            private readonly string $orderId,
            private readonly Criteria $criteria,
            private readonly Context $context,
        ) {
        }

        public function getOrderId(): string
        {
            return $this->orderId;
        }

        public function getCriteria(): Criteria
        {
            return $this->criteria;
        }

        public function getContext(): Context
        {
            return $this->context;
        }
    }
} else {
    #[Package('checkout')]
    class OrderStateChangeCriteriaEvent extends Event
    {
        private Context $context;

        /**
         * @deprecated tag:v6.7.0 - $context property will be added
         */
        public function __construct(
            private readonly string $orderId,
            private readonly Criteria $criteria
        ) {
        }

        public function getOrderId(): string
        {
            return $this->orderId;
        }

        public function getCriteria(): Criteria
        {
            return $this->criteria;
        }

        /**
         * @deprecated tag:v6.7.0 - $context property will be set in constructor
         */
        public function setContext(Context $context): void
        {
            Feature::triggerDeprecationOrThrow('v6.7.0.0', '$context property will be set in constructor');
            $this->context = $context;
        }

        public function getContext(): Context
        {
            return $this->context;
        }
    }
}
