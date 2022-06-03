<?php

namespace App\Http\Requests\Crm\Company;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
            'name_en'   => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]+([ \-][a-zA-Z]+)*+/i'],
            'name_ua'   => ['required', 'string', 'max:255', "regex:/[а-яА-ЯІіЇїҐґ']+([ \-][а-яА-ЯІіЇїҐґ']+)*/i"],
            'email'     => ['required', 'string', 'max:255', 'email', 'unique:companies,email'],
            'phone'     => ['required', 'string', 'min:7', 'max:12', 'regex:/\d+/'],
            'website'   => ['required', 'string', 'max:255', 'url'],
            'logo'      => ['image', 'dimensions:min_width=100,min_height=100']
        ];
    }

    /**
     * Change attributes' names.
     * @return string[]
     */
    public function attributes()
    {
        return [
            'name_en' => __('admin.company_name_en'),
            'name_ua' => __('admin.company_name_ua'),
            'email'   => __('admin.email'),
            'phone'   => __('admin.phone'),
            'website' => __('admin.website'),
            'logo'    => __('admin.company_logo'),
        ];
    }
}
