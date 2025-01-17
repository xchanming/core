<?php declare(strict_types=1);

namespace Cicada\Core\Test\Integration\Builder\Customer;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\Test\Stub\Framework\IdsCollection;
use Cicada\Core\Test\TestBuilderTrait;
use Cicada\Core\Test\TestDefaults;
use Doctrine\DBAL\Connection;

/**
 * @final
 * How to use:
 * $x = (new CustomerBuilder(new IdsCollection(), 'p1'))
 *          ->name('Max')
 *          ->group('standard')
 *          ->build();
 */
#[Package('checkout')]
class CustomerBuilder
{
    use KernelTestBehaviour;
    use TestBuilderTrait;

    public string $id;

    protected string $name;

    protected string $email;

    protected string $customerGroupId;

    protected string $defaultBillingAddressId;

    /**
     * @var array<string, mixed>
     */
    protected array $defaultBillingAddress = [];

    protected string $defaultShippingAddressId;

    /**
     * @var array<string, mixed>
     */
    protected array $addresses = [];

    /**
     * @var array<string, mixed>
     */
    protected array $group = [];

    /**
     * @var array<string, mixed>
     */
    protected array $salutation = [];

    public function __construct(
        IdsCollection $ids,
        protected string $customerNumber,
        protected string $salesChannelId = TestDefaults::SALES_CHANNEL,
        string $customerGroup = 'customer-group',
        string $billingAddress = 'default-address',
        string $shippingAddress = 'default-address'
    ) {
        $this->ids = $ids;
        $this->id = $ids->create($customerNumber);
        $this->name = 'Max';
        $this->email = 'max@mustermann.com';
        $this->salutation = self::salutation($ids);

        $this->customerGroup($customerGroup);
        $this->defaultBillingAddress($billingAddress);
        $this->defaultShippingAddress($shippingAddress);
    }

    public function customerNumber(string $customerNumber): self
    {
        $this->customerNumber = $customerNumber;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function customerGroup(string $key): self
    {
        $this->customerGroupId = $this->ids->get($key);
        $this->group = [
            'id' => $this->ids->get($key),
            'name' => $key,
        ];

        return $this;
    }

    /**
     * @param array<string, mixed> $customParams
     */
    public function defaultBillingAddress(string $key, array $customParams = []): self
    {
        $this->addAddress($key, $customParams);

        $defaultBillingAddress = $this->addresses;
        $defaultBillingAddress[$key]['id'] = $this->ids->get($key);
        $this->defaultBillingAddress = $defaultBillingAddress[$key];
        $this->defaultBillingAddressId = $this->ids->get($key);

        return $this;
    }

    /**
     * @param array<string, mixed> $customParams
     */
    public function defaultShippingAddress(string $key, array $customParams = []): self
    {
        $this->addAddress($key, $customParams);
        $this->defaultShippingAddressId = $this->ids->get($key);

        return $this;
    }

    /**
     * @param array<string, mixed> $customParams
     */
    public function addAddress(string $key, array $customParams = []): self
    {
        $address = \array_replace([
            'name' => $this->name,
            'salutation' => self::salutation($this->ids),
            'street' => 'Buchenweg 5',
            'zipcode' => '33062',
            'countryId' => $this->getCountry(),
        ], $customParams);

        $this->addresses[$key] = $address;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private static function salutation(IdsCollection $ids): array
    {
        return [
            'id' => $ids->get('salutation'),
            'salutationKey' => 'salutation',
            'displayName' => 'test',
            'letterName' => 'test',
        ];
    }

    private static function connection(): Connection
    {
        return self::getContainer()->get(Connection::class);
    }

    private function getCountry(): string
    {
        return self::connection()->fetchOne(
            'SELECT LOWER(HEX(country_id)) FROM sales_channel_country WHERE sales_channel_id = :id LIMIT 1',
            ['id' => Uuid::fromHexToBytes($this->salesChannelId)]
        );
    }
}
