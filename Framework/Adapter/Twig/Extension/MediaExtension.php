<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Adapter\Twig\Extension;

use Cicada\Core\Content\Media\MediaCollection;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Package('core')]
class MediaExtension extends AbstractExtension
{
    /**
     * @internal
     */
    public function __construct(private readonly EntityRepository $mediaRepository)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('searchMedia', $this->searchMedia(...)),
        ];
    }

    public function searchMedia(array $ids, Context $context): MediaCollection
    {
        if (empty($ids)) {
            return new MediaCollection();
        }

        $criteria = new Criteria($ids);

        /** @var MediaCollection $media */
        $media = $this->mediaRepository
            ->search($criteria, $context)
            ->getEntities();

        return $media;
    }
}
