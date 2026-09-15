<?php

namespace App\Providers;

use App\Models\Scopes\AdminUnitScope;
use Illuminate\Support\ServiceProvider;

class PpidAccessScopeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $models = [
            \App\Models\News::class => 'news',
            \App\Models\Page::class => 'pages',
            \App\Models\Announcement::class => 'announcements',
            \App\Models\Agenda::class => 'agendas',
            \App\Models\Gallery::class => 'galleries',
            \App\Models\PpidInformation::class => 'ppid-informations',
            \App\Models\Service::class => 'services',
            \App\Models\Regulation::class => 'regulations',
            \App\Models\Media::class => 'media',
        ];

        foreach ($models as $model => $module) {
            $model::addGlobalScope(new AdminUnitScope($module));
        }
    }
}
