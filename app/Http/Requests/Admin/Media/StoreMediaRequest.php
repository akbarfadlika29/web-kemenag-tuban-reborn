<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',

                File::types([
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                    'gif',
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'pptx',
                    'txt',
                    'csv',
                    'mp3',
                    'wav',
                    'mp4',
                    'webm',
                ])->max('50mb'),
            ],

            'unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'alt_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_public' => [
                'required',
                'boolean',
            ],

            'expected_type' => [
                'nullable',

                Rule::in([
                    'all',
                    'image',
                    'document',
                    'audio',
                    'video',
                    'other',
                ]),
            ],
        ];
    }
}
