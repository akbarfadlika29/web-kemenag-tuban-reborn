<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\User;
use App\Services\Access\AccessService;
use App\Services\Access\NewsEditorialPolicy;
use App\Services\Access\NewsEditorialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsEditorialController extends Controller
{
    public function show(Request $request, News $news)
    {
        abort_unless(
            app(AccessService::class)->allowsUnit(
                $request->user(), 'news.view', $news->unit_id
            ),
            403
        );

        $news->load(['author', 'unit']);

        return view('admin.news.editorial', [
            'news' => $news,
            'policy' => app(NewsEditorialPolicy::class),
            'history' => DB::table('news_editorial_events as e')
                ->leftJoin('users as u', 'u.id', '=', 'e.actor_id')
                ->where('e.news_id', $news->id)
                ->select('e.*', 'u.name as actor_name')
                ->orderByDesc('e.id')
                ->paginate(15),
        ]);
    }

    public function submit(Request $request, News $news)
    {
        return $this->run($request, $news, 'submit');
    }

    public function reject(Request $request, News $news)
    {
        return $this->run($request, $news, 'reject');
    }

    public function publish(Request $request, News $news)
    {
        return $this->run($request, $news, 'publish');
    }

    private function run(Request $request, News $news, string $action)
    {
        $data = $request->validate([
            'editorial_version' => ['required', 'integer', 'min:0'],
            'reason' => [
                $action === 'reject' ? 'required' : 'nullable',
                'string', 'max:4000',
            ],
        ]);

        DB::transaction(function () use ($request, $news, $action, $data) {
            $current = News::query()
                ->whereKey($news->id)
                ->lockForUpdate()
                ->firstOrFail();

            $actor = User::findOrFail($request->user()->id);

            app(NewsEditorialService::class)->transition(
                $actor,
                $current,
                $action,
                (int) $data['editorial_version'],
                $data['reason'] ?? null
            );
        });

        return redirect()
            ->route('admin.news.editorial', $news)
            ->with('success', match ($action) {
                'submit' => 'Berita diajukan untuk diperiksa.',
                'reject' => 'Berita dikembalikan beserta alasan penolakan.',
                'publish' => 'Berita berhasil diterbitkan.',
            });
    }
}