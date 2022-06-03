<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class NewPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'regex:/^\S+$/', Rules\Password::defaults()],
        ];
    }

    /**
     * Change attributes' names.
     * @return string[]
     */
    public function attributes()
    {
        return [
            'token' => __('token'),
            'email' => __('Email'),
            'password' => __('Password'),
        ];
    }

    /**
     * Change messages' texts.
     * @return string[]
     */
    public function messages()
    {
        return [
            'password.regex' => trans('passwords.spaces')
        ];
    }
}
