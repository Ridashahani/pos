<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Variation\StoreVariationRequest;
use App\Http\Requests\Variation\UpdateVariationRequest;
use App\Models\Variation;
use Illuminate\Support\Facades\Redirect;

class VariationController extends Controller
{
    public function index()
    {
        $variations = Variation::query()->when(request('search'), function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        })->latest()->paginate((int) request('row', 10))->withQueryString();

        return view('variations.index', compact('variations'));
    }

    public function create() { return view('variations.create'); }

    public function store(StoreVariationRequest $request)
    {
        Variation::create(['name' => $request->validated('name'), 'types' => array_values($request->validated('types'))]);
        return Redirect::route('variations.index')->with('success', 'Variation has been created!');
    }

    public function edit(Variation $variation) { return view('variations.edit', compact('variation')); }

    public function update(UpdateVariationRequest $request, Variation $variation)
    {
        $variation->update(['name' => $request->validated('name'), 'types' => array_values($request->validated('types'))]);
        return Redirect::route('variations.index')->with('success', 'Variation has been updated!');
    }

    public function destroy(Variation $variation)
    {
        $variation->delete();
        return Redirect::route('variations.index')->with('success', 'Variation has been deleted!');
    }
}