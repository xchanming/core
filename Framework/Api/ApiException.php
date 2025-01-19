<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Api;

use Cicada\Core\Framework\Api\Context\AdminApiSource;
use Cicada\Core\Framework\Api\Context\Exception\InvalidContextSourceException;
use Cicada\Core\Framework\Api\Controller\Exception\ExpectedUserHttpException;
use Cicada\Core\Framework\Api\Exception\ExpectationFailedException;
use Cicada\Core\Framework\Api\Exception\InvalidSalesChannelIdException;
use Cicada\Core\Framework\Api\Exception\InvalidSyncOperationException;
use Cicada\Core\Framework\Api\Exception\InvalidVersionNameException;
use Cicada\Core\Framework\Api\Exception\LiveVersionDeleteException;
use Cicada\Core\Framework\Api\Exception\MissingPrivilegeException;
use Cicada\Core\Framework\Api\Exception\NoEntityClonedException;
use Cicada\Core\Framework\Api\Exception\ResourceNotFoundException;
use Cicada\Core\Framework\Api\Exception\UnsupportedEncoderInputException;
use Cicada\Core\Framework\CicadaHttpException;
use Cicada\Core\Framework\DataAbstractionLayer\Exception\DefinitionNotFoundException;
use Cicada\Core\Framework\DataAbstractionLayer\Exception\MissingReverseAssociation;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\HttpException;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Routing\Exception\SalesChannelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

#[Package('core')]
class ApiException extends HttpException
{
    public const API_INVALID_SYNC_CRITERIA_EXCEPTION = 'API_INVALID_SYNC_CRITERIA_EXCEPTION';
    public const API_RESOLVER_NOT_FOUND_EXCEPTION = 'API_RESOLVER_NOT_FOUND_EXCEPTION';

    public const API_UNSUPPORTED_ASSOCIATION_FIELD = 'FRAMEWORK__API_UNSUPPORTED_ASSOCIATION_FIELD_EXCEPTION';
    public const API_INVALID_SYNC_OPERATION_EXCEPTION = 'FRAMEWORK__INVALID_SYNC_OPERATION';
    public const API_INVALID_SCHEMA_DEFINITION_EXCEPTION = 'FRAMEWORK__INVALID_SCHEMA_DEFINITION';

    public const API_NOT_EXISTING_RELATION_EXCEPTION = 'FRAMEWORK__NOT_EXISTING_RELATION_EXCEPTION';

    public const API_UNSUPPORTED_OPERATION_EXCEPTION = 'FRAMEWORK__UNSUPPORTED_OPERATION_EXCEPTION';
    public const API_INVALID_VERSION_ID = 'FRAMEWORK__INVALID_VERSION_ID';
    public const API_TYPE_PARAMETER_INVALID = 'FRAMEWORK__API_TYPE_PARAMETER_INVALID';
    public const API_APP_ID_PARAMETER_IS_MISSING = 'FRAMEWORK__APP_ID_PARAMETER_IS_MISSING';
    public const API_SALES_CHANNEL_ID_PARAMETER_IS_MISSING = 'FRAMEWORK__API_SALES_CHANNEL_ID_PARAMETER_IS_MISSING';
    public const API_CUSTOMER_ID_PARAMETER_IS_MISSING = 'FRAMEWORK__API_CUSTOMER_ID_PARAMETER_IS_MISSING';
    public const API_SHIPPING_COSTS_PARAMETER_IS_MISSING = 'FRAMEWORK__API_SHIPPING_COSTS_PARAMETER_IS_MISSING';
    public const API_UNABLE_GENERATE_BUNDLE = 'FRAMEWORK__API_UNABLE_GENERATE_BUNDLE';
    public const API_INVALID_ACCESS_KEY_EXCEPTION = 'FRAMEWORK__API_INVALID_ACCESS_KEY';
    public const API_INVALID_ACCESS_KEY_IDENTIFIER_EXCEPTION = 'FRAMEWORK__API_INVALID_ACCESS_KEY_IDENTIFIER';

    public const API_INVALID_SYNC_RESOLVERS = 'FRAMEWORK__API_INVALID_SYNC_RESOLVERS';
    public const API_SALES_CHANNEL_MAINTENANCE_MODE = 'FRAMEWORK__API_SALES_CHANNEL_MAINTENANCE_MODE';
    public const API_SYNC_RESOLVER_FIELD_NOT_FOUND = 'FRAMEWORK__API_SYNC_RESOLVER_FIELD_NOT_FOUND';
    public const API_INVALID_ASSOCIATION_FIELD = 'FRAMEWORK__API_INVALID_ASSOCIATION';
    public const API_UNSUPPORTED_ENCODER_INPUT = 'FRAMEWORK__API_UNSUPPORTED_ENCODER_INPUT';
    public const API_INVALID_CONTEXT_SOURCE = 'FRAMEWORK__INVALID_CONTEXT_SOURCE';
    public const API_EXPECTED_USER = 'FRAMEWORK__API_EXPECTED_USER';
    public const API_INVALID_SCOPE_ACCESS_TOKEN = 'FRAMEWORK__INVALID_SCOPE_ACCESS_TOKEN';

