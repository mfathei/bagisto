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
        Schema::create('saas_tenant_domains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('tenant_id');
            $table->string('hostname')->unique();
            $table->string('type', 30)->default('platform_subdomain');
            $table->boolean('is_primary')->default(false);
            $table->string('status', 20)->default('pending');
            $table->string('verification_token', 64)->nullable();
            $table->timestamp('verification_token_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('ssl_status', 20)->default('pending');
            $table->timestamp('ssl_issued_at')->nullable();
            $table->string('dns_target')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->string('failure_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_primary']);
            $table->index(['status', 'type']);
            $table->index('verification_token');

            $table->foreign('tenant_id')->references('id')->on('saas_tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saas_tenant_domains');
    }
};
