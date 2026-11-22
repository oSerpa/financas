<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category === null || $this->user()->can('update', $category);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(TransactionType::class)],
            'name' => [
                'required', 'string', 'max:60',
                // Nome único por usuário e tipo, ignorando a própria categoria na edição.
                Rule::unique('categories')
                    ->where('user_id', $this->user()->id)
                    ->where('type', $this->input('type'))
                    ->ignore($this->route('category')),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['type' => 'tipo'];
    }
}
