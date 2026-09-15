<?php

namespace App\View\Components\Frontend;

use App\Models\RelatedLink;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class RelatedLinks extends Component
{
    public function render(): View
    {
        return view(
            'components.frontend.related-links',
            [
                'relatedLinks' =>
                    $this->relatedLinks(),
            ]
        );
    }

    private function relatedLinks(): Collection
    {
        return RelatedLink::query()
            ->with('media')
            ->orderBy('id')
            ->get()
            ->filter(
                fn (RelatedLink $link) =>
                    $link->media !== null
                    && $link->media->type === 'image'
            )
            ->map(
                function (
                    RelatedLink $link
                ): array {
                    $medium =
                        $link->media;

                    return [
                        'id' =>
                            $link->id,

                        'name' =>
                            $link->name,

                        'url' =>
                            $link->url,

                        'image_url' =>
                            Storage::disk(
                                $medium->disk
                            )->url(
                                $medium->path
                            ),

                        'image_alt' =>
                            $medium->alt_text
                            ?: $link->name,
                    ];
                }
            )
            ->values();
    }
}
