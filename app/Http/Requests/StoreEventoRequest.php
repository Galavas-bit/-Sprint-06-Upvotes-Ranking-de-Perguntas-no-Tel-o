<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * TICKET #008: regras de validação do formulário de Eventos.
     */
    public function rules(): array
    {
        return [
            'titulo'    => 'required|string|min:3|max:255',
            'descricao' => 'required|string|min:10|max:1000',
        ];
    }

    /**
     * Mensagens em português exibidas pelo @error na view.
     */
    public function messages(): array
    {
        return [
            'titulo.required'    => 'O título do evento é obrigatório.',
            'titulo.min'         => 'O título deve ter pelo menos :min caracteres.',
            'titulo.max'         => 'O título deve ter no máximo :max caracteres.',
            'descricao.required' => 'A descrição do evento é obrigatória.',
            'descricao.min'      => 'A descrição deve ter pelo menos :min caracteres.',
            'descricao.max'      => 'A descrição deve ter no máximo :max caracteres.',
        ];
    }
}
