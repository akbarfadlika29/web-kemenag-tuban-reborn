<?php

namespace App\Http\Requests\Admin\News\Tag;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:120',
                'unique:news_tags,slug',
            ],
        ];
    }
}