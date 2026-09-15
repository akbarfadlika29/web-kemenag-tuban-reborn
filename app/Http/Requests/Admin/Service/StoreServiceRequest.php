<?php

namespace App\Http\Requests\Admin\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',

                Rule::exists(
                    'service_categories',
                    'id'
                )->where(
                    fn ($query) =>
                        $query
                            ->where('is_active', true)
                            ->whereNull('deleted_at')
                ),
            ],

            'unit_id' => [
                'required',
                'integer',

                Rule::exists(
                    'units',
                    'id'
                )->where(
                    fn ($query) =>
                        $query
                            ->where('is_active', true)
                            ->whereNull('deleted_at')
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

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'requirements' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'procedure' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'completion_time' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_free' => [
                'required',
                'boolean',
            ],

            'fee_description' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'service_output' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'legal_basis' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'service_channel' => [
                'required',

                Rule::in([
                    'online',
                    'offline',
                    'hybrid',
                ]),
            ],

            'service_url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'service_location' => [
                'nullable',
                'string',
                'max:500',
            ],

            'service_hours' => [
                'nullable',
                'string',
                'max:500',
            ],

            'contact_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_phone' => [
                'nullable',
                'string',
                'max:100',
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:255',
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

    public function messages(): array
    {
        return [
            'category_id.required' =>
                'Kategori layanan wajib dipilih.',

            'unit_id.required' =>
                'Unit kerja wajib dipilih.',

            'title.required' =>
                'Nama layanan wajib diisi.',

            'service_channel.required' =>
                'Kanal layanan wajib dipilih.',
        ];
    }
}