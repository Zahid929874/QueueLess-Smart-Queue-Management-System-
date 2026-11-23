<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_name' => ['required', 'string', 'max:150'],
            'employee_id' => ['required', 'string', 'max:50', 'unique:admin_profiles,employee_id'],
            'institutional_mail' => ['required', 'email', 'max:150', 'unique:users,email'],
            'duty_contact' => ['required', 'string', 'max:25'],
            'office_title' => ['nullable', 'string', 'max:100'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'invite_token' => ['required', 'string', 'max:200'],
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{12,}$/',
            ],
            'totp_code' => ['required', 'string', 'size:6'],
        ];
    }
}
