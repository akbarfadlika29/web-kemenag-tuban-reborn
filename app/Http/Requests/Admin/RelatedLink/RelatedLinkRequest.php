<?php

namespace App\Http\Requests\Admin\RelatedLink;

use App\Models\Media;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RelatedLinkRequest extends FormRequest
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
                'max:150',
            ],

            'url' => [
                'required',
                'string',
                'max:2048',
                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {
                    $url = trim(
                        (string) $value
                    );

                    if (
                        filter_var(
                            $url,
                            FILTER_VALIDATE_URL
                        ) === false
                    ) {
                        $fail(
                            'Alamat URL harus berupa URL yang valid.'
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
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim(
                (string) $this->input(
                    'name'
                )
            ),

            'url' => trim(
                (string) $this->input(
                    'url'
                )
            ),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'Nama link wajib diisi.',

            'name.max' =>
                'Nama link maksimal 150 karakter.',

            'url.required' =>
                'Alamat URL wajib diisi.',

            'media_id.required' =>
                'Gambar wajib dipilih.',

            'media_id.exists' =>
                'Gambar yang dipilih tidak tersedia.',
        ];
    }
}
