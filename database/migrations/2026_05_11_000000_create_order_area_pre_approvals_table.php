<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('auth_db')->create('order_area_pre_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('company_code', 10);
            $table->string('order_type', 10);
            $table->string('order_code', 50);
            $table->unsignedBigInteger('area_manager_user_id');
            $table->string('area_manager_name');
            $table->timestamp('area_manager_approved_at');
            $table->timestamps();

            $table->unique(['company_code', 'order_type', 'order_code'], 'order_area_pre_approvals_unique_order');
            $table->index(['company_code', 'order_type'], 'order_area_pre_approvals_company_type_idx');
            $table->foreign('area_manager_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('auth_db')->dropIfExists('order_area_pre_approvals');
    }
};
