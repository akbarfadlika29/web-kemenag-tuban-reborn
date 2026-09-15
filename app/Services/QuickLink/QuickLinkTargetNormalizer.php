<?php

namespace App\Services\QuickLink;

use App\Models\QuickLink;

class QuickLinkTargetNormalizer
{
    public function normalize(
        array $data
    ): array {
        $type =
            $data['target_type']
            ?? null;

        if (
            $type
            !== QuickLink::TARGET_PAGE
        ) {
            $data['page_id'] = null;
        }

        if (
            $type
            !== QuickLink::TARGET_NEWS_CATEGORY
        ) {
            $data['news_category_id'] = null;
        }

        if (
            $type
            !== QuickLink::TARGET_ROUTE
        ) {
            $data['route_name'] = null;
        }

        if (
            $type
            !== QuickLink::TARGET_URL
        ) {
            $data['url'] = null;
        }

        return $data;
    }
}
