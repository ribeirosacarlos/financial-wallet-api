<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return true;
    }
}