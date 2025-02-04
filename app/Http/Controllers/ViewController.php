<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\view;
use Illuminate\Support\Facades\File;

class ViewController extends Controller
{
    public function index()
    {
        $views = view::all()->where('delete_status', '0');
        $Deviews = view::all()->where('delete_status', '1');
        return view('admin.view', compact('views', 'Deviews'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'view_name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('view_images', 'public');
        }

        view::create($data);

        return redirect()->route('admin.view.index')->with('success', 'View added successfully!');
    }

    public function edit($id)
    {
        $view = view::findOrFail($id);

        return view('admin.edit_view', compact('view'));
    }

    public function update(Request $request, $id)
    {
        $view = view::findOrFail($id);

        $request->validate([
            'view_name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $view->view_name = $request->view_name;
        $view->description = $request->description;

        if ($request->hasFile('image')) {
            if ($view->image) {
                $oldImagePath = public_path('storage/' . basename($view->image));
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }
            // Store new image
            $newImagePath = $request->file('image')->store('view_images', 'public');
            $view->image = $newImagePath;
        }

        $view->save();

        return redirect()->route('admin.view.index')->with('success', 'View updated successfully.');
    }

    public function destroy($id)
    {

        $view = view::findOrFail($id);
        $view->delete_status= '1';
        $view->save();
        return redirect()->route('admin.view.index')->with('success', 'View deleted successfully.');
    }

    public function restore($id)
    {
        $view = view::findOrFail($id);
        $view->delete_status = '0';
        $view->save();

        return redirect()->route('admin.view.index')->with('success', 'Room Type deleted successfully!');
    }
}
