<?php

namespace Webkul\Saas\Console\Commands;

use Illuminate\Console\Command;
use Throwable;
use Webkul\Saas\Contracts\TenantDomain;
use Webkul\Saas\Repositories\TenantDomainRepository;
use Webkul\Saas\Services\DomainService;

class VerifyDomainsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:verify-domains
                            {--domain= : Verify a single hostname instead of every pending custom domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check DNS ownership challenges for pending custom domains; SSL is never activated here.';

    /**
     * Execute the console command.
     */
    public function handle(TenantDomainRepository $domains, DomainService $service): int
    {
        $domain = $this->option('domain');

        if ($domain !== null && $domain !== '') {
            $record = $domains->findByHost(strtolower(trim((string) $domain)));

            if (! $record) {
                $this->components->error('No domain matches "'.$domain.'".');

                return self::FAILURE;
            }

            return $this->verifyOne($service, $record) ? self::SUCCESS : self::FAILURE;
        }

        $verified = 0;
        $pending = 0;
        $failed = 0;

        foreach ($domains->pendingCustomDomains() as $record) {
            try {
                $result = $service->verify($record);

                if ($result['verified']) {
                    $verified++;
                } else {
                    $pending++;
                }
            } catch (Throwable $exception) {
                $failed++;
                $this->components->warn('Verification failed for '.$record->hostname.': '.$exception->getMessage());
            }
        }

        $this->table(
            ['Verified', 'Still pending', 'Errors'],
            [[$verified, $pending, $failed]]
        );

        $this->components->success('Domain verification completed.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Verify one domain and report the outcome.
     */
    protected function verifyOne(DomainService $service, TenantDomain $domain): bool
    {
        try {
            $result = $service->verify($domain);
        } catch (Throwable $exception) {
            $this->components->error('Verification failed: '.$exception->getMessage());

            return false;
        }

        if ($result['verified']) {
            $this->components->success($domain->hostname.': '.$result['message']);

            return true;
        }

        $this->components->warn($domain->hostname.': '.$result['message']);

        return false;
    }
}
