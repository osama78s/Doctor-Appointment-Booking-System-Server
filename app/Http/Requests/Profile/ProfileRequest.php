<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileRequest extends FormRequest
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
        $id = Auth::id();
        return [
            'image' => 'nullable|file|mimes:png,jpg,jpeg|max:3072',
            'first_name'    => 'required|string|max:50',
            'last_name'     => 'required|string|max:50',
            'phone'         => "required|string|unique:users,phone,$id,id|min:11",
            'gender' => 'required|string|in:M,F',
            'city' => 'required|string',
            'address' => 'required|string',
        ];
    }
}
