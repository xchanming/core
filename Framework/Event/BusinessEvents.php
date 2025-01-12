<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Event;

use Cicada\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Cicada\Core\Checkout\Customer\Event\CustomerAccountRecoverRequestEvent;
use Cicada\Core\Checkout\Customer\Event\CustomerBeforeLoginEvent;
use Cicada\Core\Checkout\Customer\Event\CustomerDeletedEvent;
use Cicada\Core\Checkout\Customer\Event\CustomerDoubleOptInRegistrationEvent;
use Cicada\Core\Checkout\Customer\Event\CustomerGroupRegistrationAccepted;
use Cicada\Core\Checkout\Customer\Event\CustomerGroupRegistrationDeclined;
use Cicada\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Cicada\Core\Checkout\Customer\Event\CustomerLogoutEvent;
use Cicada\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use Cicada\Core\Checkout\Customer\Event\DoubleOptInGuestOrderEvent;
use Cicada\Core\Checkout\Customer\Event\GuestCustomerRegisterEvent;
use Cicada\Core\Checkout\Order\Event\OrderPaymentMethodChangedEvent;
use Cicada\Core\Content\ContactForm\Event\ContactFormEvent;
use Cicada\Core\Content\MailTemplate\Service\Event\MailBeforeSentEvent;
use Cicada\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use Cicada\Core\Content\MailTemplate\Service\Event\MailSentEvent;
use Cicada\Core\Content\Newsletter\Event\NewsletterConfirmEvent;
use Cicada\Core\Content\Newsletter\Event\NewsletterRegisterEvent;
use Cicada\Core\Content\Newsletter\Event\NewsletterUnsubscribeEvent;
use Cicada\Core\Content\Product\SalesChannel\Review\Event\ReviewFormEvent;
use Cicada\Core\Content\ProductExport\Event\ProductExportLoggingEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\User\Recovery\UserRecoveryRequestEvent;

#[Package('services-settings')]
final class BusinessEvents
{
    public const CHECKOUT_CUSTOMER_BEFORE_LOGIN = CustomerBeforeLoginEvent::EVENT_NAME;

    public const CHECKOUT_CUSTOMER_LOGIN = CustomerLoginEvent::EVENT_NAME;

    public const CHECKOUT_CUSTOMER_LOGOUT = CustomerLogoutEvent::EVENT_NAME;

    public const CHECKOUT_CUSTOMER_DELETED = CustomerDeletedEvent::EVENT_NAME;

    public const USER_RECOVERY_REQUEST = UserRecoveryRequestEvent::EVENT_NAME;

    public const CHECKOUT_ORDER_PLACED = CheckoutOrderPlacedEvent::EVENT_NAME;

    public const CHECKOUT_ORDER_PAYMENT_METHOD_CHANGED = OrderPaymentMethodChangedEvent::EVENT_NAME;

    public const CUSTOMER_ACCOUNT_RECOVER_REQUEST = CustomerAccountRecoverRequestEvent::EVENT_NAME;

    public const CUSTOMER_DOUBLE_OPT_IN_REGISTRATION = CustomerDoubleOptInRegistrationEvent::EVENT_NAME;

    public const CUSTOMER_GROUP_REGISTRATION_ACCEPTED = CustomerGroupRegistrationAccepted::EVENT_NAME;

    public const CUSTOMER_GROUP_REGISTRATION_DECLINED = CustomerGroupRegistrationDeclined::EVENT_NAME;

    public const CUSTOMER_REGISTER = CustomerRegisterEvent::EVENT_NAME;

    public const DOUBLE_OPT_IN_GUEST_ORDER = DoubleOptInGuestOrderEvent::EVENT_NAME;

    public const GUEST_CUSTOMER_REGISTER = GuestCustomerRegisterEvent::EVENT_NAME;

    public const CONTACT_FORM = ContactFormEvent::EVENT_NAME;

    public const REVIEW_FORM = ReviewFormEvent::EVENT_NAME;

    public const MAIL_BEFORE_SENT = MailBeforeSentEvent::EVENT_NAME;

    public const MAIL_BEFORE_VALIDATE = MailBeforeValidateEvent::EVENT_NAME;

    public const MAIL_SENT = MailSentEvent::EVENT_NAME;

    public const NEWSLETTER_CONFIRM = NewsletterConfirmEvent::EVENT_NAME;

    public const NEWSLETTER_REGISTER = NewsletterRegisterEvent::EVENT_NAME;

    public const NEWSLETTER_UNSUBSCRIBE = NewsletterUnsubscribeEvent::EVENT_NAME;

    public const PRODUCT_EXPORT_LOGGING = ProductExportLoggingEvent::NAME;

    private function __construct()
    {
    }
}
