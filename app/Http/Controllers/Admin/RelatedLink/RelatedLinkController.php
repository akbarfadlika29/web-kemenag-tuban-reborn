<?php

namespace App\Http\Controllers\Admin\RelatedLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RelatedLink\StoreRelatedLinkRequest;
use App\Http\Requests\Admin\RelatedLink\UpdateRelatedLinkRequest;
use App\Models\RelatedLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RelatedLinkController extends Controller
{
    public function index(): View
    {
        $relatedLinks = RelatedLink::query()
            ->with('media')
            ->orderBy('id')
            ->paginate(20);

        return view(
            'admin.related-links.index',
            compact('relatedLinks')
        );
    }

    public function create(): View
    {
        return view(
            'admin.related-links.create'
        );
    }

    public function store(
        StoreRelatedLinkRequest $request
    ): RedirectResponse {
        RelatedLink::create(
            $request->validated()
        );

        return redirect()
            ->route(
                'admin.related-links.index'
            )
            ->with(
                'success',
                'Link terkait berhasil ditambahkan.'
            );
    }

    public function edit(
        RelatedLink $relatedLink
    ): View {
        $relatedLink->load('media');

        return view(
            'admin.related-links.edit',
            compact('relatedLink')
        );
    }

    public function update(
        UpdateRelatedLinkRequest $request,
        RelatedLink $relatedLink
    ): RedirectResponse {
        $relatedLink->update(
            $request->validated()
        );

        return redirect()
            ->route(
                'admin.related-links.index'
            )
            ->with(
                'success',
                'Link terkait berhasil diperbarui.'
            );
    }

    public function destroy(
        RelatedLink $relatedLink
    ): RedirectResponse {
        $relatedLink->delete();

        return redirect()
            ->route(
                'admin.related-links.index'
            )
            ->with(
                'success',
                'Link terkait berhasil dihapus.'
            );
    }
}
