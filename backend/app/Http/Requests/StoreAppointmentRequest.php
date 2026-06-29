<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'client_id' => ['required', 'exists:clients,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Selecione um atendente.',
            'user_id.exists' => 'Atendente não encontrado.',
            'client_id.required' => 'Selecione um cliente.',
            'client_id.exists' => 'Cliente não encontrado.',
            'date.required' => 'O campo data é obrigatório.',
            'date.after_or_equal' => 'Não é possível agendar em datas passadas.',
            'start_time.required' => 'O campo horário é obrigatório.',
            'start_time.date_format' => 'Formato de horário inválido.',
            'end_time.required' => 'O campo horário final é obrigatório.',
            'end_time.after' => 'O horário final deve ser maior que o inicial.',
            'notes.max' => 'As observações devem ter no máximo 500 caracteres.',
        ];
    }
}
