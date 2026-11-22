<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: classe só para validar o formulário de categoria. Tira a
 * validação do controller e é usada tanto no store quanto no update.
 */
class CategoryRequest extends FormRequest
{
    /**
     * Quem pode enviar. Se devolver false, o Laravel responde 403.
     */
    public function authorize(): bool
    {
        // Na criação não há {category} na rota (null) e qualquer usuário logado pode.
        // Na edição, só o dono (CategoryPolicy::update).
        $category = $this->route('category');

        return $category === null || $this->user()->can('update', $category);
    }

    /** Regras de validação de cada campo. */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(TransactionType::class)],
            'name' => [
                'required', 'string', 'max:60',
                // Ex.: o usuário não pode ter duas saídas chamadas "Mercado",
                // mas pode ter uma entrada e uma saída com o mesmo nome.
                // Nome único por usuário e tipo, ignorando a própria categoria na edição.
                Rule::unique('categories')
                    ->where('user_id', $this->user()->id)
                    ->where('type', $this->input('type'))
                    ->ignore($this->route('category')),
            ],
        ];
    }

    /** Nome do campo nas mensagens de erro: "O campo tipo é obrigatório." */
    public function attributes(): array
    {
        return ['type' => 'tipo'];
    }
}
