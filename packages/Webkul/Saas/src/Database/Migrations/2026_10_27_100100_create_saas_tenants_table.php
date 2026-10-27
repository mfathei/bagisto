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
        Schema::create('saas_tenants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legal_name')->nullable();
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('status', 30)->default('pending');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('current_subscription_id')->nullable();
            $table->unsignedInteger('channel_id')->nullable();
            $table->unsignedInteger('owner_admin_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('suspension_reason', 500)->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('default_locale', 12)->default('en');
            $table->string('default_currency', 3)->default('USD');
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('email');
            $table->index(['status', 'created_at']);
            $table->index('current_subscription_id');

            $table->foreign('plan_id')->references('id')->on('saas_plans')->nullOnDelete();
            $table->foreign('channel_id')->references('id')->on('channels')->nullOnDelete();
            $table->foreign('owner_admin_id')->references('id')->on('admins')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saas_tenants');
    }
};
