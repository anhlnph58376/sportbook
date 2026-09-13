<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.different' => 'Mật khẩu mới không được trùng với mật khẩu cũ.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ];
    }
}
