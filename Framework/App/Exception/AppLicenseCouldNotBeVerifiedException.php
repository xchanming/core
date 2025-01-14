<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Exception;

use Cicada\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class AppLicenseCouldNotBeVerifiedException extends AppRegistrationException
{
    public function getErrorCode(): string
    {
        return 'FRAMEWORK__APP_LICENSE_COULD_NOT_BE_VERIFIED';
    }
}
