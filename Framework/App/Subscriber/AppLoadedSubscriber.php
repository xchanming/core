<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Subscriber;

use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class AppLoadedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'app.loaded' => 'unserialize',
        ];
    }

    public function unserialize(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $app) {
            if (!$app instanceof AppEntity) {
                continue;
            }

            $iconRaw = $app->getIconRaw();
            if ($iconRaw !== null) {
                $app->setIcon(base64_encode($iconRaw));
            }
        }
    }
}
