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
        Schema::create('escola_servidors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->nullable()->index();
            $table->foreignId('servidor_id')->nullable()->index();
            $table->string("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escola_servidors');
    }
};
