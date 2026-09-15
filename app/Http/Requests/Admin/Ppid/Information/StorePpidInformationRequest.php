<?php

namespace App\Http\Requests\Admin\Ppid\Information;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePpidInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $documents =
            collect(
                $this->input(
                    'documents',
                    []
                )
            )
                ->filter(
                    fn ($document) =>
                        is_array($document)
                        && !empty(
                            $document['media_id']
                        )
                )
                ->values()
                ->map(
                    function (
                        array $document,
                        int $index
                    ) {
                        $document['sort_order'] =
                            $index;

                        $document['is_primary'] =
                            filter_var(
                                $document['is_primary']
                                    ?? false,
                                FILTER_VALIDATE_BOOLEAN
                            );

                        return $document;
                    }
                )
                ->all();

        /*
         * Pastikan maksimal satu dokumen utama.
         */
        $primaryFound = false;

        foreach ($documents as &$document) {
            if (
                $document['is_primary']
                && !$primaryFound
            ) {
                $primaryFound = true;

                continue;
            }

            if ($document['is_primary']) {
                $document['is_primary'] =
                    false;
            }
        }

        unset($document);

        $this->merge([
            'documents' =>
                $documents,
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',

                Rule::exists(
                    'ppid_categories',
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

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'classification' => [
                'required',

                Rule::in([
                    'berkala',
                    'serta_merta',
                    'setiap_saat',
                ]),
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

            /*
             * Metadata administratif
             */
            'information_holder' => [
                'nullable',
                'string',
                'max:255',
            ],

            'person_in_charge' => [
                'nullable',
                'string',
                'max:255',
            ],

            'information_form' => [
                'nullable',

                Rule::in([
                    'digital',
                    'print',
                    'both',
                ]),
            ],

            'information_format' => [
                'nullable',
                'string',
                'max:255',
            ],

            'publication_media' => [
                'nullable',
                'string',
                'max:255',
            ],

            'retention_period' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'retention_unit' => [
                'nullable',

                Rule::in([
                    'day',
                    'month',
                    'year',
                    'permanent',
                ]),
            ],

            'availability' => [
                'required',

                Rule::in([
                    'online',
                    'by_request',
                    'both',
                ]),
            ],

            'access_level' => [
                'required',

                Rule::in([
                    'public',
                    'limited',
                ]),
            ],

            'document_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'document_date' => [
                'nullable',
                'date',
            ],

            'effective_date' => [
                'nullable',
                'date',
            ],

            'last_reviewed_at' => [
                'nullable',
                'date',
            ],

            'legal_basis' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'year' => [
                'nullable',
                'integer',
                'min:1900',
                'max:2100',
            ],

            /*
             * Publikasi
             */
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

            /*
             * Dokumen
             */
            'documents' => ['present', 'array'],

            'documents.*.media_id' => [
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
                                'is_public',
                                true
                            )
                            ->whereNull(
                                'deleted_at'
                            )
                ),
            ],

            'documents.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'documents.*.description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'documents.*.version' => [
                'nullable',
                'string',
                'max:50',
            ],

            'documents.*.document_status' => [
                'required',

                Rule::in([
                    'active',
                    'superseded',
                    'expired',
                ]),
            ],

            'documents.*.document_date' => [
                'nullable',
                'date',
            ],

            'documents.*.sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'documents.*.is_primary' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' =>
                'Kategori PPID wajib dipilih.',

            'unit_id.required' =>
                'Unit kerja wajib dipilih.',

            'title.required' =>
                'Judul informasi wajib diisi.',

            'classification.required' =>
                'Klasifikasi informasi wajib dipilih.',

            'documents.*.media_id.distinct' =>
                'Dokumen yang sama tidak boleh ditambahkan dua kali.',
        ];
    }
}