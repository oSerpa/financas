<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Cria a tabela transactions (lançamentos). Roda depois da de categorias
 * porque o nome do arquivo começa com uma data/número maior, e ela precisa
 * que categories já exista para criar a chave estrangeira.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // O user_id fica repetido aqui (ele já está na categoria) para as
            // consultas filtrarem por usuário sem precisar de JOIN.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Não deixa apagar uma categoria que ainda tem lançamentos.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('description', 120);
            // decimal(12, 2): até 12 dígitos, 2 depois da vírgula. Dinheiro nunca
            // vai em float, que tem erro de arredondamento.
            $table->decimal('amount', 12, 2); // sempre positivo; o sinal vem do tipo da categoria
            $table->date('occurred_on');
            $table->text('notes')->nullable(); // observação é opcional
            $table->timestamps();

            // Índice composto: acelera a consulta mais comum, "lançamentos do usuário X no mês Y".
            $table->index(['user_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
