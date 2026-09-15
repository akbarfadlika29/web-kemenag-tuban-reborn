<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\ExperienceSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicInteractionController extends Controller
{
    public function store(
        Request $request,
        string $key,
        ExperienceSettings $settings
    ): JsonResponse {
        $data = $request->validate([
            'action' => ['required', 'in:view,like,status'],
            'liked' => ['required_if:action,like', 'boolean'],
        ]);

        $preferences = $settings->all();

        $visitor = hash_hmac(
            'sha256',
            $request->session()->getId(),
            (string) config('app.key')
        );

        $identity = [
            'page_key' => $key,
            'visitor_key' => $visitor,
        ];

        if ($data['action'] === 'view' && $preferences['views']) {
            DB::table('public_page_interactions')->insertOrIgnore([
                ...$identity,
                'kind' => 'view',
                'period' => now()->toDateString(),
                'created_at' => now(),
            ]);
        }

        if ($data['action'] === 'like') {
            abort_unless($preferences['likes'], 403, 'Fitur suka dinonaktifkan.');

            $like = [...$identity, 'kind' => 'like', 'period' => 'all'];

            if ($request->boolean('liked')) {
                DB::table('public_page_interactions')->insertOrIgnore([
                    ...$like,
                    'created_at' => now(),
                ]);
            } else {
                DB::table('public_page_interactions')->where($like)->delete();
            }
        }

        $base = DB::table('public_page_interactions')->where('page_key', $key);

        return response()->json([
            'views' => $preferences['views']
                ? (clone $base)->where('kind', 'view')->count() : 0,
            'likes' => $preferences['likes']
                ? (clone $base)->where('kind', 'like')->count() : 0,
            'liked' => $preferences['likes'] && (clone $base)
                ->where('kind', 'like')
                ->where('visitor_key', $visitor)
                ->exists(),
        ])->header('Cache-Control', 'private, no-store');
    }
}
