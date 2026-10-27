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
        Schema::create('saas_usage_metrics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('metric', 40);
            $table->string('period', 20)->default('current');
            $table->decimal('value', 16, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'metric', 'period']);
            $table->index(['tenant_id', 'metric']);

            $table->foreign('tenant_id')->references('id')->on('saas_tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saas_usage_metrics');
    }
};
