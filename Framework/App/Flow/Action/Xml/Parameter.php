<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Flow\Action\Xml;

use Cicada\Core\Framework\App\Manifest\Xml\XmlElement;
use Cicada\Core\Framework\App\Manifest\XmlParserUtils;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('core')]
class Parameter extends XmlElement
{
    protected string $type;

    protected string $name;

    protected string $value;

    protected string $id;

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    protected static function parse(\DOMElement $element): array
    {
        return XmlParserUtils::parseAttributes($element);
    }
}
