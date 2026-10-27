<?php

namespace Webkul\Saas\Services;

use Illuminate\Support\Facades\Http;
use Webkul\Saas\Contracts\DomainChallenge as DomainChallengeContract;
use Webkul\Saas\Contracts\DomainOwnershipVerifier;
use Webkul\Saas\Enums\ChallengeType;

class DnsOwnershipVerifier implements DomainOwnershipVerifier
{
    /**
     * The well-known path used for HTTP file verification.
     */
    public const HTTP_PATH = '.well-known/saas-verification.txt';

    /**
     * Verify the challenge using the strategy recorded on it.
     */
    public function verify(DomainChallengeContract $challenge, string $hostname): bool
    {
        $expected = trim((string) $challenge->expected_value);

        if ($expected === '') {
            return false;
        }

        return match ($challenge->type) {
            ChallengeType::DnsTxt => $this->verifyDns($hostname, $expected),
            ChallengeType::HttpFile => $this->verifyHttp($hostname, $expected),
        };
    }

    /**
     * Look for the expected value in the hostname's TXT records.
     */
    public function verifyDns(string $hostname, string $expected): bool
    {
        if (! function_exists('dns_get_record')) {
            return false;
        }

        $records = @dns_get_record($hostname, DNS_TXT);

        if (! is_array($records)) {
            return false;
        }

        foreach ($records as $record) {
            $value = trim((string) ($record['txt'] ?? ''), "\" \t\n\r\0\x0B");

            if (hash_equals($expected, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch the well-known verification file over HTTP.
     */
    public function verifyHttp(string $hostname, string $expected): bool
    {
        if (! $this->resolvesToPublicAddress($hostname)) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->withoutRedirecting()
                ->withHeaders(['Accept' => 'text/plain'])
                ->get('http://'.$hostname.'/'.self::HTTP_PATH);

            if (! $response->successful()) {
                return false;
            }

            return hash_equals($expected, trim((string) $response->body()));
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Reject hostnames pointing at private or reserved addresses.
     */
    protected function resolvesToPublicAddress(string $hostname): bool
    {
        $address = gethostbyname($hostname);

        if ($address === $hostname) {
            return false;
        }

        return filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}
