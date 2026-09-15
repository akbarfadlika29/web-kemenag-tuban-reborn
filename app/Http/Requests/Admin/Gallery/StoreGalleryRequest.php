<?php

namespace App\Http\Requests\Admin\Gallery;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items =
            collect(
                $this->input(
                    'items',
                    []
                )
            )
                ->filter(
                    fn ($item) =>
                        is_array($item)
                        && !empty(
                            $item['media_id']
                        )
                )
                ->values()
                ->map(
                    function (
                        array $item,
                        int $index
                    ) {
                        $item['sort_order'] =
                            $index;

                        return $item;
                    }
                )
                ->all();

        $this->merge([
            'items' => $items,
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

            'description' => [
                'nullable',
                'string',
                'max:5000',
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

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.media_id' => [
                'required',
                'integer',
                'distinct',

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

            'items.*.caption' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.alt_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' =>
                'Unit kerja wajib dipilih.',

            'title.required' =>
                'Judul galeri wajib diisi.',

            'items.required' =>
                'Galeri harus memiliki minimal satu foto.',

            'items.min' =>
                'Galeri harus memiliki minimal satu foto.',

            'items.*.media_id.distinct' =>
                'Foto yang sama tidak boleh ditambahkan dua kali.',
        ];
    }
}