<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $targetId = (int) $this->route('id');

        return $user->isAdmin() || $user->id === $targetId;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        if ($this->user()->isAdmin()) {
            $rules['role'] = ['required', 'in:admin,attendant'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'role.required' => 'O campo tipo de usuário é obrigatório.',
            'role.in' => 'Tipo de usuário inválido.',
        ];
    }
}
