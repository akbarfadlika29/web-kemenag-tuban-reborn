<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $state = (string) $request->input(
            'state',
            'upcoming'
        );

        $agendas = Agenda::query()
            ->published()
            ->with([
                'unit',
                'coverMedia',
            ])
            ->when(
                $state === 'upcoming',
                fn ($query) =>
                    $query->where(
                        'start_at',
                        '>',
                        now()
                    )
            )
            ->when(
                $state === 'finished',
                fn ($query) =>
                    $query->where(
                        function ($query) {
                            $query
                                ->where(
                                    'end_at',
                                    '<',
                                    now()
                                )
                                ->orWhere(
                                    function ($query) {
                                        $query
                                            ->whereNull('end_at')
                                            ->where(
                                                'start_at',
                                                '<',
                                                now()->startOfDay()
                                            );
                                    }
                                );
                        }
                    )
            )
            ->orderBy('start_at')
            ->paginate(12)
            ->withQueryString();

        return view(
            'frontend.agendas.index',
            compact(
                'agendas',
                'state'
            )
        );
    }

    public function show(string $slug): View
    {
        $agenda = Agenda::query()
            ->published()
            ->with([
                'unit',
                'coverMedia',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view(
            'frontend.agendas.show',
            compact('agenda')
        );
    }
}