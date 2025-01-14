<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Routing;

use Cicada\Core\Framework\HttpException;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Routing\Exception\CustomerNotLoggedInRoutingException;
use Symfony\Component\HttpFoundation\Response;

#[Package('core')]
class RoutingException extends HttpException
{
    public const MISSING_REQUEST_PARAMETER_CODE = 'FRAMEWORK__MISSING_REQUEST_PARAMETER';
    public const INVALID_REQUEST_PARAMETER_CODE = 'FRAMEWORK__INVALID_REQUEST_PARAMETER';
    public const APP_INTEGRATION_NOT_FOUND = 'FRAMEWORK__APP_INTEGRATION_NOT_FOUND';
    public const LANGUAGE_NOT_FOUND = 'FRAMEWORK__LANGUAGE_NOT_FOUND';

    public const CUSTOMER_NOT_LOGGED_IN_CODE = 'FRAMEWORK__ROUTING_CUSTOMER_NOT_LOGGED_IN';
    public const ACCESS_DENIED_FOR_XML_HTTP_REQUEST = 'FRAMEWORK__ACCESS_DENIED_FOR_XML_HTTP_REQUEST';

    public static function invalidRequestParameter(string $name): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_REQUEST_PARAMETER_CODE,
            'The parameter "{{ parameter }}" is invalid.',
            ['parameter' => $name]
        );
    }

    public static function missingRequestParameter(string $name, string $path = ''): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MISSING_REQUEST_PARAMETER_CODE,
            'Parameter "{{ parameterName }}" is missing.',
            ['parameterName' => $name, 'path' => $path]
        );
    }

    public static function languageNotFound(?string $languageId): self
    {
        return new self(
            Response::HTTP_PRECONDITION_FAILED,
            self::LANGUAGE_NOT_FOUND,
            self::$couldNotFindMessage,
            ['entity' => 'language', 'field' => 'id', 'value' => $languageId]
        );
    }

    public static function appIntegrationNotFound(string $integrationId): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::APP_INTEGRATION_NOT_FOUND,
            self::$couldNotFindMessage,
            ['entity' => 'app integration', 'field' => 'id', 'value' => $integrationId]
        );
    }

    public static function customerNotLoggedIn(): CustomerNotLoggedInRoutingException
    {
        return new CustomerNotLoggedInRoutingException(
            Response::HTTP_FORBIDDEN,
            self::CUSTOMER_NOT_LOGGED_IN_CODE,
            'Customer is not logged in.'
        );
    }

    public static function accessDeniedForXmlHttpRequest(): self
    {
        return new self(
            Response::HTTP_FORBIDDEN,
            self::ACCESS_DENIED_FOR_XML_HTTP_REQUEST,
            'PageController can\'t be requested via XmlHttpRequest.'
        );
    }
}
