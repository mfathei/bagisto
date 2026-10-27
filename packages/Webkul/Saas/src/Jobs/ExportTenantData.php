<?php

namespace Webkul\Saas\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\AuditLogger;
use Webkul\Saas\Services\TenantContext;

class ExportTenantData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts before the export is abandoned.
     */
    public int $tries = 2;

    /**
     * Create a new export job.
     */
    public function __construct(public int $tenantId, public ?int $requestedBy = null) {}

    /**
     * Write a private, per-resource export of the tenant's data.
     */
    public function handle(TenantRepository $tenants, TenantContext $context, AuditLogger $audit): void
    {
        $context->clear();

        $tenant = $tenants->find($this->tenantId);

        if (! $tenant) {
            return;
        }

        $diskName = (string) config('saas.tenants.export_disk', 'local');
        $disk = Storage::disk($diskName);
        $directory = trim((string) config('saas.tenants.export_prefix', 'saas/exports'), '/')
            .'/'.$tenant->uuid.'/'.now()->format('YmdHis');

        $disk->makeDirectory($directory);

        $manifest = [
            'tenant' => [
                'uuid' => $tenant->uuid,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status->value,
            ],
            'generated_at' => now()->toIso8601String(),
            'requested_by' => $this->requestedBy,
            'resources' => [],
        ];

        foreach ($this->resources($tenant->getKey()) as $name => $query) {
            $path = $directory.'/'.$name.'.ndjson';
            $count = 0;
            $buffer = '';

            foreach ($query->orderBy('id')->cursor() as $row) {
                $buffer .= json_encode((array) $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
                $count++;

                if (strlen($buffer) > 1048576) {
                    $disk->append($path, $buffer);
                    $buffer = '';
                }
            }

            if ($buffer !== '') {
                $disk->append($path, $buffer);
            }

            $manifest['resources'][$name] = [
                'path' => $path,
                'records' => $count,
                'sha256' => $disk->exists($path) ? hash('sha256', $disk->get($path)) : hash('sha256', ''),
            ];
        }

        $disk->put($directory.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $audit->system(AuditAction::TenantExported, $tenant, [
            'description' => 'Tenant data export completed.',
            'metadata' => ['disk' => $diskName, 'directory' => $directory, 'requested_by' => $this->requestedBy],
        ]);
    }

    /**
     * Clear tenant context when the export fails.
     */
    public function failed(?Throwable $exception): void
    {
        app(TenantContext::class)->clear();
    }

    /**
     * Build the tenant-scoped export queries.
     *
     * @return array<string, Builder>
     */
    protected function resources(int $tenantId): array
    {
        $productIds = DB::table('products')->select('id')->where('tenant_id', $tenantId);
        $categoryIds = DB::table('categories')->select('id')->where('tenant_id', $tenantId);
        $customerIds = DB::table('customers')->select('id')->where('tenant_id', $tenantId);
        $orderIds = DB::table('orders')->select('id')->where('tenant_id', $tenantId);
        $invoiceIds = DB::table('invoices')->select('id')->where('tenant_id', $tenantId);
        $shipmentIds = DB::table('shipments')->select('id')->where('tenant_id', $tenantId);
        $refundIds = DB::table('refunds')->select('id')->where('tenant_id', $tenantId);
        $cartIds = DB::table('cart')->select('id')->where('tenant_id', $tenantId);

        return [
            'domains' => DB::table('saas_tenant_domains')->where('tenant_id', $tenantId),
            'memberships' => DB::table('saas_tenant_users')->where('tenant_id', $tenantId),
            'subscriptions' => DB::table('saas_subscriptions')->where('tenant_id', $tenantId),
            'billing_records' => DB::table('saas_billing_records')->where('tenant_id', $tenantId),
            'usage_metrics' => DB::table('saas_usage_metrics')->where('tenant_id', $tenantId),
            'audit_logs' => DB::table('saas_audit_logs')->where('tenant_id', $tenantId),
            'inventory_sources' => DB::table('inventory_sources')->where('tenant_id', $tenantId),
            'products' => DB::table('products')->whereIn('id', $productIds),
            'product_attribute_values' => DB::table('product_attribute_values')->whereIn('product_id', $productIds),
            'product_images' => DB::table('product_images')->whereIn('product_id', $productIds),
            'product_inventories' => DB::table('product_inventories')->whereIn('product_id', $productIds),
            'categories' => DB::table('categories')->whereIn('id', $categoryIds),
            'category_translations' => DB::table('category_translations')->whereIn('category_id', $categoryIds),
            'customers' => DB::table('customers')->whereIn('id', $customerIds),
            'customer_addresses' => DB::table('addresses')->whereIn('customer_id', $customerIds),
            'carts' => DB::table('cart')->whereIn('id', $cartIds),
            'cart_items' => DB::table('cart_items')->whereIn('cart_id', $cartIds),
            'orders' => DB::table('orders')->whereIn('id', $orderIds),
            'order_items' => DB::table('order_items')->whereIn('order_id', $orderIds),
            'order_addresses' => DB::table('order_addresses')->whereIn('order_id', $orderIds),
            'invoices' => DB::table('invoices')->whereIn('id', $invoiceIds),
            'shipments' => DB::table('shipments')->whereIn('id', $shipmentIds),
            'refunds' => DB::table('refunds')->whereIn('id', $refundIds),
        ];
    }
}
