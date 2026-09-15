<?php

namespace App\Http\Requests\Admin\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'short_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:units,slug',
            ],

            'code' => [
                'nullable',
                'string',
                'max:255',
                'unique:units,code',
            ],

            'type' => [
                'required',
                'string',
                Rule::in([
                    'kankemenag',
                    'subbag',
                    'seksi',
                    'kua',
                    'satker',
                    'lainnya',
                ]),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'head_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'head_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }
}

