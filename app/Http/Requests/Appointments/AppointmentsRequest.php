<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return 
        [
            'status' => 'nullable|in:active,un_active',
            'start_time' => 'required|date_format:H:i:s A',
            'end_time' => 'required|date_format:H:i:s A|after:start_time',
            'day_id' => 'required|integer',
            'user_id' => 'required|integer'
        ];
    }
}
