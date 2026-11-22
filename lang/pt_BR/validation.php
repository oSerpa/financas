<?php

// Apenas as regras usadas no projeto. O que faltar cai no inglês (fallback).
return [
    'confirmed' => 'A confirmação de :attribute não confere.',
    'email' => 'Informe um :attribute válido.',
    'lowercase' => 'O :attribute deve estar em letras minúsculas.',
    'max' => [
        'string' => 'O :attribute deve ter no máximo :max caracteres.',
        'numeric' => 'O :attribute deve ser no máximo :max.',
    ],
    'min' => [
        'string' => 'O :attribute deve ter pelo menos :min caracteres.',
        'numeric' => 'O :attribute deve ser pelo menos :min.',
    ],
    'password' => [
        'letters' => 'A senha precisa ter pelo menos uma letra.',
        'mixed' => 'A senha precisa ter letras maiúsculas e minúsculas.',
        'numbers' => 'A senha precisa ter pelo menos um número.',
        'symbols' => 'A senha precisa ter pelo menos um símbolo.',
        'uncompromised' => 'Essa senha apareceu em um vazamento de dados. Escolha outra.',
    ],
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O :attribute deve ser um texto.',
    'unique' => 'Esse :attribute já está em uso.',

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
    ],
];
