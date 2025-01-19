<?php declare(strict_types=1);

namespace Cicada\Core\Content\Mail;

use Cicada\Core\Content\MailTemplate\Exception\MailTransportFailedException;
use Cicada\Core\Framework\CicadaHttpException;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\HttpException;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
#[Package('after-sales')]
class MailException extends HttpException
{
    final public const GIVEN_OPTION_INVALID = 'MAIL__GIVEN_OPTION_INVALID';

    final public const GIVEN_AGENT_INVALID = 'MAIL__GIVEN_AGENT_INVALID';

    final public const MAIL_BODY_TOO_LONG = 'MAIL__MAIL_BODY_TOO_LONG';

    final public const MAIL_TEMPLATE_NOT_FOUND = 'MAIL_TEMPLATE_NOT_FOUND';

    final public const MAIL_TRANSPORT_FAILED = 'CONTENT__MAIL_TRANSPORT_FAILED';

    final public const MAIL_OAUTH_ERROR = 'MAIL__OAUTH_ERROR';

    /**
     * @param string[] $validOptions
     */
    public static function givenSendMailOptionIsInvalid(string $option, array $validOptions): CicadaHttpException
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::GIVEN_OPTION_INVALID,
            'Given sendmail option "{{ option }}" is invalid. Available options: {{ validOptions }}',
            ['option' => $option, 'validOptions' => implode(', ', $validOptions)]
        );
    }

    public static function givenMailAgentIsInvalid(string $agent): CicadaHttpException
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::GIVEN_AGENT_INVALID,
            'Invalid mail agent given "{{ agent }}"',
            ['agent' => $agent]
        );
    }

    public static function mailBodyTooLong(int $maxContentLength): CicadaHttpException
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MAIL_BODY_TOO_LONG,
            'Mail body is too long. Maximum allowed length is {{ maxContentLength }}',
            ['maxContentLength' => $maxContentLength]
        );
    }

    public static function mailTemplateNotFound(string $mailTemplateId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::MAIL_TEMPLATE_NOT_FOUND,
            'Mail template with id {id} not found',
            ['id' => $mailTemplateId]
        );
    }

    public static function mailTransportFailedException(?\Throwable $e = null): self|MailTransportFailedException
    {
        if (!Feature::isActive('v6.7.0.0')) {
            return new MailTransportFailedException([], $e);
        }

        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MAIL_TRANSPORT_FAILED,
            'Failed sending mail with Error: {{ errorMessage }}',
            ['errorMessage' => $e ? $e->getMessage() : 'Unknown error']
        );
    }

    public static function oauthError(string $message): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::MAIL_OAUTH_ERROR,
            $message
        );
    }
}
