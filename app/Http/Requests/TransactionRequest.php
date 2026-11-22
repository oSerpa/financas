<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        return $transaction === null || $this->user()->can('update', $transaction);
    }

    protected function prepareForValidation(): void
    {
        // Aceita o valor no formato brasileiro (1.234,56).
        $this->merge(['amount' => Money::parse($this->input('amount'))]);
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'occurred_on' => ['required', 'date'],
            // A categoria precisa existir e pertencer ao usuário logado.
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

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
