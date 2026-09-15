<?php

namespace App\Http\Requests\Admin\Agenda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Stevebauman\Purify\Facades\Purify;

class StoreAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => Purify::config('news')
                ->clean(
                    (string) $this->input(
                        'content',
                        ''
                    )
                ),
        ]);
    }

    public function rules(): array
    {
        return [
            'unit_id' => [
                'required',
                'integer',

                Rule::exists(
                    'units',
                    'id'
                )->where(
                    fn ($query) =>
                        $query
                            ->where(
                                'is_active',
                                true
                            )
                            ->whereNull(
                                'deleted_at'
                            )
                ),
            ],

            'cover_media_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'media',
                    'id'
                )->where(
                    fn ($query) =>
                        $query
                            ->where(
                                'type',
                                'image'
                            )
                            ->where(
                                'is_public',
                                true
                            )
                            ->whereNull(
                                'deleted_at'
                            )
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

            'location' => [
                'nullable',
                'string',
                'max:500',
            ],

            'start_at' => [
                'required',
                'date',
            ],

            'end_at' => [
                'nullable',
                'date',
                'after:start_at',
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

            'is_featured' => [
                'required',
                'boolean',
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
                $text = html_entity_decode(
                    strip_tags(
                        (string) $this->input(
                            'content',
                            ''
                        )
                    ),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );

                $text = preg_replace(
                    '/\s+/u',
                    ' ',
                    $text
                );

                if (
                    trim(
                        (string) $text
                    ) === ''
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'content',
                            'Isi agenda wajib diisi.'
                        );
                }
            },
        ];
    }
}