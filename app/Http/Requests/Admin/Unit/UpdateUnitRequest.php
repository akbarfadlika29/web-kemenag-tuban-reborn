<?php

namespace App\Http\Requests\Admin\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unit = $this->route('unit');

        return [
            'parent_id' => ['nullable', 'integer', 'exists:units,id', Rule::notIn([$unit?->id])],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('units', 'slug')->ignore($unit?->id)],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('units', 'code')->ignore($unit?->id)],
            'type' => [
                'required',
                'string',
                Rule::in(['kankemenag', 'subbag', 'seksi', 'kua', 'satker', 'lainnya']),
            ],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'head_name' => ['nullable', 'string', 'max:255'],
            'head_title' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $unit = $this->route('unit');

                if (!$unit || !$this->filled('parent_id')) {
                    return;
                }

                $parentId = (int) $this->input('parent_id');

                if ($parentId === $unit->id) {
                    $validator->errors()->add(
                        'parent_id',
                        'Unit kerja tidak dapat menjadi induk untuk dirinya sendiri.'
                    );

                    return;
                }

                if (in_array($parentId, $unit->descendantIds(), true)) {
                    $validator->errors()->add(
                        'parent_id',
                        'Unit turunan tidak dapat dijadikan induk karena akan membuat struktur melingkar.'
                    );
                }
            },
        ];
    }
}

