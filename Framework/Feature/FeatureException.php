<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Feature;

use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\HttpException;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('core')]
class FeatureException extends HttpException
{
    final public const FEATURE_NOT_REGISTERED = 'FRAMEWORK__FEATURE_NOT_REGISTERED';
    final public const MAJOR_FEATURE_CANNOT_BE_TOGGLE = 'FRAMEWORK__MAJOR_FEATURE_CANNOT_BE_TOGGLE';
    final public const FEATURE_CANNOT_BE_TOGGLE = 'FRAMEWORK__FEATURE_CANNOT_BE_TOGGLE';
    final public const FEATURE_ERROR = 'FRAMEWORK__FEATURE_ERROR';

    public static function featureNotRegistered(string $feature): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::FEATURE_NOT_REGISTERED,
            'Feature "{{ feature }}" is not registered.',
            ['feature' => $feature]
        );
    }

    public static function featureCannotBeToggled(string $feature): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::FEATURE_CANNOT_BE_TOGGLE,
            'Feature "{{ feature }}" cannot be toggled.',
            ['feature' => $feature]
        );
    }

    /**
     * @deprecated tag:v6.7.0 - Will be removed
     */
    public static function cannotToggleMajor(string $feature): self
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Method "FeatureException::cannotToggleMajor" will be removed as it is unused.');

        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MAJOR_FEATURE_CANNOT_BE_TOGGLE,
            'Feature "{{ feature }}" is major so it cannot be toggled.',
            ['feature' => $feature]
        );
    }

    public static function error(string $message): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::FEATURE_ERROR,
            $message
        );
    }
}
