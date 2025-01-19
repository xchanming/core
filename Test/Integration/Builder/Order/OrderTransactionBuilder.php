<?php declare(strict_types=1);

namespace Cicada\Core\Test\Integration\Builder\Order;

use Cicada\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Cicada\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Cicada\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransactionCapture\OrderTransactionCaptureStates;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour;
use Cicada\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Cicada\Core\Test\Stub\Framework\IdsCollection;
use Cicada\Core\Test\TestBuilderTrait;

/**
 * @final
 */
#[Package('checkout')]
class OrderTransactionBuilder
{
    use BasicTestDataBehaviour;
    use KernelTestBehaviour;
    use TestBuilderTrait;

    protected string $id;

    protected string $orderId;

    protected string $paymentMethodId;

    protected CalculatedPrice $amount;

    protected string $stateId;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $captures = [];

    public function __construct(
        IdsCollection $ids,
        string $key,
        string $orderNumber = '10000',
        float $amount = 420.69,
        string $state = OrderTransactionStates::STATE_OPEN
    ) {
        $this->id = $ids->get($key);
        $this->ids = $ids;
        $this->paymentMethodId = $this->getValidPaymentMethodId();
        $this->orderId = $ids->get($orderNumber);
        $this->stateId = $this->getStateMachineState(OrderTransactionStates::STATE_MACHINE, $state);

        $this->amount($amount);
    }

    public function amount(float $amount): self
    {
        $this->amount = new CalculatedPrice($amount, $amount, new CalculatedTaxCollection(), new TaxRuleCollection());

        return $this;
    }

    /**
     * @param array<string, mixed> $customParams
     */
    public function addCapture(string $key, array $customParams = []): self
    {
        $capture = \array_merge([
            'id' => $this->ids->get($key),
            'orderTransactionId' => $this->id,
            'stateId' => $this->getStateMachineState(
                OrderTransactionCaptureStates::STATE_MACHINE,
                OrderTransactionCaptureStates::STATE_PENDING
            ),
            'externalReference' => null,
            'totalAmount' => 420.69,
            'amount' => new CalculatedPrice(
                420.69,
                420.69,
                new CalculatedTaxCollection(),
                new TaxRuleCollection()
            ),
        ], $customParams);

        $this->captures[$this->ids->get($key)] = $capture;

        return $this;
    }
}
