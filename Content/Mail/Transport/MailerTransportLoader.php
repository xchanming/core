<?php declare(strict_types=1);

namespace Cicada\Core\Content\Mail\Transport;

use Cicada\Core\Content\Mail\MailException;
use Cicada\Core\Content\Mail\Service\MailAttachmentsBuilder;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Doctrine\DBAL\Exception\DriverException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\Transports;

/**
 * @internal
 */
#[Package('after-sales')]
class MailerTransportLoader
{
    private const VALID_OPTIONS = ['-bs', '-i', '-t'];

    /**
     * @internal
     */
    public function __construct(
        private readonly Transport $envBasedTransport,
        private readonly SystemConfigService $configService,
        private readonly MailAttachmentsBuilder $attachmentsBuilder,
        private readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param array<string, string> $dsns
     */
    public function fromStrings(array $dsns): Transports
    {
        $transports = [];
        foreach ($dsns as $name => $dsn) {
            if ($name === 'main') {
                $transports[$name] = $this->fromString($dsn);
            } else {
                $transports[$name] = $this->createTransportUsingDSN($dsn);
            }
        }

        return new Transports($transports);
    }

    public function fromString(string $dsn): TransportInterface
    {
        try {
            $transportConfig = trim($this->configService->getString('core.mailerSettings.emailAgent'));

            if ($transportConfig === '') {
                return $this->createTransportUsingDSN($dsn);
            }
        } catch (DriverException) {
            // We don't have a database connection right now
            return $this->createTransportUsingDSN($dsn);
        }

        return new MailerTransportDecorator(
            $this->create(),
            $this->attachmentsBuilder,
            $this->filesystem,
        );
    }

    public function createTransportUsingDSN(string $dsn): MailerTransportDecorator
    {
        return new MailerTransportDecorator(
            $this->envBasedTransport->fromString($dsn),
            $this->attachmentsBuilder,
            $this->filesystem,
        );
    }

    private function create(): TransportInterface
    {
        $emailAgent = $this->configService->getString('core.mailerSettings.emailAgent');

        return match ($emailAgent) {
            'smtp+oauth' => $this->createSmtpOAuthTransport($this->configService),
            'smtp' => $this->createSmtpTransport($this->configService),
            'local' => $this->createSendmailTransport($this->configService),
            default => throw MailException::givenMailAgentIsInvalid($emailAgent),
        };
    }

    private function createSmtpOAuthTransport(SystemConfigService $configService): TransportInterface
    {
        $dsn = new Dsn(
            'smtp',
            $configService->getString('core.mailerSettings.host'),
            $configService->getString('core.mailerSettings.senderAddress'),
            $configService->getString('core.mailerSettings.clientSecret'),
            $configService->getInt('core.mailerSettings.port'),
            [
                SmtpOauthTransportFactoryDecorator::OPTION_KEY_USE_OAUTH => true,
            ]
        );

        return $this->envBasedTransport->fromDsnObject($dsn);
    }

    private function createSmtpTransport(SystemConfigService $configService): TransportInterface
    {
        $dsn = new Dsn(
            $this->getEncryption($configService) === 'ssl' ? 'smtps' : 'smtp',
            $configService->getString('core.mailerSettings.host'),
            $configService->getString('core.mailerSettings.username'),
            $configService->getString('core.mailerSettings.password'),
            $configService->getInt('core.mailerSettings.port'),
            $this->getEncryption($configService) !== null ? [] : ['verify_peer' => 0]
        );

        return $this->envBasedTransport->fromDsnObject($dsn);
    }

    private function getEncryption(SystemConfigService $configService): ?string
    {
        $encryption = $configService->getString('core.mailerSettings.encryption');

        return match ($encryption) {
            'ssl' => 'ssl',
            'tls' => 'tls',
            default => null,
        };
    }

    private function createSendmailTransport(SystemConfigService $configService): TransportInterface
    {
        $dsn = new Dsn(
            scheme: 'sendmail',
            host: '',
            options: [
                'command' => $this->getSendMailCommandLineArgument($configService),
            ]
        );

        return $this->envBasedTransport->fromDsnObject($dsn);
    }

    private function getSendMailCommandLineArgument(SystemConfigService $configService): string
    {
        $command = '/usr/sbin/sendmail ';

        $sendMailOptions = trim($configService->getString('core.mailerSettings.sendMailOptions'));

        if ($sendMailOptions === '') {
            $sendMailOptions = '-t -i';
        }

        $options = preg_split('/\s+/', $sendMailOptions) ?: [$sendMailOptions];

        foreach ($options as $sendMailOption) {
            if (!\in_array(trim($sendMailOption), self::VALID_OPTIONS, true)) {
                throw MailException::givenSendMailOptionIsInvalid($sendMailOption, self::VALID_OPTIONS);
            }
        }

        return $command . $sendMailOptions;
    }
}
