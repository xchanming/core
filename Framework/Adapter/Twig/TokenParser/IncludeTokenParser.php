<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Adapter\Twig\TokenParser;

use Cicada\Core\Framework\Adapter\Twig\Node\SwInclude;
use Cicada\Core\Framework\Adapter\Twig\TemplateFinder;
use Cicada\Core\Framework\Log\Package;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\IncludeNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

#[Package('core')]
final class IncludeTokenParser extends AbstractTokenParser
{
    public function __construct(private readonly TemplateFinder $finder)
    {
    }

    /**
     * @return Node
     */
    public function parse(Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();
        \assert($expr instanceof AbstractExpression);

        [$variables, $only, $ignoreMissing] = $this->parseArguments();

        // resolves parent template
        if ($expr->hasAttribute('value')) {
            // set pointer to next value (contains the template file name)
            $parent = $this->finder->find($expr->getAttribute('value'), $ignoreMissing);

            $expr->setAttribute('value', $parent);

            return new IncludeNode($expr, $variables, $only, $ignoreMissing, $token->getLine());
        }

        return new SwInclude($expr, $variables, $only, $ignoreMissing, $token->getLine());
    }

    public function getTag(): string
    {
        return 'sw_include';
    }

    /**
     * @return array{AbstractExpression|null, bool, bool}
     */
    private function parseArguments(): array
    {
        $stream = $this->parser->getStream();

        $ignoreMissing = false;
        if ($stream->nextIf(Token::NAME_TYPE, 'ignore')) {
            $stream->expect(Token::NAME_TYPE, 'missing');

            $ignoreMissing = true;
        }

        $variables = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $only = false;
        if ($stream->nextIf(Token::NAME_TYPE, 'only')) {
            $only = true;
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return [$variables, $only, $ignoreMissing];
    }
}
