<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Intencionalmente vacío.
        // El filtro por área para órdenes usa una vista SQL de solo lectura en BDWENCO
        // y no debe alterar tablas del proveedor.
    }

    public function down(): void
    {
        // Intencionalmente vacío.
    }
};
