<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\CustomField\CustomFieldService;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
#[Package('core')]
class CustomFieldsAccessorBuilder extends JsonFieldAccessorBuilder
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CustomFieldService $customFieldService,
        Connection $connection
    ) {
        parent::__construct($connection);
    }

    public function buildAccessor(string $root, Field $field, Context $context, string $accessor): ?string
    {
        if (!$field instanceof CustomFields) {
            return null;
        }

        /**
         * Possible paths / attribute names:
         * - propertyName.attribute_name -> attribute_name
         * - propertyName.attribute_name.foo -> attribute_name
         * - propertyName."attribute.name" -> attribute.name
         * - propertyName."attribute.name".foo -> attribute.name
         *
         * @var string $attributeName
         */
        $attributeName = preg_replace(
            '#^' . preg_quote($field->getPropertyName(), '#') . '\.("([^"]*)"|([^.]*)).*#',
            '$2$3',
            $accessor
        );
        $attributeField = $this->customFieldService->getCustomField($attributeName)
            ?? new JsonField($attributeName, $attributeName);

        $field->setPropertyMapping([$attributeField]);

        return parent::buildAccessor($root, $field, $context, $accessor);
    }
}
