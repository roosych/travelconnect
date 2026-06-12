<?php

namespace App\Domain\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isOperator();
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Укажите имя оператора.',
            'email.required' => 'Укажите email.',
            'email.email'    => 'Введите корректный email.',
            'email.unique'   => 'Пользователь с таким email уже существует.',
        ];
    }
}
