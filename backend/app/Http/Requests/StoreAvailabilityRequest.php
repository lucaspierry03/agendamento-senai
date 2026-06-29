<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Selecione um atendente.',
            'user_id.exists' => 'Atendente não encontrado.',
            'day_of_week.required' => 'Selecione o dia da semana.',
            'day_of_week.between' => 'Dia da semana inválido.',
            'start_time.required' => 'O campo hora inicial é obrigatório.',
            'start_time.date_format' => 'Formato de hora inválido.',
            'end_time.required' => 'O campo hora final é obrigatório.',
            'end_time.date_format' => 'Formato de hora inválido.',
            'end_time.after' => 'A hora final deve ser maior que a hora inicial.',
            'is_active.required' => 'O campo ativo é obrigatório.',
        ];
    }
}
