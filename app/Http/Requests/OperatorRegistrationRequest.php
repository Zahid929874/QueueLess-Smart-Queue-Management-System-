<?php

namespace App\Http\Requests;

use App\Models\InviteCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OperatorRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'officer_name' => ['required', 'string', 'max:150'],
            'badge_no' => ['required', 'string', 'max:50', 'unique:operator_profiles,badge_no'],
            'official_mail' => ['required', 'email', 'max:150', 'unique:users,email'],
            'service_unit_id' => ['required', 'integer', 'exists:departments,id'],
            'counter_id' => [
                'required',
                'integer',
                Rule::exists('counters', 'id')->where(
                    fn ($query) => $query->where('department_id', $this->input('service_unit_id'))
                ),
            ],
            'rank' => ['required', Rule::in(['Counter Officer', 'Senior Officer', 'Desk Supervisor'])],
            'shift' => ['required', Rule::in(['Morning', 'Day', 'Evening'])],
            'duty_contact' => ['required', 'string', 'max:25'],
            'invite_code' => ['required', 'string', 'max:100'],
            'access_passcode' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $code = InviteCode::where('code', $this->input('invite_code'))->first();

            if (!$code) {
                $validator->errors()->add('invite_code', 'The invite code is invalid.');
                return;
            }

            if ($code->expires_at && $code->expires_at->isPast()) {
                $validator->errors()->add('invite_code', 'The invite code has expired.');
            }

            if ($code->used_count >= $code->max_uses) {
                $validator->errors()->add('invite_code', 'The invite code has reached its use limit.');
            }

            if ($code->service_unit_id && (int) $code->service_unit_id !== (int) $this->input('service_unit_id')) {
                $validator->errors()->add('invite_code', 'The invite code is not valid for this service unit.');
            }
        });
    }
}
