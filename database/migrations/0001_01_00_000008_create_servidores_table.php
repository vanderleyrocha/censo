<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servidores', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';
            $table->id();
            $table->integer('matricula')->nullable();
            $table->integer('contrato1')->nullable();
            $table->integer('contrato2')->nullable();
            $table->string('nome', 28)->nullable();
            $table->string('cargo', 38)->nullable();
            $table->string('funcao', 100)->nullable();
            $table->string('lotacao', 100)->nullable();
            $table->string('usuario', 19)->nullable();
            $table->string('email', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servidores');
    }
};
