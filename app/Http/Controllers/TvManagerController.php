<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\TvManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TvManagerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $properties = $user->properties()->orderBy('name')->get();

        $selectedPropertyId = $request->get('property_id');
        $search = $request->get('search', '');

        $tvManagers = collect();
        $selectedProperty = null;

        if ($selectedPropertyId) {
            $selectedProperty = $properties->find($selectedPropertyId);

            if ($selectedProperty) {
                $query = $selectedProperty->tvManagers()
                    ->orderBy('guest_name');

                // Apply search filter
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('guest_name', 'like', "%{$search}%")
                          ->orWhere('area_name', 'like', "%{$search}%");
                    });
                }

                $tvManagers = $query->paginate(12);
            }
        }

        return Inertia::render('TvManagers/Index', [
            'properties' => $properties,
            'selectedProperty' => $selectedProperty,
            'tvManagers' => $tvManagers,
            'filters' => [
                'property_id' => $selectedPropertyId,
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $properties = $user->properties()->orderBy('name')->get();

        return Inertia::render('TvManagers/Create', [
            'properties' => $properties,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|integer|exists:properties,id',
            'area_name' => 'required|string|max:255',
            'guest_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $property = $user->properties()->findOrFail($request->property_id);

        $tvManager = new TvManager([
            'property_id' => $property->id,
            'area_name' => $request->area_name,
            'guest_name' => $request->guest_name,
            'birth_date' => $request->birth_date,
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('tv-managers', 'public');
            $tvManager->image = $imagePath;
        }

        $tvManager->save();

        return redirect()->route('tv-managers.index', [
            'property_id' => $property->id
        ])->with('success', 'Guest added successfully');
    }

    public function edit(TvManager $tvManager)
    {
        $user = Auth::user();
        $properties = $user->properties()->orderBy('name')->get();

        return Inertia::render('TvManagers/Edit', [
            'tvManager' => $tvManager,
            'properties' => $properties,
        ]);
    }

    public function update(Request $request, TvManager $tvManager)
    {
        $request->validate([
            'property_id' => 'required|integer|exists:properties,id',
            'area_name' => 'required|string|max:255',
            'guest_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        $user = Auth::user();

        // Check if user owns the property
        $property = $user->properties()->find($request->property_id);
        if (!$property) {
            return back()->withErrors(['property_id' => 'Property not found or you do not have access.']);
        }

        $tvManager->update([
            'property_id' => $property->id,
            'area_name' => $request->area_name,
            'guest_name' => $request->guest_name,
            'birth_date' => $request->birth_date,
        ]);

        return redirect()->route('tv-managers.index', [
            'property_id' => $property->id
        ])->with('success', 'Guest data updated successfully');
    }

    public function updateImage(Request $request, TvManager $tvManager)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($tvManager->image) {
                \Storage::disk('public')->delete($tvManager->image);
            }

            $imagePath = $request->file('image')->store('tv-managers', 'public');
            $tvManager->update(['image' => $imagePath]);
        }

        return back()->with('success', 'Guest photo updated successfully');
    }

    public function destroy(TvManager $tvManager)
    {
        $propertyId = $tvManager->property_id;

        // Delete image if exists
        if ($tvManager->image) {
            \Storage::disk('public')->delete($tvManager->image);
        }

        $tvManager->delete();

        return redirect()->route('tv-managers.index', [
            'property_id' => $propertyId
        ])->with('success', 'Guest data deleted successfully');
    }
}
