<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{

    public function index()
    {
        $districts = District::all();
        return view('backend.districts.index', compact('districts'));
    }


    public function create()
    {
        return view('backend.districts.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        District::create([
            'name' => $request->name,
        ]);

        return redirect()->route('districts.index')->with('success', 'District created successfully!');
    }

    public function show($id)
    {

        $district = District::find($id);

        return view('backend.districts.show', compact('district'));
    }



    public function edit($id)
    {

        $district = District::find($id);

        return view('backend.districts.edit', compact('district'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $district = District::find($id);

        $district->update([
            'name' => $request->name,
        ]);

        return redirect()->route('districts.index')->with('success', 'District updated successfully!');
    }


    public function destroy($id)
    {
        $district = District::find($id);
        $district->delete();

        return redirect()->route('districts.index')->with('destroy', 'District deleted successfully!');
    }
}
