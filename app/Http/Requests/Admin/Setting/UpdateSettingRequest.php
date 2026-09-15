<?php

namespace App\Http\Requests\Admin\Setting;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'settings' => [
                'required',
                'array',
            ],
        ];

        foreach (
            config('site-settings.groups', [])
            as $group
        ) {
            foreach (
                $group['fields'] ?? []
                as $key => $field
            ) {
                $rules["settings.{$key}"] =
                    $field['rules']
                    ?? [
                        'nullable',
                        'string',
                    ];
            }
        }

        return $rules;
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator) {
                $logoMediaId = (int) $this->input(
                    'settings.logo_media_id',
                    0
                );

                if ($logoMediaId <= 0) {
                    return;
                }

                $exists = Media::query()
                    ->whereKey($logoMediaId)
                    ->where('type', 'image')
                    ->where('is_public', true)
                    ->exists();

                if (! $exists) {
                    $validator
                        ->errors()
                        ->add(
                            'settings.logo_media_id',
                            'Logo harus berupa gambar publik dari Media Manager.'
                        );
                }
            }
        );
    }
}
