<?php

namespace App\Http\Controllers\Admin\Regulation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Regulation\RegulationRequest;
use App\Models\Media;
use App\Models\Regulation;
use App\Models\RegulationType;
use App\Models\Unit;
use App\Services\Regulation\RegulationService;
use Illuminate\Http\Request;

class RegulationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:200',
            'type' => 'nullable|integer',
            'year' => 'nullable|integer|between:1900,2200',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        $query = Regulation::with('type')->latest();

        if ($search = trim($filters['search'] ?? '')) {
            $query->where(fn ($q) =>
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('number', 'ilike', "%{$search}%")
            );
        }

        foreach ([
            'type' => 'regulation_type_id',
            'year' => 'year',
            'status' => 'publication_status',
        ] as $key => $column) {
            if (!empty($filters[$key])) {
                $query->where($column, $filters[$key]);
            }
        }

        return view('admin.regulations.index', [
            'items' => $query->paginate(15)->withQueryString(),
            'types' => RegulationType::orderBy('sort_order')
                ->orderBy('name')->get(),
        ]);
    }

    private function form(Regulation $regulation)
    {
        $regulation->load('documents.media');

        return view('admin.regulations.form', [
            'regulation' => $regulation,
            'types' => RegulationType::orderBy('sort_order')
                ->orderBy('name')->get(),
            'units' => Unit::where('is_active', true)
                ->orWhere('id', $regulation->unit_id)
                ->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return $this->form(new Regulation([
            'year' => date('Y'),
            'legal_status' => 'unverified',
            'publication_status' => 'draft',
        ]));
    }

    public function edit(Regulation $regulation)
    {
        return $this->form($regulation);
    }

    public function store(
        RegulationRequest $request,
        RegulationService $service
    ) {
        $regulation = $service->save($request, new Regulation);

        return redirect()->route('admin.regulations.edit', $regulation)
            ->with('success', 'Regulasi berhasil disimpan.');
    }

    public function update(
        RegulationRequest $request,
        Regulation $regulation,
        RegulationService $service
    ) {
        $service->save($request, $regulation);

        return back()->with('success', 'Regulasi berhasil diperbarui.');
    }

    public function destroy(Regulation $regulation)
    {
        $regulation->delete();

        return redirect()->route('admin.regulations.index')
            ->with('success', 'Regulasi dihapus. Dokumen tetap tersimpan.');
    }
}
