<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';
            $table->id();
            $table->string('nome', 75)->comment('Nome completo do estado');
            $table->char('uf', 2)->unique()->comment('Sigla da UF');
            $table->integer('ibge')->unique()->comment('Código IBGE do estado');
            $table->integer('pais')->nullable()->comment('País ao qual pertence');
            $table->string('ddd')->comment('DDDs do estado separados por vírgula');
            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->index('nome');
            $table->index('uf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
