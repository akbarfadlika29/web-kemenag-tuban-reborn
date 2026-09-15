<?php

namespace App\Http\Requests\Admin\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                'exists:menu_items,id',
            ],

            'page_id' => [
                'nullable',
                'integer',
                'exists:pages,id',
                Rule::requiredIf(
                    fn () => $this->input('type') === 'page'
                ),
            ],

            'label' => [
                'required',
                'string',
                'max:150',
            ],

            'location' => [
                'required',
                Rule::in([
                    'header',
                    'footer',
                ]),
            ],

            'type' => [
                'required',
                Rule::in([
                    'group',
                    'url',
                    'route',
                    'page',
                ]),
            ],

            'url' => [
                'nullable',
                'string',
                'max:2048',
                Rule::requiredIf(
                    fn () => $this->input('type') === 'url'
                ),
            ],

            'route_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(
                    fn () => $this->input('type') === 'route'
                ),
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:1',
            ],

            'open_in_new_tab' => [
                'boolean',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'open_in_new_tab' =>
                $this->boolean('open_in_new_tab'),

            'is_active' =>
                $this->boolean('is_active'),
        ]);
    }
}
