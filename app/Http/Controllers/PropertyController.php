<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = auth()->user()->properties()->get();

        return Inertia::render('Properties/Index', [
            'properties' => $properties,
        ]);
    }

            public function getUserProperties()
    {
        $properties = auth()->user()->properties()->select('id', 'name', 'hotel_category')->get();

        return response()->json($properties);
    }

    public function create()
    {
        return Inertia::render('Properties/Create');
    }

    public function show(Property $property)
    {
        // Ensure user can only view their own properties
        if ($property->user_id !== auth()->id()) {
            abort(403);
        }

        $property->load('images');

        return Inertia::render('Properties/Show', [
            'property' => $property
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'total_rooms' => 'nullable|integer|min:1',
            'hotel_category' => 'nullable|string|in:budget,mid-range,luxury',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $property = auth()->user()->properties()->create($validated);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('property-images', 'public');
                $property->images()->create([
                    'image' => $path,
                    'type' => 'interior'
                ]);
            }
        }

        return redirect()->route('properties.index')
            ->with('success', 'Hotel berhasil ditambahkan');
    }

    public function update(Request $request, Property $property)
    {
        // Ensure user can only update their own properties
        if ($property->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string|max:255',
            'state' => 'sometimes|nullable|string|max:255',
            'zip' => 'sometimes|nullable|string|max:255',
            'country' => 'sometimes|nullable|string|max:255',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'star_rating' => 'sometimes|nullable|integer|min:1|max:5',
            'total_rooms' => 'sometimes|nullable|integer|min:1',
            'hotel_category' => 'sometimes|nullable|string|in:budget,mid-range,luxury',
        ]);

        $property->update($validated);

        return back()->with('success', 'Properti berhasil diperbarui');
    }

    public function updateImages(Request $request, Property $property)
    {
        // Log untuk debugging
        \Log::info('updateImages called', [
            'property_id' => $property->id,
            'user_id' => auth()->id(),
            'has_files' => $request->hasFile('new_images'),
            'file_count' => $request->hasFile('new_images') ? count($request->file('new_images')) : 0,
        ]);

        // Ensure user can only update their own properties
        if ($property->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'images_to_delete.*' => 'nullable|integer|exists:property_images,id'
        ]);

        $uploadedCount = 0;
        $deletedCount = 0;

        // Handle image deletions
        if ($request->has('images_to_delete')) {
            $imagesToDelete = $request->input('images_to_delete');
            $images = $property->images()->whereIn('id', $imagesToDelete)->get();
            foreach ($images as $img) {
                if ($img->image && \Storage::disk('public')->exists($img->image)) {
                    \Storage::disk('public')->delete($img->image);
                }
                $img->delete();
                $deletedCount++;
            }
        }

        // Handle new image uploads
        if ($request->hasFile('new_images')) {
            \Log::info('Processing new images', ['count' => count($request->file('new_images'))]);

            foreach ($request->file('new_images') as $index => $image) {
                \Log::info('Processing image', [
                    'index' => $index,
                    'name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'mime' => $image->getMimeType()
                ]);

                $path = $image->store('property-images', 'public');
                \Log::info('Image stored', ['path' => $path]);

                $property->images()->create([
                    'image' => $path,
                    'type' => 'interior'
                ]);
                $uploadedCount++;
            }
        }

        $message = '';
        if ($uploadedCount > 0 && $deletedCount > 0) {
            $message = "Berhasil mengupload {$uploadedCount} gambar dan menghapus {$deletedCount} gambar.";
        } elseif ($uploadedCount > 0) {
            $message = "Berhasil mengupload {$uploadedCount} gambar.";
        } elseif ($deletedCount > 0) {
            $message = "Berhasil menghapus {$deletedCount} gambar.";
        } else {
            $message = 'Tidak ada perubahan gambar.';
        }

        \Log::info('updateImages completed', [
            'uploaded_count' => $uploadedCount,
            'deleted_count' => $deletedCount,
            'message' => $message
        ]);

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'uploaded_count' => $uploadedCount,
                'deleted_count' => $deletedCount
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroyImage($id)
    {
        $image = \App\Models\PropertyImage::findOrFail($id);
        $property = $image->property;
        if ($property->user_id !== auth()->id()) {
            abort(403);
        }
        if ($image->image && \Storage::disk('public')->exists($image->image)) {
            \Storage::disk('public')->delete($image->image);
        }
        $image->delete();
        return response()->json(['success' => true], 200);
    }
}

