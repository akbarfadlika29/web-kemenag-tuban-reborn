<?php

namespace App\Http\Requests\Admin\HeroSlide;

use App\Models\Media;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HeroSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media_id' => [
                'required',
                'integer',

                Rule::exists(
                    'media',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {
                    $medium = Media::query()
                        ->find($value);

                    if (
                        $medium
                        && $medium->type !== 'image'
                    ) {
                        $fail(
                            'Media yang dipilih harus berupa gambar.'
                        );
                    }
                },
            ],

            'title' => [
                'nullable',
                'string',
                'max:180',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'button_label' => [
                'nullable',
                'string',
                'max:100',
                'required_with:url',
            ],

            'url' => [
                'nullable',
                'string',
                'max:2048',
                'required_with:button_label',

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {
                    $url = trim(
                        (string) $value
                    );

                    if ($url === '') {
                        return;
                    }

                    if (
                        str_starts_with($url, '/')
                        || str_starts_with($url, '#')
                    ) {
                        return;
                    }

                    if (
                        filter_var(
                            $url,
                            FILTER_VALIDATE_URL
                        ) === false
                    ) {
                        $fail(
                            'Alamat URL tidak valid.'
                        );

                        return;
                    }

                    $scheme = strtolower(
                        (string) parse_url(
                            $url,
                            PHP_URL_SCHEME
                        )
                    );

                    if (
                        ! in_array(
                            $scheme,
                            [
                                'http',
                                'https',
                            ],
                            true
                        )
                    ) {
                        $fail(
                            'Alamat URL hanya boleh menggunakan http atau https.'
                        );
                    }
                },
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' =>
                $this->normalizeNullableString(
                    $this->input('title')
                ),

            'description' =>
                $this->normalizeNullableString(
                    $this->input('description')
                ),

            'button_label' =>
                $this->normalizeNullableString(
                    $this->input('button_label')
                ),

            'url' =>
                $this->normalizeNullableString(
                    $this->input('url')
                ),

            'is_active' =>
                $this->boolean('is_active'),
        ]);
    }

    private function normalizeNullableString(
        mixed $value
    ): ?string {
        $value = trim(
            (string) $value
        );

        return $value !== ''
            ? $value
            : null;
    }

    public function messages(): array
    {
        return [
            'media_id.required' =>
                'Gambar slide wajib dipilih.',

            'media_id.exists' =>
                'Gambar yang dipilih tidak tersedia.',

            'title.max' =>
                'Judul maksimal 180 karakter.',

            'description.max' =>
                'Deskripsi maksimal 2000 karakter.',

            'button_label.required_with' =>
                'Label tombol wajib diisi jika URL tombol diisi.',

            'url.required_with' =>
                'Alamat URL wajib diisi jika label tombol diisi.',

            'sort_order.required' =>
                'Urutan slide wajib diisi.',

            'sort_order.min' =>
                'Urutan slide minimal 1.',
        ];
    }
}
