<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validação do formulário de lançamento (criar e editar).
 */
class TransactionRequest extends FormRequest
{
    /**
     * Na criação libera; na edição, só o dono do lançamento (TransactionPolicy).
     */
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        return $transaction === null || $this->user()->can('update', $transaction);
    }

    /**
     * Roda antes das regras. Serve para ajustar os dados que chegaram.
     */
    protected function prepareForValidation(): void
    {
        // Aceita o valor no formato brasileiro (1.234,56).
        $this->merge(['amount' => Money::parse($this->input('amount'))]);
    }

    public function rules(): array
    {
        // A regra 'numeric' do amount só funciona porque o valor já foi
        // convertido para "1234.56" no prepareForValidation.
        return [
            'description' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'occurred_on' => ['required', 'date'],
            // A categoria precisa existir e pertencer ao usuário logado.
            // Isso impede alguém de mandar, pelo DevTools, o id da categoria de outra pessoa.
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** Nomes em português para as mensagens de erro. */
    public function attributes(): array
    {
        return [
            'description' => 'descrição',
            'amount' => 'valor',
            'occurred_on' => 'data',
            'category_id' => 'categoria',
            'notes' => 'observação',
        ];
    }
}
