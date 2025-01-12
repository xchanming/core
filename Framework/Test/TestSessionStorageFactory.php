<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test;

use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * @internal
 */
#[Package('core')]
class TestSessionStorageFactory implements SessionStorageFactoryInterface
{
    public function createStorage(?Request $request): SessionStorageInterface
    {
        return new TestSessionStorage();
    }
}
