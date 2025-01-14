<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart;

use Cicada\Core\Checkout\Cart\Error\ErrorCollection;
use Cicada\Core\Checkout\Cart\Event\CartLoadedEvent;
use Cicada\Core\Checkout\Cart\Event\CartSavedEvent;
use Cicada\Core\Checkout\Cart\Event\CartVerifyPersistEvent;
use Cicada\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Cicada\Core\Framework\Adapter\Cache\RedisConnectionFactory;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-import-type RedisTypeHint from RedisConnectionFactory
 */
#[Package('checkout')]
class RedisCartPersister extends AbstractCartPersister
{
    final public const PREFIX = 'cart-persister-';

    /**
     * @param RedisTypeHint $redis
     *
     * @internal
     */
    public function __construct(
        /** @phpstan-ignore cicada.propertyNativeType (Cannot type natively, as Symfony might change the implementation in the future) */
        private $redis,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CartSerializationCleaner $cartSerializationCleaner,
        private readonly CartCompressor $compressor,
        private readonly int $expireDays
    ) {
    }

    public function getDecorated(): AbstractCartPersister
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(string $token, SalesChannelContext $context): Cart
    {
        /** @var string|bool|array<mixed> $value */
        $value = $this->redis->get(self::PREFIX . $token);

        if ($value === false || !\is_string($value)) {
            throw CartException::tokenNotFound($token);
        }

        try {
            $value = \unserialize($value);
        } catch (\Exception) {
            throw CartException::tokenNotFound($token);
        }

        if (!isset($value['compressed'])) {
            throw CartException::tokenNotFound($token);
        }

        try {
            $content = $this->compressor->unserialize($value['content'], (int) $value['compressed']);
        } catch (\Exception) {
            // When we can't decode it, we have to delete it
            throw CartException::tokenNotFound($token);
        }

        if (!\is_array($content)) {
            throw CartException::tokenNotFound($token);
        }

        $cart = $content['cart'];

        if (!$cart instanceof Cart) {
            throw CartException::deserializeFailed();
        }

        $cart->setToken($token);
        $cart->setRuleIds($content['rule_ids']);

        $this->eventDispatcher->dispatch(new CartLoadedEvent($cart, $context));

        return $cart;
    }

    public function save(Cart $cart, SalesChannelContext $context): void
    {
        $shouldPersist = $this->shouldPersist($cart);

        $this->eventDispatcher->dispatch(new CartSavedEvent($context, $cart));

        $event = new CartVerifyPersistEvent($context, $cart, $shouldPersist);

        $this->eventDispatcher->dispatch($event);
        if (!$event->shouldBePersisted()) {
            $this->delete($cart->getToken(), $context);

            return;
        }

        $content = $this->serializeCart($cart, $context);

        $this->redis->set(self::PREFIX . $cart->getToken(), $content, ['EX' => $this->expireDays * 86400]);
    }

    public function delete(string $token, SalesChannelContext $context): void
    {
        $this->redis->del(self::PREFIX . $token);
    }

    public function replace(string $oldToken, string $newToken, SalesChannelContext $context): void
    {
        try {
            $cart = $this->load($oldToken, $context);
        } catch (CartTokenNotFoundException) {
            return;
        }

        $copyContext = clone $context;
        $copyContext->setRuleIds($cart->getRuleIds());

        $cart->setToken($newToken);
        $this->save($cart, $copyContext);
        $cart->setToken($oldToken);

        $this->delete($oldToken, $context);
    }

    private function serializeCart(Cart $cart, SalesChannelContext $context): string
    {
        $errors = $cart->getErrors();
        $data = $cart->getData();

        $cart->setErrors(new ErrorCollection());
        $cart->setData(null);

        $this->cartSerializationCleaner->cleanupCart($cart);

        [$compressed, $content] = $this->compressor->serialize(['cart' => $cart, 'rule_ids' => $context->getRuleIds()]);

        $cart->setErrors($errors);
        $cart->setData($data);

        return \serialize([
            'compressed' => $compressed,
            'content' => $content,
        ]);
    }
}
