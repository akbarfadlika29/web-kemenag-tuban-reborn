<?php

namespace App\Http\Requests\Admin\QuickLink;

use App\Models\QuickLink;
use App\Services\Routing\FrontendRouteService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class QuickLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'media',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),
            ],

            'page_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'pages',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),

                Rule::requiredIf(
                    fn () =>
                        $this->input(
                            'target_type'
                        )
                        === QuickLink::TARGET_PAGE
                ),
            ],

            'news_category_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'news_categories',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),

                Rule::requiredIf(
                    fn () =>
                        $this->input(
                            'target_type'
                        )
                        ===
                        QuickLink::TARGET_NEWS_CATEGORY
                ),
            ],

            'label' => [
                'required',
                'string',
                'max:150',
            ],

            'target_type' => [
                'required',

                Rule::in(
                    QuickLink::TARGET_TYPES
                ),
            ],

            'route_name' => [
                'nullable',
                'string',
                'max:255',

                Rule::requiredIf(
                    fn () =>
                        $this->input(
                            'target_type'
                        )
                        === QuickLink::TARGET_ROUTE
                ),

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ): void {
                    if (
                        $this->input(
                            'target_type'
                        )
                        !== QuickLink::TARGET_ROUTE
                    ) {
                        return;
                    }

                    if (
                        blank($value)
                    ) {
                        return;
                    }

                    $routes = app(
                        FrontendRouteService::class
                    );

                    if (
                        !$routes->isAllowed(
                            (string) $value
                        )
                    ) {
                        $fail(
                            'Modul publik yang dipilih tidak valid.'
                        );
                    }
                },
            ],

            'url' => [
                'nullable',
                'string',
                'max:2048',

                Rule::requiredIf(
                    fn () =>
                        $this->input(
                            'target_type'
                        )
                        === QuickLink::TARGET_URL
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
            'label' =>
                trim(
                    (string) $this->input(
                        'label',
                        ''
                    )
                ),

            'route_name' =>
                $this->filled('route_name')
                    ? trim(
                        (string) $this->input(
                            'route_name'
                        )
                    )
                    : null,

            'url' =>
                $this->filled('url')
                    ? trim(
                        (string) $this->input(
                            'url'
                        )
                    )
                    : null,

            'open_in_new_tab' =>
                $this->boolean(
                    'open_in_new_tab'
                ),

            'is_active' =>
                $this->boolean(
                    'is_active'
                ),
        ]);
    }

    public function messages(): array
    {
        return [
            'label.required' =>
                'Nama akses cepat wajib diisi.',

            'target_type.required' =>
                'Sumber tujuan wajib dipilih.',

            'target_type.in' =>
                'Sumber tujuan tidak valid.',

            'page_id.required' =>
                'Halaman tujuan wajib dipilih.',

            'news_category_id.required' =>
                'Kategori berita tujuan wajib dipilih.',

            'route_name.required' =>
                'Modul publik tujuan wajib dipilih.',

            'url.required' =>
                'URL tujuan wajib diisi.',

            'sort_order.required' =>
                'Urutan wajib diisi.',

            'sort_order.min' =>
                'Urutan minimal bernilai 1.',
        ];
    }
}
