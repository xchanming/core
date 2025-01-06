<?php declare(strict_types=1);

namespace Cicada\Core\Content\MailTemplate\Exception;

use Cicada\Core\Framework\CicadaHttpException;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated tag:v6.7.0 - Will be removed in v6.7.0.0. Use MailException::mailTransportFailedException instead
 */
#[Package('buyers-experience')]
class MailTransportFailedException extends CicadaHttpException
{
    public function __construct(
        array $failedRecipients,
        ?\Throwable $e = null
    ) {
        Feature::triggerDeprecationOrThrow(
            'v6.7.0.0',
            Feature::deprecatedClassMessage(self::class, 'v6.7.0.0', 'MailException::mailTransportFailedException')
        );

        parent::__construct(
            'Failed sending mail to following recipients: {{ recipients }} with Error: {{ errorMessage }}',
            ['recipients' => $failedRecipients, 'recipientsString' => implode(', ', $failedRecipients), 'errorMessage' => $e ? $e->getMessage() : 'Unknown error'],
            $e
        );
    }

    public function getErrorCode(): string
    {
        Feature::triggerDeprecationOrThrow(
            'v6.7.0.0',
            Feature::deprecatedClassMessage(self::class, 'v6.7.0.0', 'MailException::mailTransportFailedException')
        );

        return 'CONTENT__MAIL_TRANSPORT_FAILED';
    }

    public function getStatusCode(): int
    {
        Feature::triggerDeprecationOrThrow(
            'v6.7.0.0',
            Feature::deprecatedClassMessage(self::class, 'v6.7.0.0', 'MailException::mailTransportFailedException')
        );

        return Response::HTTP_BAD_REQUEST;
    }
}
