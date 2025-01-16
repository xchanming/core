<?php declare(strict_types=1);

namespace Cicada\Core\Content\Newsletter;

use Cicada\Core\Content\Newsletter\Event\NewsletterConfirmEvent;
use Cicada\Core\Content\Newsletter\Event\NewsletterRegisterEvent;
use Cicada\Core\Content\Newsletter\Event\NewsletterUnsubscribeEvent;
use Cicada\Core\Framework\Log\Package;

#[Package('after-sales')]
class NewsletterEvents
{
    final public const NEWSLETTER_CONFIRM_EVENT = NewsletterConfirmEvent::class;

    final public const NEWSLETTER_RECIPIENT_WRITTEN_EVENT = 'newsletter_recipient.written';

    final public const NEWSLETTER_RECIPIENT_DELETED_EVENT = 'newsletter_recipient.deleted';

    final public const NEWSLETTER_RECIPIENT_LOADED_EVENT = 'newsletter_recipient.loaded';

    final public const NEWSLETTER_RECIPIENT_SEARCH_RESULT_LOADED_EVENT = 'newsletter_recipient.search.result.loaded';

    final public const NEWSLETTER_RECIPIENT_AGGREGATION_LOADED_EVENT = 'newsletter_recipient.aggregation.result.loaded';

    final public const NEWSLETTER_RECIPIENT_ID_SEARCH_RESULT_LOADED_EVENT = 'newsletter_recipient.id.search.result.loaded';

    final public const NEWSLETTER_REGISTER_EVENT = NewsletterRegisterEvent::class;

    final public const NEWSLETTER_UNSUBSCRIBE_EVENT = NewsletterUnsubscribeEvent::class;
}
