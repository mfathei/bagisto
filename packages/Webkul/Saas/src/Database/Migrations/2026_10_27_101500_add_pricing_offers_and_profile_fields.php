<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saas_plans', function (Blueprint $table) {
            $table->decimal('yearly_price', 12, 2)->default(0)->after('price');
            $table->unsignedInteger('premium_themes')->default(0)->after('trial_days');
            $table->string('offer_status', 20)->nullable()->after('premium_themes');
            $table->string('offer_title', 191)->nullable()->after('offer_status');
            $table->string('offer_type', 20)->nullable()->after('offer_title');
            $table->decimal('offer_discount', 12, 2)->nullable()->after('offer_type');
        });

        Schema::table('saas_tenants', function (Blueprint $table) {
            $table->string('cname', 191)->nullable()->after('phone');
            $table->string('logo', 191)->nullable()->after('cname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saas_tenants', function (Blueprint $table) {
            $table->dropColumn(['cname', 'logo']);
        });

        Schema::table('saas_plans', function (Blueprint $table) {
            $table->dropColumn([
                'yearly_price',
                'premium_themes',
                'offer_status',
                'offer_title',
                'offer_type',
                'offer_discount',
            ]);
        });
    }
};
