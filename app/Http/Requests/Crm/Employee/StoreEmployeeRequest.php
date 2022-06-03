<?php

namespace App\Http\Requests\Crm\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'first_name_en'   => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]+([ \-][a-zA-Z]+)*+/i'],
            'first_name_ua'   => ['required', 'string', 'max:255', "regex:/[а-яА-ЯІіЇїҐґ']+([ \-][а-яА-ЯІіЇїҐґ']+)*/i"],
            'last_name_en'    => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]+([ \-][a-zA-Z]+)*+/i'],
            'last_name_ua'    => ['required', 'string', 'max:255', "regex:/[а-яА-ЯІіЇїҐґ']+([ \-][а-яА-ЯІіЇїҐґ']+)*/i"],
            'email'           => ['required', 'string', 'max:255', 'email', 'unique:employees,email'],
            'phone'           => ['required', 'string', 'min:7', 'max:12', 'regex:/\d+/'],
            'company_id'      => ['required', 'integer']
        ];
    }

    /**
     * Change attributes' names.
     * @return string[]
     */
    public function attributes()
    {
        return [
            'first_name_ua' => __('admin.employee_name_ua'),
            'first_name_en' => __('admin.employee_name_en'),
            'last_name_ua' => __('admin.employee_lastname_ua'),
            'last_name_en' => __('admin.employee_lastname_en'),
            'email'   => __('admin.email'),
            'phone'   => __('admin.phone'),
            'company_id' => __('admin.company_name'),
        ];
    }
}
