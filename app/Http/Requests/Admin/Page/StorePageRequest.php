<?php

namespace App\Http\Requests\Admin\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Stevebauman\Purify\Facades\Purify;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $content = (string) $this->input(
            'content',
            ''
        );

        $this->merge([
            'content' => Purify::config('news')
                ->clean($content),
        ]);
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('pages', 'id')
                    ->whereNull('deleted_at'),
            ],

            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')
                    ->where(
                        fn ($query) =>
                            $query
                                ->where('is_active', true)
                                ->whereNull('deleted_at')
                    ),
            ],

            'cover_media_id' => [
                'nullable',
                'integer',
                Rule::exists('media', 'id')
                    ->where(
                        fn ($query) =>
                            $query
                                ->where('type', 'image')
                                ->where('is_public', true)
                                ->whereNull('deleted_at')
                    ),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'content' => [
                'required',
                'string',
            ],

            'template' => [
                'required',
                Rule::in([
                    'default',
                    'full-width',
                    'landing',
                ]),
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                    'archived',
                ]),
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'show_in_menu' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $content = (string) $this->input(
                    'content',
                    ''
                );

                $plainText = html_entity_decode(
                    strip_tags($content),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );

                $plainText = preg_replace(
                    '/\s+/u',
                    ' ',
                    $plainText
                );

                if (
                    trim((string) $plainText) === ''
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'content',
                            'Isi halaman wajib diisi.'
                        );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' =>
                'Judul halaman wajib diisi.',

            'content.required' =>
                'Isi halaman wajib diisi.',

            'status.required' =>
                'Status halaman wajib dipilih.',

            'template.required' =>
                'Template halaman wajib dipilih.',
        ];
    }
}