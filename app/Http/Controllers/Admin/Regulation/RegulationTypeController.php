<?php

namespace App\Http\Controllers\Admin\Regulation;

use App\Http\Controllers\Controller;
use App\Models\RegulationType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegulationTypeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['edit' => 'nullable|integer']);

        return view('admin.regulations.types', [
            'types' => RegulationType::orderBy('sort_order')
                ->orderBy('name')->paginate(20)->withQueryString(),
            'editing' => $request->filled('edit')
                ? RegulationType::findOrFail($request->edit)
                : new RegulationType(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    private function save(Request $request, RegulationType $type)
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('regulation_types')->ignore($type->id),
            ],
            'description' => 'nullable|string|max:3000',
            'sort_order' => 'required|integer|between:0,9999',
            'is_active' => 'required|boolean',
        ]);

        if (!$type->exists) {
            $data['slug'] = (Str::slug($data['name']) ?: 'jenis')
                . '-' . Str::lower(Str::random(6));
        }

        $type->fill($data)->save();

        return redirect()->route('admin.regulation-types.index')
            ->with('success', 'Jenis regulasi disimpan.');
    }

    public function store(Request $request)
    {
        return $this->save($request, new RegulationType);
    }

    public function update(Request $request, RegulationType $regulationType)
    {
        return $this->save($request, $regulationType);
    }

    public function destroy(RegulationType $regulationType)
    {
        if ($regulationType->regulations()->withTrashed()->exists()) {
            return back()->with(
                'error',
                'Jenis masih terhubung ke regulasi. Nonaktifkan jika tidak dipakai untuk entri baru.'
            );
        }

        $regulationType->delete();

        return back()->with('success', 'Jenis regulasi dihapus.');
    }
}
