<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\TaxFreeConfig;
use Cicada\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Cicada\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Cicada\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('core')]
class TaxFreeConfigFieldSerializer extends JsonFieldSerializer
{
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if ($data->getValue() !== null) {
            $value = $data->getValue();
            unset($value['extensions']);

            $data = new KeyValuePair($data->getKey(), $value, $data->isRaw());
        }

        yield from parent::encode($field, $existence, $data, $parameters);
    }

    public function decode(Field $field, mixed $value): ?TaxFreeConfig
    {
        if ($value === null) {
            return null;
        }

        $raw = json_decode((string) $value, true, 512, \JSON_THROW_ON_ERROR);

        return new TaxFreeConfig(
            (bool) $raw['enabled'],
            (string) $raw['currencyId'],
            (float) $raw['amount']
        );
    }
}
