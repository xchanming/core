<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\Exception;

use Cicada\Core\Framework\CicadaHttpException;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class LanguageOfProductReviewDeleteException extends CicadaHttpException
{
    public function __construct(
        string $language,
        ?\Throwable $e = null
    ) {
        parent::__construct(
            'The language "{{ language }}" cannot be deleted because product reviews with this language exist.',
            ['language' => $language],
            $e
        );
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__LANGUAGE_OF_PRODUCT_REVIEW_DELETE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
