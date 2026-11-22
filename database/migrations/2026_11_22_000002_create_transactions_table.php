<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Não deixa apagar uma categoria que ainda tem lançamentos.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('description', 120);
            $table->decimal('amount', 12, 2); // sempre positivo; o sinal vem do tipo da categoria
            $table->date('occurred_on');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
