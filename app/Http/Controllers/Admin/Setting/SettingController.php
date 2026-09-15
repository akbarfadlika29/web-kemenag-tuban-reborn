<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\UpdateSettingRequest;
use App\Services\Setting\SiteSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(
        SiteSettingService $settings
    ): View {
        return view(
            'admin.settings.index',
            [
                'groups' =>
                    $settings->definitions(),

                'values' =>
                    $settings->all(),

                'logoMedia' =>
                    $settings->logo(),
            ]
        );
    }

    public function update(
        UpdateSettingRequest $request,
        SiteSettingService $settings
    ): RedirectResponse {
        $settings->update(
            $request->validated(
                'settings'
            )
        );

        return redirect()
            ->route(
                'admin.settings.index'
            )
            ->with(
                'success',
                'Pengaturan website berhasil disimpan.'
            );
    }
}
