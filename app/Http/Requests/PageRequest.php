<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
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
            'draw'                   => ['required', 'boolean'],
            'start'                  => ['required', 'integer', 'gte:0'],
            'length'                 => ['required', 'integer', 'gte:1'],
            'order'                  => ['nullable', 'array'],
            'order.*.column'         => ['required', 'integer', 'gte:0'],
            'order.*.dir'            => ['required', Rule::in(['asc', 'desc'])],
            'search'                 => ['nullable', 'array'],
            'search.value'           => ['nullable', 'string'],
            'search.regex'           => ['nullable', 'string'],
            'columns'                => ['required', 'array', 'min:1'],
            'columns.*.data'         => ['required', 'string'],
            'columns.*.name'         => ['nullable', 'string'],
            'columns.*.searchable'   => ['required', Rule::in(['true', 'false'])],
            'columns.*.orderable'    => ['required', Rule::in(['true', 'false'])],
            'columns.*.search'       => ['nullable', 'array'],
            'columns.*.search.value' => ['nullable', 'string'],
            'columns.*.search.regex' => ['nullable', 'string'],
        ];
    }
}
