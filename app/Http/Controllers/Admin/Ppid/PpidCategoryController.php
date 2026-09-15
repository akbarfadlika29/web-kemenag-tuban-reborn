<?php

namespace App\Http\Controllers\Admin\Ppid;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ppid\Category\StorePpidCategoryRequest;
use App\Http\Requests\Admin\Ppid\Category\UpdatePpidCategoryRequest;
use App\Models\PpidCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PpidCategoryController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $categories =
            PpidCategory::query()
                ->withCount(
                    'informations'
                )
                ->when(
                    $search !== '',
                    fn ($query) =>
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
                        )
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'name'
                )
                ->paginate(20)
                ->withQueryString();

        return view(
            'admin.ppid.categories.index',
            compact(
                'categories',
                'search'
            )
        );
    }

    public function create(): View
    {
        return view(
            'admin.ppid.categories.create'
        );
    }

    public function store(
        StorePpidCategoryRequest $request
    ): RedirectResponse {
        $data =
            $request->validated();

        $data['slug'] =
            $this->generateSlug(
                $data['name']
            );

        PpidCategory::create(
            $data
        );

        return redirect()
            ->route(
                'admin.ppid-categories.index'
            )
            ->with(
                'success',
                'Kategori PPID berhasil ditambahkan.'
            );
    }

    public function edit(
        PpidCategory $ppidCategory
    ): View {
        return view(
            'admin.ppid.categories.edit',
            [
                'category' =>
                    $ppidCategory,
            ]
        );
    }

    public function update(
        UpdatePpidCategoryRequest $request,
        PpidCategory $ppidCategory
    ): RedirectResponse {
        $data =
            $request->validated();

        $data['slug'] =
            $this->generateSlug(
                $data['name'],
                $ppidCategory->id
            );

        $ppidCategory->update(
            $data
        );

        return redirect()
            ->route(
                'admin.ppid-categories.index'
            )
            ->with(
                'success',
                'Kategori PPID berhasil diperbarui.'
            );
    }

    public function destroy(
        PpidCategory $ppidCategory
    ): RedirectResponse {
        if (
            $ppidCategory
                ->informations()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'admin.ppid-categories.index'
                )
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan.'
                );
        }

        $ppidCategory->delete();

        return redirect()
            ->route(
                'admin.ppid-categories.index'
            )
            ->with(
                'success',
                'Kategori PPID berhasil dihapus.'
            );
    }

    private function generateSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $base =
            Str::slug($name)
            ?: 'kategori-ppid';

        $slug = $base;
        $counter = 2;

        while (
            PpidCategory::withTrashed()
                ->where(
                    'slug',
                    $slug
                )
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
            $slug =
                $base
                . '-'
                . $counter++;
        }

        return $slug;
    }
}