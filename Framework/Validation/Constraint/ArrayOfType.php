<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Validation\Constraint;

use Cicada\Core\Framework\Log\Package;
use Symfony\Component\Validator\Constraint;

#[Package('core')]
class ArrayOfType extends Constraint
{
    final public const INVALID_MESSAGE = 'This value "{{ value }}" should be of type {{ type }}.';
    final public const INVALID_TYPE_MESSAGE = 'This value should be of type array.';

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    public $type;

    public function __construct(string $type)
    {
        parent::__construct();
        $this->type = $type;
    }
}
