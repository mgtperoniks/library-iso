<?php

namespace App\Http\Controllers\QualityObjective;

use App\Http\Controllers\Controller;
use App\Http\Requests\QualityObjective\StorePeriodRequest;
use App\Http\Requests\QualityObjective\UpdatePeriodRequest;
use App\Models\QualityObjectivePeriod;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    /**
     * Display a listing of the periods.
     */
    public function index()
    {
        $periods = QualityObjectivePeriod::withCount('objectives')
            ->orderByDesc('year')
            ->paginate(15);

        return view('quality-objectives.periods.index', compact('periods'));
    }

    /**
     * Show the form for creating a new period.
     */
    public function create()
    {
        return view('quality-objectives.periods.create');
    }

    /**
     * Store a newly created period in storage.
     */
    public function store(StorePeriodRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $period = QualityObjectivePeriod::create($data);

        return redirect()
            ->route('quality-objectives.periods.index')
            ->with('success', "Periode tahun {$period->year} berhasil dibuat.");
    }

    /**
     * Show the form for editing the specified period.
     */
    public function edit($id)
    {
        $period = QualityObjectivePeriod::findOrFail($id);
        return view('quality-objectives.periods.edit', compact('period'));
    }

    /**
     * Update the specified period in storage.
     */
    public function update(UpdatePeriodRequest $request, $id)
    {
        $period = QualityObjectivePeriod::findOrFail($id);
        $data = $request->validated();

        if ($data['status'] === 'active' && $period->status !== 'active') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        }

        $period->update($data);

        return redirect()
            ->route('quality-objectives.periods.index')
            ->with('success', "Periode tahun {$period->year} berhasil diperbarui.");
    }

    /**
     * Remove the specified period from storage.
     */
    public function destroy($id)
    {
        $period = QualityObjectivePeriod::findOrFail($id);
        
        if ($period->objectives()->exists()) {
            return back()->with('error', 'Periode tidak dapat dihapus karena memiliki sasaran mutu aktif.');
        }

        $period->delete();

        return redirect()
            ->route('quality-objectives.periods.index')
            ->with('success', 'Periode berhasil dihapus.');
    }
}
