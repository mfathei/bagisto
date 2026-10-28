<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables owned through an order or customer record.
     *
     * @var array<string, array{source: string, key: string, foreign: string}>
     */
    protected array $tables = [
        'bookings' => ['source' => 'orders', 'key' => 'id', 'foreign' => 'order_id'],
        'eu_withdrawals' => ['source' => 'orders', 'key' => 'id', 'foreign' => 'order_id'],
        'gdpr_data_request' => ['source' => 'customers', 'key' => 'id', 'foreign' => 'customer_id'],
        'rma' => ['source' => 'orders', 'key' => 'id', 'foreign' => 'order_id'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName => $relation) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
            });

            if (! Schema::hasTable($relation['source'])) {
                continue;
            }

            DB::table($tableName)
                ->whereNull('tenant_id')
                ->whereNotNull($relation['foreign'])
                ->update([
                    'tenant_id' => DB::raw(sprintf(
                        '(select %s.tenant_id from %s where %s.%s = %s.%s)',
                        $relation['source'],
                        $relation['source'],
                        $relation['source'],
                        $relation['key'],
                        $tableName,
                        $relation['foreign']
                    )),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_keys($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
