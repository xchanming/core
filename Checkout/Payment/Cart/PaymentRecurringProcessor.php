<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Payment\Cart;

use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Cicada\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use Cicada\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use Cicada\Core\Checkout\Payment\PaymentException;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\StateMachine\Loader\InitialStateIdLoader;
use Psr\Log\LoggerInterface;

#[Package('checkout')]
class PaymentRecurringProcessor
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderTransactionCollection> $orderTransactionRepository
     */
    public function __construct(
        private readonly EntityRepository $orderTransactionRepository,
        private readonly InitialStateIdLoader $initialStateIdLoader,
        private readonly OrderTransactionStateHandler $stateHandler,
        private readonly PaymentHandlerRegistry $paymentHandlerRegistry,
        private readonly AbstractPaymentTransactionStructFactory $paymentTransactionStructFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function processRecurring(string $orderId, Context $context): void
    {
        $transaction = $this->getCurrentOrderTransaction($orderId, $context);

        try {
            $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($transaction->getPaymentMethodId());
            if (!$paymentHandler) {
                throw PaymentException::unknownPaymentMethodById($transaction->getPaymentMethodId());
            }

            if (!$paymentHandler->supports(PaymentHandlerType::RECURRING, $transaction->getPaymentMethodId(), $context)) {
                throw PaymentException::paymentTypeUnsupported($transaction->getPaymentMethodId(), PaymentHandlerType::RECURRING);
            }

            $struct = $this->paymentTransactionStructFactory->build($transaction->getId(), $context);
            $paymentHandler->recurring($struct, $context);
        } catch (\Throwable $e) {
            $this->logger->error('An error occurred during processing the payment', ['orderTransactionId' => $transaction->getId(), 'exceptionMessage' => $e->getMessage()]);
            $this->stateHandler->fail($transaction->getId(), $context);

            throw $e;
        }
    }

    private function getCurrentOrderTransaction(string $orderId, Context $context): OrderTransactionEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('stateId', $this->initialStateIdLoader->get(OrderTransactionStates::STATE_MACHINE)));
        $criteria->addFilter(new EqualsFilter('orderId', $orderId));
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $criteria->setLimit(1);

        $transaction = $this->orderTransactionRepository->search($criteria, $context)->getEntities()->first();

        if (!$transaction) {
            throw PaymentException::invalidOrder($orderId);
        }

        return $transaction;
    }
}
