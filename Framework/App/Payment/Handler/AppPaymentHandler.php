<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Payment\Handler;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund\OrderTransactionCaptureRefundCollection;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund\OrderTransactionCaptureRefundDefinition;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund\OrderTransactionCaptureRefundEntity;
use Cicada\Core\Checkout\Order\OrderEntity;
use Cicada\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use Cicada\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use Cicada\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use Cicada\Core\Checkout\Payment\Cart\Recurring\RecurringDataStruct;
use Cicada\Core\Checkout\Payment\Cart\RefundPaymentTransactionStruct;
use Cicada\Core\Checkout\Payment\PaymentException;
use Cicada\Core\Framework\App\Aggregate\AppPaymentMethod\AppPaymentMethodEntity;
use Cicada\Core\Framework\App\AppCollection;
use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\AppException;
use Cicada\Core\Framework\App\Payload\SourcedPayloadInterface;
use Cicada\Core\Framework\App\Payment\Payload\PaymentPayloadService;
use Cicada\Core\Framework\App\Payment\Payload\Struct\PaymentPayload;
use Cicada\Core\Framework\App\Payment\Payload\Struct\RefundPayload;
use Cicada\Core\Framework\App\Payment\Payload\Struct\ValidatePayload;
use Cicada\Core\Framework\App\Payment\Response\AbstractResponse;
use Cicada\Core\Framework\App\Payment\Response\PaymentResponse;
use Cicada\Core\Framework\App\Payment\Response\RefundResponse;
use Cicada\Core\Framework\App\Payment\Response\ValidateResponse;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\ArrayStruct;
use Cicada\Core\Framework\Struct\Struct;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\Framework\Validation\DataBag\RequestDataBag;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Cicada\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use Cicada\Core\System\StateMachine\StateMachineRegistry;
use Cicada\Core\System\StateMachine\Transition;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal only for use by the app-system
 */
