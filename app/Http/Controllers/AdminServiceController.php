<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\ValidatedData;
use App\Models\extra_service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AdminServiceController extends Controller
{
    public function index()
    {
        $services = extra_service::all();
        return view('admin.service', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_name' => 'required|string|max:255',
            'service_price' => 'required|numeric',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('service_images', 'public');
        }

        extra_service::create($data);

        return redirect()->route('admin.service.index')->with('success', 'Extra service added successfully!');
    }

    public function edit($id)
    {
        $service = extra_service::findOrFail($id);

        return view('admin.edit_service', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $service = extra_service::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'service_price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $service->service_name = $request->name;
        $service->description = $request->description;
        $service->service_price = $request->service_price;

        if ($request->hasFile('image')) {
            if ($service->image) {
                $oldImagePath = public_path('storage/' . basename($service->image));
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }
            // Store new image
            $newImagePath = $request->file('image')->store('service_images', 'public');
            $service->image = $newImagePath;
        }

        $service->save();

        return redirect()->route('admin.service.index')->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        extra_service::findOrFail($id)->delete();

        return redirect()->route('admin.service.index')->with('success', 'Service deleted successfully.');
    }
}
