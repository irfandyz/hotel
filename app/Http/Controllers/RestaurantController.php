<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RestaurantCategory;
use App\Models\RestaurantMenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $properties = $user->properties()->orderBy('name')->get();

        $selectedPropertyId = $request->get('property_id');
        $selectedCategories = $request->get('categories', []);
        $search = $request->get('search', '');

        $menuItems = collect();
        $selectedProperty = null;

        if ($selectedPropertyId) {
            $selectedProperty = $properties->find($selectedPropertyId);

            if ($selectedProperty) {
                $query = $selectedProperty->restaurantMenuItems()
                    ->with('categories')
                    ->orderBy('name');

                // Apply search filter
                if ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                }

                // Apply category filter
                if (!empty($selectedCategories)) {
                    $query->whereHas('categories', function ($q) use ($selectedCategories) {
                        $q->whereIn('restaurant_categories.id', $selectedCategories);
                    });
                }

                $menuItems = $query->paginate(12);
            }
        }

        $categories = RestaurantCategory::orderBy('name')->get();

        return Inertia::render('Restaurants/Index', [
            'properties' => $properties,
            'selectedProperty' => $selectedProperty,
            'menuItems' => $menuItems,
            'categories' => $categories,
            'filters' => [
                'property_id' => $selectedPropertyId,
                'categories' => $selectedCategories,
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $properties = $user->properties()->orderBy('name')->get();
        $categories = RestaurantCategory::orderBy('name')->get();

        return Inertia::render('Restaurants/Create', [
            'properties' => $properties,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:enabled,disabled',
            'categories' => 'array',
            'categories.*' => 'exists:restaurant_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $property = $user->properties()->findOrFail($request->property_id);

        $menuItem = new RestaurantMenuItem([
            'property_id' => $property->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'status' => $request->status,
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('restaurant-menu', 'public');
            $menuItem->image = $imagePath;
        }

        $menuItem->save();

        // Attach categories
        if ($request->has('categories')) {
            $menuItem->categories()->attach($request->categories);
        }

        return redirect()->route('restaurants.index', [
            'property_id' => $property->id
        ])->with('success', 'Menu item berhasil ditambahkan');
    }

    public function edit(RestaurantMenuItem $menuItem)
    {
        $user = Auth::user();
        $properties = $user->properties()->orderBy('name')->get();
        $categories = RestaurantCategory::orderBy('name')->get();

        // Load the menu item with its categories
        $menuItem->load('categories');

        return Inertia::render('Restaurants/Edit', [
            'menuItem' => $menuItem,
            'properties' => $properties,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, RestaurantMenuItem $menuItem)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:enabled,disabled',
            'categories' => 'array',
            'categories.*' => 'exists:restaurant_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $property = $user->properties()->findOrFail($request->property_id);

        $menuItem->update([
            'property_id' => $property->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'status' => $request->status,
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($menuItem->image) {
                \Storage::disk('public')->delete($menuItem->image);
            }

            $imagePath = $request->file('image')->store('restaurant-menu', 'public');
            $menuItem->update(['image' => $imagePath]);
        }

        // Sync categories
        $menuItem->categories()->sync($request->categories ?? []);

        return redirect()->route('restaurants.index', [
            'property_id' => $property->id
        ])->with('success', 'Menu item berhasil diperbarui');
    }

    public function destroy(RestaurantMenuItem $menuItem)
    {
        $propertyId = $menuItem->property_id;

        // Delete image if exists
        if ($menuItem->image) {
            \Storage::disk('public')->delete($menuItem->image);
        }

        $menuItem->delete();

        return redirect()->route('restaurants.index', [
            'property_id' => $propertyId
        ])->with('success', 'Menu item berhasil dihapus');
    }
}
