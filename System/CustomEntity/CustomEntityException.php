<?php declare(strict_types=1);

namespace Cicada\Core\System\CustomEntity;

use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\HttpException;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\CustomEntity\Exception\CustomEntityNotFoundException;
use Cicada\Core\System\CustomEntity\Exception\CustomEntityXmlParsingException;
use Cicada\Core\System\SystemConfig\Exception\XmlParsingException;
use Symfony\Component\HttpFoundation\Response;

#[Package('core')]
class CustomEntityException extends HttpException
{
    public const CUSTOM_FIELDS_AWARE_NO_LABEL_PROPERTY = 'NO_LABEL_PROPERTY';
    public const CUSTOM_FIELDS_AWARE_LABEL_PROPERTY_NOT_DEFINED = 'LABEL_PROPERTY_NOT_DEFINED';
    public const CUSTOM_FIELDS_AWARE_LABEL_PROPERTY_WRONG_TYPE = 'LABEL_PROPERTY_WRONG_TYPE';

    public const XML_PARSE_ERROR = 'SYSTEM_CUSTOM_ENTITY__XML_PARSE_ERROR';

    public const NOT_FOUND = 'FRAMEWORK__CUSTOM_ENTITY_NOT_FOUND';

    public static function noLabelProperty(): self
    {
        return new self(Response::HTTP_INTERNAL_SERVER_ERROR, self::CUSTOM_FIELDS_AWARE_NO_LABEL_PROPERTY, 'Entity must have a label property when it is custom field aware');
    }

    public static function labelPropertyNotDefined(string $labelProperty): self
    {
        return new self(Response::HTTP_INTERNAL_SERVER_ERROR, self::CUSTOM_FIELDS_AWARE_LABEL_PROPERTY_NOT_DEFINED, 'Entity label_property "{{ labelProperty }}" is not defined in fields', ['labelProperty' => $labelProperty]);
    }

    public static function labelPropertyWrongType(string $labelProperty): self
    {
        return new self(Response::HTTP_INTERNAL_SERVER_ERROR, self::CUSTOM_FIELDS_AWARE_LABEL_PROPERTY_WRONG_TYPE, 'Entity label_property "{{ labelProperty }}" must be a string field', ['labelProperty' => $labelProperty]);
    }

    public static function notFound(string $entityName): self|CustomEntityNotFoundException
    {
        if (!Feature::isActive('v6.7.0.0')) {
            return new CustomEntityNotFoundException($entityName);
        }

        return new self(Response::HTTP_NOT_FOUND, self::NOT_FOUND, 'Custom entity "{{ entityName }}" not found', ['entityName' => $entityName]);
    }

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - Will only return `self` in the future
     */
    public static function xmlParsingException(string $file, string $message): self|XmlParsingException
    {
        if (!Feature::isActive('v6.7.0.0')) {
            return new XmlParsingException($file, $message);
        }

        return new CustomEntityXmlParsingException($file, $message);
    }
}