#[Package('checkout')]
class AppPaymentHandler extends AbstractPaymentHandler
{
    /**
     * @param EntityRepository<OrderTransactionCaptureRefundCollection> $refundRepository
     * @param EntityRepository<OrderTransactionCollection> $orderTransactionRepository
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly StateMachineRegistry $stateMachineRegistry,
        private readonly PaymentPayloadService $payloadService,
        private readonly EntityRepository $refundRepository,
        private readonly EntityRepository $orderTransactionRepository,
        private readonly EntityRepository $appRepository,
        private readonly Connection $connection,
    ) {
    }

    public function supports(PaymentHandlerType $type, string $paymentMethodId, Context $context): bool
    {
        $requiredUrl = match ($type) {
            PaymentHandlerType::REFUND => 'refund_url',
            PaymentHandlerType::RECURRING => 'recurring_url',
        };

        $result = $this->connection->createQueryBuilder()
            ->select($requiredUrl)
            ->from('app_payment_method')
            ->where('payment_method_id = :paymentMethodId')
            ->setParameter('paymentMethodId', Uuid::fromHexToBytes($paymentMethodId))
            ->executeQuery()
            ->fetchOne();

        return (bool) $result;
    }

    public function validate(Cart $cart, RequestDataBag $dataBag, SalesChannelContext $context): Struct
    {
        $appPaymentMethod = $context->getPaymentMethod()->getAppPaymentMethod();
        if ($appPaymentMethod === null) {
            throw PaymentException::validatePreparedPaymentInterrupted('Loaded data invalid');
        }

        $validateUrl = $appPaymentMethod->getValidateUrl();
        if (!$validateUrl) {
            return new ArrayStruct();
        }

        $app = $this->getApp($appPaymentMethod);

        $payload = $this->buildValidatePayload($cart, $dataBag, $context);
        $response = $this->requestAppServer($validateUrl, ValidateResponse::class, $payload, $app, $context->getContext());

        return new ArrayStruct($response->getPreOrderPayment());
    }

    public function pay(Request $request, PaymentTransactionStruct $transaction, Context $context, ?Struct $validateStruct = null): ?RedirectResponse
    {
        $orderTransaction = $this->getOrderTransaction($transaction->getOrderTransactionId(), $context);
        $order = $orderTransaction->getOrder();
        if (!$order) {
            throw AppException::invalidTransaction($transaction->getOrderTransactionId());
        }

        $appPaymentMethod = $this->getAppPaymentMethod($orderTransaction);
        $app = $this->getApp($appPaymentMethod);

        $payload = $this->buildPayload($orderTransaction, $order, $request->request->all(), $transaction->getReturnUrl(), new ArrayStruct(), $transaction->getRecurring());
        $payUrl = $appPaymentMethod->getPayUrl();
        if ($payUrl) {
            $response = $this->requestAppServer($payUrl, PaymentResponse::class, $payload, $app, $context);

            // @deprecated tag:v6.7.0 - remove complete if statement, there are no default payment states for app payments anymore
            if (!Feature::isActive('v6.7.0.0') && $response->getRedirectUrl() && !$response->getStatus()) {
                $response->assign(['status' => StateMachineTransitionActions::ACTION_PROCESS_UNCONFIRMED]);
            }

            $this->transitionState($orderTransaction->getId(), $response, $context);

            if ($response->getRedirectUrl()) {
                return new RedirectResponse($response->getRedirectUrl());
            }
        }

        return null;
    }

    public function finalize(Request $request, PaymentTransactionStruct $transaction, Context $context): void
    {
        $queryParameters = $request->query->all();

        unset($queryParameters['_sw_payment_token']);

        $orderTransaction = $this->getOrderTransaction($transaction->getOrderTransactionId(), $context);
        $order = $orderTransaction->getOrder();
        if (!$order) {
            throw AppException::invalidTransaction($transaction->getOrderTransactionId());
        }
        $paymentMethod = $this->getAppPaymentMethod($orderTransaction);
        $app = $this->getApp($paymentMethod);

        $payload = $this->buildPayload($orderTransaction, $order, $queryParameters, recurring: $transaction->getRecurring());

        $url = $paymentMethod->getFinalizeUrl();
        if ($url === null) {
            throw AppException::interrupted('Finalize URL not defined');
        }

        $response = $this->requestAppServer($url, PaymentResponse::class, $payload, $app, $context);

        // @deprecated tag:v6.7.0 - remove complete if statement, there are no default payment states for app payments anymore
        if (!Feature::isActive('v6.7.0.0') && !$response->getStatus()) {
            $response->assign(['status' => StateMachineTransitionActions::ACTION_PROCESS_UNCONFIRMED]);
        }

        $this->transitionState($orderTransaction->getId(), $response, $context);
    }

    public function refund(RefundPaymentTransactionStruct $transaction, Context $context): void
    {
        $criteria = new Criteria([$transaction->getRefundId()]);
        $criteria->addAssociation('stateMachineState');
        $criteria->addAssociation('transactionCapture.transaction.order');
        $criteria->addAssociation('transactionCapture.transaction.paymentMethod.appPaymentMethod.app');
        $criteria->addAssociation('transactionCapture.positions');

        $refund = $this->refundRepository->search($criteria, $context)->getEntities()->first();

        if (!$refund) {
            throw PaymentException::unknownRefund($transaction->getRefundId());
        }

        if (!$refund->getTransactionCapture()?->getTransaction()?->getOrder()) {
            return;
        }

        $orderTransaction = $refund->getTransactionCapture()->getTransaction();
        $paymentMethod = $this->getAppPaymentMethod($orderTransaction);
        $app = $this->getApp($paymentMethod);

        $refundUrl = $paymentMethod->getRefundUrl();
        if (!$refundUrl) {
            throw PaymentException::paymentTypeUnsupported($paymentMethod->getId(), PaymentHandlerType::REFUND);
        }

        $payload = $this->buildRefundPayload($refund, $refund->getTransactionCapture()->getTransaction()->getOrder());
        $response = $this->requestAppServer($refundUrl, RefundResponse::class, $payload, $app, $context);
        $this->transitionState($transaction->getRefundId(), $response, $context, OrderTransactionCaptureRefundDefinition::ENTITY_NAME);
    }

    public function recurring(PaymentTransactionStruct $transaction, Context $context): void
    {
        $orderTransaction = $this->getOrderTransaction($transaction->getOrderTransactionId(), $context);
        $order = $orderTransaction->getOrder();
        if (!$order) {
            throw AppException::invalidTransaction($transaction->getOrderTransactionId());
        }
        $paymentMethod = $this->getAppPaymentMethod($orderTransaction);
        $app = $this->getApp($paymentMethod);

        $recurringUrl = $paymentMethod->getRecurringUrl();
        if (!$recurringUrl) {
            throw PaymentException::paymentTypeUnsupported($paymentMethod->getId(), PaymentHandlerType::RECURRING);
        }

        $payload = $this->buildPayload($orderTransaction, $order, recurring: $transaction->getRecurring());
        $response = $this->requestAppServer($recurringUrl, PaymentResponse::class, $payload, $app, $context);

        $this->transitionState($orderTransaction->getId(), $response, $context);
    }

    /**
     * @template T of AbstractResponse
     *
     * @param class-string<T> $responseClass
     *
     * @return T
     */
    private function requestAppServer(
        string $url,
        string $responseClass,
        SourcedPayloadInterface $payload,
        AppEntity $app,
        Context $context
    ): AbstractResponse {
        $response = $this->payloadService->request($url, $payload, $app, $responseClass, $context);

        if ($response->getErrorMessage()) {
            throw AppException::interrupted($response->getErrorMessage());
        }

        return $response;
    }

