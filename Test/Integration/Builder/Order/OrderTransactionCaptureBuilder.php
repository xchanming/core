<?php declare(strict_types=1);

namespace Cicada\Core\Test\Integration\Builder\Order;

use Cicada\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Cicada\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Cicada\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransactionCapture\OrderTransactionCaptureStates;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund\OrderTransactionCaptureRefundStates;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour;
use Cicada\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Cicada\Core\Test\Stub\Framework\IdsCollection;
use Cicada\Core\Test\TestBuilderTrait;

/**
 * @final
 */
#[Package('checkout')]
class OrderTransactionCaptureBuilder
{
    use BasicTestDataBehaviour;
    use KernelTestBehaviour;
    use TestBuilderTrait;

    protected string $id;

    protected CalculatedPrice $amount;

    protected string $stateId;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $refunds = [];

    public function __construct(
        IdsCollection $ids,
        string $key,
        protected string $orderTransactionId,
        float $amount = 420.69,
        string $state = OrderTransactionCaptureStates::STATE_PENDING,
        protected ?string $externalReference = null
    ) {
        $this->id = $ids->get($key);
        $this->ids = $ids;
        $this->stateId = $this->getStateMachineState(OrderTransactionCaptureStates::STATE_MACHINE, $state);

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
    public function addRefund(string $key, array $customParams = []): self
    {
        $refund = \array_replace([
            'id' => $this->ids->get($key),
            'captureId' => $this->id,
            'stateId' => $this->getStateMachineState(
                OrderTransactionCaptureRefundStates::STATE_MACHINE,
                OrderTransactionCaptureRefundStates::STATE_OPEN
            ),
            'externalReference' => null,
            'reason' => null,
            'amount' => new CalculatedPrice(
                420.69,
                420.69,
                new CalculatedTaxCollection(),
                new TaxRuleCollection()
            ),
        ], $customParams);

        $this->refunds[$this->ids->get($key)] = $refund;

        return $this;
    }
}
