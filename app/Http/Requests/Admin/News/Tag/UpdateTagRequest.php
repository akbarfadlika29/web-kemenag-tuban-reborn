<?php

namespace App\Http\Requests\Admin\News\Tag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tag = $this->route('news_tag');

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
                Rule::unique(
                    'news_tags',
                    'slug'
                )->ignore($tag?->id),
            ],
        ];
    }
}