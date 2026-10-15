<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Support\Facades\Redirect;
use Spatie\QueryBuilder\QueryBuilder;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::search(request('search'))
            ->latest()
            ->paginate(6);
        return view('Unit.index', compact('units'));
    }

    public function create()
    {
        return view('Unit.create');
    }

    public function store(StoreUnitRequest $request)
    {
        Unit::create($request->validated());

        return Redirect::route('units.index')->with('success', 'Unit has been created!');
    }

    public function edit(Unit $unit)
    {
        return view('Unit.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return Redirect::route('units.index')->with('success', 'Unit has been updated!');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return Redirect::route('units.index')->with('success', 'Unit has been deleted!');
    }
}