    public const API_ROUTES_ARE_LOADED_ALREADY = 'FRAMEWORK__API_ROUTES_ARE_LOADED_ALREADY';
    public const API_NOTIFICATION_THROTTLED = 'FRAMEWORK__NOTIFICATION_THROTTLED';

    /**
     * @param array<array{pointer: string, entity: string}> $exceptions
     */
    public static function canNotResolveForeignKeysException(array $exceptions): self
    {
        $message = [];
        $parameters = [];

        foreach ($exceptions as $i => $exception) {
            $message[] = \sprintf(
                'Can not resolve foreign key at position %s. Reference field: %s',
                $exception['pointer'],
                $exception['entity']
            );
            $parameters['pointer-' . $i] = $exception['pointer'];
            $parameters['field-' . $i] = $exception['entity'];
        }

        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_INVALID_SYNC_RESOLVERS,
            implode("\n", $message),
            $parameters
        );
    }

    public static function invalidSyncCriteriaException(string $operationKey): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_INVALID_SYNC_CRITERIA_EXCEPTION,
            \sprintf('Sync operation %s, with action "delete", requires a criteria with at least one filter and can only be applied for mapping entities', $operationKey)
        );
    }

    public static function invalidSyncOperationException(string $message): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_INVALID_SYNC_OPERATION_EXCEPTION,
            $message
        );
    }

    public static function resolverNotFoundException(string $key): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_RESOLVER_NOT_FOUND_EXCEPTION,
            \sprintf('Foreign key resolver for key %s not found', $key)
        );
    }

    public static function unsupportedAssociation(string $field): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_UNSUPPORTED_ASSOCIATION_FIELD,
            'Unsupported association for field {{ field }}',
            ['field' => $field]
        );
    }

    /**
     * @param string[] $permissions
     */
    public static function missingPrivileges(array $permissions): CicadaHttpException
    {
        return new MissingPrivilegeException($permissions);
    }

    public static function missingReverseAssociation(string $entity, string $parentEntity): CicadaHttpException
    {
        return new MissingReverseAssociation($entity, $parentEntity);
    }

    public static function definitionNotFound(DefinitionNotFoundException $exception): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            $exception->getErrorCode(),
            $exception->getMessage(),
        );
    }

    public static function pathIsNoAssociationField(string $path): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_INVALID_ASSOCIATION_FIELD,
            'Field "%s" is not a valid association field.',
            ['path' => $path]
        );
    }

    public static function notExistingRelation(string $path): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::API_NOT_EXISTING_RELATION_EXCEPTION,
            'Resource at path "{{ path }}" is not an existing relation.',
            ['path' => $path]
        );
    }

    public static function unsupportedMediaType(string $contentType): SymfonyHttpException
    {
        return new UnsupportedMediaTypeHttpException(\sprintf('The Content-Type "%s" is unsupported.', $contentType));
    }

    public static function badRequest(string $message): SymfonyHttpException
    {
        return new BadRequestHttpException($message);
    }

    /**
     * @param string[] $allow
     */
    public static function methodNotAllowed(array $allow, string $message): SymfonyHttpException
    {
        return new MethodNotAllowedHttpException($allow, $message);
    }

    public static function unauthorized(string $challenge, string $message): SymfonyHttpException
    {
        return new UnauthorizedHttpException($challenge, $message);
    }

    public static function noEntityCloned(string $entity, string $id): CicadaHttpException
    {
        return new NoEntityClonedException($entity, $id);
    }

    /**
     * @param string[] $fails
     */
    public static function expectationFailed(array $fails): CicadaHttpException
    {
        return new ExpectationFailedException($fails);
    }

    public static function invalidSyncOperation(string $message): CicadaHttpException
    {
        return new InvalidSyncOperationException($message);
    }

    public static function invalidSalesChannelId(string $salesChannelId): CicadaHttpException
    {
        return new InvalidSalesChannelIdException($salesChannelId);
    }

    public static function invalidVersionName(): CicadaHttpException
    {
        return new InvalidVersionNameException();
    }

    public static function salesChannelNotFound(): CicadaHttpException
    {
        return new SalesChannelNotFoundException();
    }

    public static function deleteLiveVersion(): CicadaHttpException
    {
        return new LiveVersionDeleteException();
    }

    /**
     * @param array<mixed> $payload
     */
    public static function resourceNotFound(string $entity, array $payload): CicadaHttpException
    {
        return new ResourceNotFoundException($entity, $payload);
    }

    public static function unsupportedOperation(string $operation): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_UNSUPPORTED_OPERATION_EXCEPTION,
            'Unsupported {{ operation }} operation.',
            ['operation' => $operation]
        );
    }

    public static function invalidVersionId(string $versionId): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_INVALID_VERSION_ID,
            'versionId {{ versionId }} is not a valid uuid.',
            ['versionId' => $versionId]
        );
    }

    public static function invalidApiType(string $type): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_TYPE_PARAMETER_INVALID,
            'Parameter type {{ type }} is invalid.',
            ['type' => $type]
        );
    }

    public static function appIdParameterIsMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_APP_ID_PARAMETER_IS_MISSING,
            'Parameter "id" is missing.',
        );
    }

    public static function salesChannelIdParameterIsMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_SALES_CHANNEL_ID_PARAMETER_IS_MISSING,
            'Parameter "salesChannelId" is missing.',
        );
    }

    public static function customerIdParameterIsMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_CUSTOMER_ID_PARAMETER_IS_MISSING,
            'Parameter "customerId" is missing.',
        );
    }

    public static function shippingCostsParameterIsMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_SHIPPING_COSTS_PARAMETER_IS_MISSING,
            'Parameter "shippingCosts" is missing.',
        );
    }

    public static function unableGenerateBundle(string $bundleName): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::API_UNABLE_GENERATE_BUNDLE,
            'Unable to generate bundle directory for bundle "{{ bundleName }}".',
            ['bundleName' => $bundleName]
        );
    }

    public static function invalidSchemaDefinitions(string $filename, \JsonException $exception): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::API_INVALID_SCHEMA_DEFINITION_EXCEPTION,
            \sprintf('Failed to parse JSON file "%s": %s', $filename, $exception->getMessage()),
        );
    }

    /**
     * @deprecated tag:v6.7.0 - reason:exception-change - Will return status code 403 instead of 500
     */
    public static function invalidAccessKey(): self
    {
        if (!Feature::isActive('v6.7.0.0')) {
            return new self(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                self::API_INVALID_ACCESS_KEY_EXCEPTION,
                'Access key is invalid and could not be identified.',
            );
        }

        return new self(
            Response::HTTP_FORBIDDEN,
            self::API_INVALID_ACCESS_KEY_EXCEPTION,
            'Access key is invalid and could not be identified.',
        );
    }

    public static function invalidAccessKeyIdentifier(): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::API_INVALID_ACCESS_KEY_IDENTIFIER_EXCEPTION,
            'Given identifier for access key is invalid.',
        );
    }

    public static function salesChannelInMaintenanceMode(): self
    {
        return new self(
            Response::HTTP_SERVICE_UNAVAILABLE,
            self::API_SALES_CHANNEL_MAINTENANCE_MODE,
            'The sales channel is in maintenance mode.',
        );
    }

    public static function canNotResolveResolverField(string $entity, string $fieldName): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::API_SYNC_RESOLVER_FIELD_NOT_FOUND,
            'Can not resolve entity field name {{ entity }}.{{ field }} for sync operation resolver',
            ['entity' => $entity, 'field' => $fieldName]
        );
    }

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - Will only return `self` in the future
     */
    public static function unsupportedEncoderInput(): self|UnsupportedEncoderInputException
    {
        if (!Feature::isActive('v6.7.0.0')) {
            return new UnsupportedEncoderInputException();
        }

        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::API_UNSUPPORTED_ENCODER_INPUT,
            'Unsupported encoder data provided. Only entities and entity collections are supported',
        );
    }

    public static function apiRoutesAreAlreadyLoaded(): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::API_ROUTES_ARE_LOADED_ALREADY,
            'API routes are already loaded',
        );
    }

    public static function invalidAdminSource(string $actual): self
    {
        return new InvalidContextSourceException(AdminApiSource::class, $actual);
    }

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - Will only return `self` in the future
     */
    public static function userNotLoggedIn(): self|ExpectedUserHttpException
    {
        if (!Feature::isActive('v6.7.0.0')) {
            return new ExpectedUserHttpException();
        }

        return new self(
            Response::HTTP_FORBIDDEN,
            self::API_EXPECTED_USER,
            'For this interaction an authenticated user login is required.'
        );
    }

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - Will only return `self` in the future
     */
    public static function invalidScopeAccessToken(string $identifier): self|AccessDeniedHttpException
    {
        if (!Feature::isActive('v6.7.0.0')) {
            return new AccessDeniedHttpException(\sprintf('This access token does not have the scope "%s" to process this Request', $identifier));
        }

        return new self(
            Response::HTTP_FORBIDDEN,
            self::API_INVALID_SCOPE_ACCESS_TOKEN,
            'This access token does not have the scope "{{ scope }}" to process this Request',
            ['scope' => $identifier]
        );
    }

    public static function notificationThrottled(int $waitTime, \Throwable $e): self
    {
        return new self(
            Response::HTTP_TOO_MANY_REQUESTS,
            self::API_NOTIFICATION_THROTTLED,
            'Notification throttled for {{ seconds }} seconds.',
            ['seconds' => $waitTime],
            $e
        );
    }
}
