<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\PlatformRequest;

#[Package('core')]
class ContextTokenStruct extends Struct
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        unset($data['token']);

        $data[PlatformRequest::HEADER_CONTEXT_TOKEN] = $this->getToken();

        return $data;
    }

    public function getApiAlias(): string
    {
        return 'context_token';
    }
}
