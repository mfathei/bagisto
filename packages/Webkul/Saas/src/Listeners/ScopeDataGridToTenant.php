<?php

namespace Webkul\Saas\Listeners;

use Illuminate\Database\Query\Builder;
use Webkul\DataGrid\DataGrid;
use Webkul\Saas\Services\TenantContext;

class ScopeDataGridToTenant
{
    /**
     * Tables that carry their own tenant_id column.
     *
     * @var string[]
     */
    protected const OWNED_TABLES = [
        'admins',
        'bookings',
        'cart',
        'cart_rules',
        'catalog_rules',
        'categories',
        'cms_pages',
        'customers',
        'eu_withdrawals',
        'gdpr_data_request',
        'inventory_sources',
        'invoices',
        'order_transactions',
        'orders',
        'product_reviews',
        'products',
        'refunds',
        'rma',
        'shipments',
        'wishlist_items',
    ];

    /**
     * Tables that reach their tenant through a related table.
     *
     * @var array<string, array{table: string, owner: string, foreign: string}>
     */
    protected const RELATED_TABLES = [
        'cart_rule_coupons' => [
            'table' => 'cart_rules',
            'owner' => 'id',
            'foreign' => 'cart_rule_id',
        ],
        'product_flat' => [
            'table' => 'products',
            'owner' => 'id',
            'foreign' => 'product_id',
        ],
    ];

    /**
     * Create a new listener instance.
     */
    public function __construct(protected TenantContext $context) {}

    /**
     * Scope a prepared datagrid query to the active tenant.
     */
    public function handle(string $event, array $payload): void
    {
        $datagrid = $payload[0] ?? null;

        if (! $datagrid instanceof DataGrid) {
            return;
        }

        if (! config('saas.enabled') || ! $this->context->check()) {
            return;
        }

        $builder = $datagrid->getQueryBuilder();

        if (! $builder instanceof Builder) {
            return;
        }

        [$table, $alias] = $this->parseFrom($builder->from);

        if (! $table) {
            return;
        }

        if (isset(self::RELATED_TABLES[$table])) {
            $this->applyRelatedScope($builder, $table, $alias);

            return;
        }

        if (in_array($table, self::OWNED_TABLES, true)) {
            $this->applyDirectScope($builder, $alias ?: $table);
        }
    }

    /**
     * Extract the table and alias from a query builder "from" clause.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function parseFrom(mixed $from): array
    {
        if (! is_string($from)) {
            return [null, null];
        }

        $matched = preg_match(
            '/^\s*([A-Za-z0-9_]+)(?:\s+as\s+([A-Za-z0-9_]+))?\s*$/i',
            $from,
            $matches
        );

        if (! $matched) {
            return [null, null];
        }

        return [$matches[1], $matches[2] ?? null];
    }

    /**
     * Constrain a tenant-owned table through its own tenant_id column.
     */
    protected function applyDirectScope(Builder $builder, string $table): void
    {
        if ($this->alreadyScoped($builder, $table)) {
            return;
        }

        $builder->where($table.'.tenant_id', $this->context->id());
    }

    /**
     * Constrain a shared table through the tenant-owned table it belongs to.
     */
    protected function applyRelatedScope(Builder $builder, string $table, ?string $alias): void
    {
        $relation = self::RELATED_TABLES[$table];

        $joinAlias = 'saas_tenant_'.$relation['table'];

        $root = $alias ?: $table;

        if (! $this->hasJoin($builder, $joinAlias)) {
            $builder->join(
                $relation['table'].' as '.$joinAlias,
                $joinAlias.'.'.$relation['owner'],
                '=',
                $root.'.'.$relation['foreign']
            );
        }

        if ($this->alreadyScoped($builder, $joinAlias)) {
            return;
        }

        $builder->where($joinAlias.'.tenant_id', $this->context->id());
    }

    /**
     * Whether the query already constrains the given table's tenant.
     */
    protected function alreadyScoped(Builder $builder, string $table): bool
    {
        foreach ($builder->wheres as $where) {
            if (($where['column'] ?? null) === $table.'.tenant_id') {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the query already joins the given alias.
     */
    protected function hasJoin(Builder $builder, string $alias): bool
    {
        foreach ($builder->joins ?? [] as $join) {
            $table = (string) $join->table;

            if ($table === $alias || str_contains($table, ' as '.$alias)) {
                return true;
            }
        }

        return false;
    }
}
