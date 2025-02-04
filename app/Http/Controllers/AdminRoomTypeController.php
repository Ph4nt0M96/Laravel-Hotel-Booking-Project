<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\room_type;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AdminRoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = Room_Type::all()->where('delete_status', '0');
        $DelroomTypes = Room_Type::all()->where('delete_status', '1');

        return view('admin.room_type', compact('roomTypes', 'DelroomTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_type' => 'required|string|max:255',
            'base_price' => 'required|numeric',
            'max_occupancy' => 'required|integer',
            'amenities' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|image',
            'image2' => 'nullable|image',
            'image3' => 'nullable|image',
            'image4' => 'nullable|image',
        ]);

        foreach (['image', 'image2', 'image3', 'image4'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $data[$imageField] = $request->file($imageField)->store('room_type_images', 'public');
            }
        }

        Room_Type::create($data);

        return redirect()->back()->with('success', 'Room type added successfully!');
    }

    public function edit($id)
    {
        $roomType = Room_Type::findOrFail($id);

        return view('admin.edit_room_type', compact('roomType'));
    }
    public function update(Request $request, $id)
    {
        $roomType = Room_Type::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'amenities' => 'required|string',
            'base_price' => 'required|numeric',
            'max_occupancy' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $roomType->room_type = $request->name;
        $roomType->description = $request->description;
        $roomType->amenities = $request->amenities;
        $roomType->base_price = $request->base_price;
        $roomType->max_occupancy = $request->max_occupancy;

        foreach (['image', 'image2', 'image3', 'image4'] as $imageField) {
            if ($request->hasFile($imageField)) {
                // Delete old image if it exists
                if ($roomType->$imageField) {
                    $oldImagePath = public_path('storage/' . basename($roomType->$imageField));
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }
                // Store new image
                $newImagePath = $request->file($imageField)->store('room_type_images', 'public');
                $roomType->$imageField = $newImagePath;
            }
        }

        $roomType->save();

        return redirect()->route('admin.room_type.index')->with('success', 'Room Type updated successfully!');
    }

    public function destroy($id)
    {
        $roomType = Room_Type::findOrFail($id);
        $roomType->delete_status = '1';
        $roomType->save();

        return redirect()->route('admin.room_type.index')->with('success', 'Room Type deleted successfully!');
    }

    public function restore($id)
    {
        $roomType = Room_Type::findOrFail($id);
        $roomType->delete_status = '0';
        $roomType->save();

        return redirect()->route('admin.room_type.index')->with('success', 'Room Type deleted successfully!');
    }
}
