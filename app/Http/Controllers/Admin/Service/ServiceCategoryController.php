<?php

namespace App\Http\Controllers\Admin\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Service\Category\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\Service\Category\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $categories = ServiceCategory::query()
            ->withCount('services')
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'name',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'description',
                                    'ilike',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.services.categories.index',
            compact(
                'categories',
                'search'
            )
        );
    }

    public function create(): View
    {
        return view(
            'admin.services.categories.create'
        );
    }

    public function store(
        StoreServiceCategoryRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $data['slug'] = $this->generateSlug(
            $data['name']
        );

        ServiceCategory::create($data);

        return redirect()
            ->route(
                'admin.service-categories.index'
            )
            ->with(
                'success',
                'Kategori layanan berhasil ditambahkan.'
            );
    }

    public function edit(
        ServiceCategory $serviceCategory
    ): View {
        return view(
            'admin.services.categories.edit',
            [
                'category' => $serviceCategory,
            ]
        );
    }

    public function update(
        UpdateServiceCategoryRequest $request,
        ServiceCategory $serviceCategory
    ): RedirectResponse {
        $data = $request->validated();

        $data['slug'] = $this->generateSlug(
            $data['name'],
            $serviceCategory->id
        );

        $serviceCategory->update($data);

        return redirect()
            ->route(
                'admin.service-categories.index'
            )
            ->with(
                'success',
                'Kategori layanan berhasil diperbarui.'
            );
    }

    public function destroy(
        ServiceCategory $serviceCategory
    ): RedirectResponse {
        if (
            $serviceCategory
                ->services()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'admin.service-categories.index'
                )
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan.'
                );
        }

        $serviceCategory->delete();

        return redirect()
            ->route(
                'admin.service-categories.index'
            )
            ->with(
                'success',
                'Kategori layanan berhasil dihapus.'
            );
    }

    private function generateSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($name)
            ?: 'kategori-layanan';

        $slug = $base;
        $counter = 2;

        while (
            ServiceCategory::withTrashed()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) =>
                        $query->where(
                            'id',
                            '!=',
                            $ignoreId
                        )
                )
                ->exists()
        ) {
            $slug = $base
                . '-'
                . $counter++;
        }

        return $slug;
    }
}