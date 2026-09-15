<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class BulkDeleteResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = [
            'admin.agendas.destroy',
            'admin.regulations.destroy',
            'admin.regulation-types.destroy',
            'admin.announcements.destroy',
            'admin.galleries.destroy',
            'admin.hero-slides.destroy',
            'admin.media.destroy',
            'admin.menus.destroy',
            'admin.news.destroy',
            'admin.news-categories.destroy',
            'admin.news-tags.destroy',
            'admin.pages.destroy',
            'admin.ppid-categories.destroy',
            'admin.ppid-informations.destroy',
            'admin.quick-links.destroy',
            'admin.related-links.destroy',
            'admin.services.destroy',
            'admin.service-categories.destroy',
            'admin.units.destroy',
        ];

        if (
            $request->header('X-PPID-Bulk') !== '1'
            || !$request->isMethod('DELETE')
            || !in_array($request->route()?->getName(), $allowed, true)
        ) {
            return $next($request);
        }

        abort_unless($request->user(), 401);

        /*
         * Prevent an older flash message from being mistaken
         * for the outcome of this request.
         */
        $session = $request->session();
        $session->forget(['success', 'error']);

        /*
         * Existing route middleware, model binding, and controller
         * still perform the actual deletion.
         */
        $response = $next($request);

        $success = $session->get('success');
        $error = $session->get('error');

        $session->forget(['success', 'error']);

        if (!$response instanceof RedirectResponse) {
            return $response;
        }

        if (is_string($error) && $error !== '') {
            return response()->json([
                'ppid_bulk' => true,
                'ok' => false,
                'message' => $error,
            ], 409);
        }

        if (is_string($success) && $success !== '') {
            return response()->json([
                'ppid_bulk' => true,
                'ok' => true,
                'message' => $success,
            ]);
        }

        return response()->json([
            'ppid_bulk' => true,
            'ok' => false,
            'uncertain' => true,
            'message' => 'Hasil belum dapat dipastikan. Muat ulang halaman sebelum mencoba lagi.',
        ], 409);
    }
}