    private function transitionState(string $entityId, AbstractResponse $response, Context $context, string $entityName = OrderTransactionDefinition::ENTITY_NAME): void
    {
        if (!$response instanceof PaymentResponse && !$response instanceof RefundResponse) {
            return;
        }

        if (!$response->getStatus()) {
            return;
        }

        if ($response->getStatus() === StateMachineTransitionActions::ACTION_CANCEL) {
            throw PaymentException::customerCanceled($entityId, $response->getErrorMessage() ?? '');
        }

        $this->stateMachineRegistry->transition(
            new Transition(
                $entityName,
                $entityId,
                $response->getStatus(),
                'stateId'
            ),
            $context
        );
    }

    private function getOrderTransaction(string $orderTransactionId, Context $context): OrderTransactionEntity
    {
        $criteria = new Criteria([$orderTransactionId]);
        $criteria->addAssociation('order.orderCustomer.customer');
        $criteria->addAssociation('order.orderCustomer.salutation');
        $criteria->addAssociation('order.language');
        $criteria->addAssociation('order.currency');
        $criteria->addAssociation('order.deliveries.shippingOrderAddress.country');
        $criteria->addAssociation('order.billingAddress.country');
        $criteria->addAssociation('order.lineItems');
        $criteria->addAssociation('order.transactions.stateMachineState');
        $criteria->addAssociation('order.transactions.paymentMethod.appPaymentMethod.app');
        $criteria->addAssociation('stateMachineState');
        $criteria->addAssociation('paymentMethod.appPaymentMethod.app');
        $criteria->getAssociation('order.transactions')->addSorting(new FieldSorting('createdAt'));
        $criteria->addSorting(new FieldSorting('createdAt'));

        $orderTransaction = $this->orderTransactionRepository->search($criteria, $context)->getEntities()->first();

        if (!$orderTransaction) {
            throw AppException::invalidTransaction($orderTransactionId);
        }

        return $orderTransaction;
    }

    private function getAppPaymentMethod(OrderTransactionEntity $orderTransaction): AppPaymentMethodEntity
    {
        if ($orderTransaction->getPaymentMethod()?->getAppPaymentMethod() === null) {
            throw AppException::interrupted('Loaded data invalid');
        }

        return $orderTransaction->getPaymentMethod()->getAppPaymentMethod();
    }

    private function getApp(AppPaymentMethodEntity $appPaymentMethod): AppEntity
    {
        if ($appPaymentMethod->getApp()) {
            return $appPaymentMethod->getApp();
        }

        $appId = $appPaymentMethod->getAppId();
        if (!$appId) {
            throw AppException::interrupted('Loaded data invalid');
        }

        $app = $this->appRepository
            ->search(new Criteria([$appId]), Context::createDefaultContext())
            ->getEntities()
            ->first();

        if (!$app) {
            throw AppException::interrupted('Loaded data invalid');
        }

        return $app;
    }

    /**
     * @param array<string, mixed> $requestData
     */
    private function buildPayload(
        OrderTransactionEntity $transaction,
        OrderEntity $order,
        array $requestData = [],
        ?string $returnUrl = null,
        ?Struct $preOrderPayment = null,
        ?RecurringDataStruct $recurring = null
    ): PaymentPayload {
        return new PaymentPayload($transaction, $order, $requestData, $returnUrl, $preOrderPayment, $recurring);
    }

    private function buildRefundPayload(OrderTransactionCaptureRefundEntity $refund, OrderEntity $order): RefundPayload
    {
        return new RefundPayload(
            $refund,
            $order
        );
    }

    private function buildValidatePayload(Cart $cart, RequestDataBag $dataBag, SalesChannelContext $context): ValidatePayload
    {
        return new ValidatePayload(
            $cart,
            $dataBag->all(),
            $context,
        );
    }
}
