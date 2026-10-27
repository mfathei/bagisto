<?php

namespace Webkul\Saas\Enums;

enum ChallengeType: string
{
    case DnsTxt = 'dns_txt';
    case HttpFile = 'http_file';

    /**
     * Human readable label for the challenge type.
     */
    public function label(): string
    {
        return match ($this) {
            self::DnsTxt => 'DNS TXT record',
            self::HttpFile => 'HTTP file',
        };
    }
}
