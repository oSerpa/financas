<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Migration: descreve uma mudança na estrutura do banco em código PHP.
 * php artisan migrate roda o up() das que ainda não rodaram; rollback roda o down().
 * O Laravel guarda as que já rodaram na tabela "migrations".
 *
 * "return new class" é uma classe anônima: o arquivo não precisa de nome de classe.
 */
return new class extends Migration
{
    /** Cria a tabela categories. */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // chave primária "id", inteiro com auto incremento
            // Chave estrangeira para users.id. Apagar o usuário apaga as categorias dele.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('type', 10); // entrada ou saida
            $table->timestamps(); // created_at e updated_at, preenchidas pelo Eloquent

            // O banco garante: o mesmo usuário não repete nome dentro do mesmo tipo.
            $table->unique(['user_id', 'type', 'name']);
        });
    }

    /** Desfaz o up(): apaga a tabela. */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
