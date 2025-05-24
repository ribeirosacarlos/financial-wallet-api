<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'O campo valor é obrigatório.',
            'amount.numeric' => 'O campo valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
        ];
    }
}