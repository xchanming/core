<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test\TestCaseBase;

use Cicada\Core\Framework\Adapter\Translation\Translator;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait TranslationTestBehaviour
{
    #[Before]
    #[After]
    public function resetInjectedTranslatorSettings(): void
    {
        /** @var Translator $translator */
        $translator = static::getContainer()->get(Translator::class);

        // reset injected settings to make tests deterministic
        $translator->reset();
    }

    abstract protected static function getContainer(): ContainerInterface;
}
