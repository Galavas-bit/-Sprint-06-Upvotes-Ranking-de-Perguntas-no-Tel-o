<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TICKET #009: tabela pivot N:M entre perguntas e usuários (upvotes).
 * Gerada com: php artisan make:migration create_pergunta_user_table --create=pergunta_user
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pergunta_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pergunta_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Um usuário só pode votar uma vez na mesma pergunta
            $table->unique(['pergunta_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pergunta_user');
    }
};
