<?php

namespace App\Http\Requests\Admin\Regulation;

use App\Models\Regulation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'regulation_type_id' => ['required', 'integer', 'exists:regulation_types,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],

            'title' => ['required', 'string', 'max:500'],
            'number' => ['required', 'string', 'max:150'],
            'year' => ['required', 'integer', 'between:1900,2200'],
            'issuing_authority' => ['required', 'string', 'max:255'],

            'subject' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'description' => ['nullable', 'string', 'max:30000'],

            'issued_at' => ['nullable', 'date'],
            'effective_at' => ['nullable', 'date'],

            'legal_status' => ['required', Rule::in(array_keys(Regulation::LEGAL))],
            'legal_status_note' => ['nullable', 'string', 'max:5000'],
            'source_url' => ['nullable', 'url:http,https', 'max:2000'],

            'publication_status' => [
                'required',
                Rule::in(array_keys(Regulation::PUBLICATION)),
            ],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],

            'main_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'remove_main' => ['nullable', 'boolean'],

            'main_file' => ['prohibited'],
            'attachments' => ['prohibited'],
            'attachment_media_ids' => ['prohibited'],
            'documents' => ['prohibited'],
        ];
    }
}
