<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('auth_db')->table('company_user', function (Blueprint $table) {
            $table->string('approval_email')->nullable()->after('user_code');
        });
    }

    public function down(): void
    {
        Schema::connection('auth_db')->table('company_user', function (Blueprint $table) {
            $table->dropColumn('approval_email');
        });
    }
};
