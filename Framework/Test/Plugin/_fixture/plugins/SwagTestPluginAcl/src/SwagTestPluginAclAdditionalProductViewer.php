<?php declare(strict_types=1);

namespace SwagTestPluginAcl;

use Cicada\Core\Framework\Plugin;

class SwagTestPluginAclAdditionalProductViewer extends Plugin
{
    public function enrichPrivileges(): array
    {
        return [
            'product.viewer' => [
                'swag_demo_data:read',
            ],
        ];
    }
}
