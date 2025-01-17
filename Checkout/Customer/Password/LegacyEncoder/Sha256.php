<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\Password\LegacyEncoder;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Util\Hasher;

#[Package('checkout')]
class Sha256 implements LegacyEncoderInterface
{
    public function getName(): string
    {
        return 'Sha256';
    }

    public function isPasswordValid(string $password, string $hash): bool
    {
        [$iterations, $salt] = explode(':', $hash);

        $verifyHash = $this->generateInternal($password, $salt, (int) $iterations);

        return hash_equals($hash, $verifyHash);
    }

    private function generateInternal(string $password, string $salt, int $iterations): string
    {
        $hash = '';
        for ($i = 0; $i <= $iterations; ++$i) {
            $hash = Hasher::hash($hash . $password . $salt, 'sha256');
        }

        return $iterations . ':' . $salt . ':' . $hash;
    }
}
