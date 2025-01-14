<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DependencyInjection;

use Cicada\Core\Framework\HttpException;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('core')]
class DependencyInjectionException extends HttpException
{
    public const PROJECT_DIR_IS_NOT_A_STRING = 'FRAMEWORK__PROJECT_DIR_IS_NOT_A_STRING';
    public const BUNDLES_METADATA_IS_NOT_AN_ARRAY = 'FRAMEWORK__BUNDLES_METADATA_IS_NOT_AN_ARRAY';
    public const TAGGED_SERVICE_HAS_WRONG_TYPE = 'FRAMEWORK__TAGGED_SERVICE_HAS_WRONG_TYPE';
    public const PARAMETER_HAS_WRONG_TYPE = 'FRAMEWORK__PARAMETER_HAS_WRONG_TYPE';

    public static function projectDirNotInContainer(): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::PROJECT_DIR_IS_NOT_A_STRING,
            'Container parameter "kernel.project_dir" needs to be a string'
        );
    }

    public static function bundlesMetadataIsNotAnArray(): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::BUNDLES_METADATA_IS_NOT_AN_ARRAY,
            'Container parameter "kernel.bundles_metadata" needs to be an array'
        );
    }

    public static function taggedServiceHasWrongType(string $service, string $tag, string $type): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::TAGGED_SERVICE_HAS_WRONG_TYPE,
            \sprintf('Service "%s" is tagged as "%s" and must therefore be of type "%s".', $service, $tag, $type)
        );
    }

    public static function parameterHasWrongType(string $parameter, string $expectedType, string $actualType): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::PARAMETER_HAS_WRONG_TYPE,
            \sprintf('Parameter "%s" should be: "%s". Got: "%s"', $parameter, $expectedType, $actualType)
        );
    }
}
