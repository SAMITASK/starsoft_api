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
        Schema::connection('auth_db')->table('users', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('auth_db')->table('users', function (Blueprint $table) {
            $table->dropColumn('cargo');
        });
    }
};
