<?php

namespace App\Http\Controllers;

use App\Models\Room_Type;
use App\Models\room;
use App\Models\view;

use Illuminate\Http\Request;

class AdminRoomController extends Controller
{
    public function index()
    {
        $roomTypes = Room_Type::all()->where('delete_status', '0');
        $views = View::all(); // Fetch views

        $rooms = Room::with(['roomType', 'view'])
            ->whereHas('roomType', function ($query) {
                $query->where('delete_status', 0);
            })
            ->get();

        return view('admin.rooms', compact('roomTypes', 'views', 'rooms'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|integer|unique:room,room_id',
            'room_type_id' => 'required|exists:room_type,room_type_id',
            'view_id' => 'required|exists:view,view_id',
            'floor' => 'required|integer',
            'is_held' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
        ]);

        $data['is_available'] = $request->has('is_available') ? 0 : 1;

        Room::create($data);

        return redirect()->back()->with('success', 'Room added successfully!');
    }

    public function saveRoomStatuses(Request $request)
    {
        $roomStatuses = $request->input('rooms');

        if ($roomStatuses) {
            foreach ($roomStatuses as $roomId => $status) {
                $room = Room::find($roomId);
                if ($room) {
                    $room->is_available = $status; 
                    $room->save();
                }
            }
        }

        return redirect()->route('admin.rooms.index')->with('success', 'Room statuses updated successfully!');
    }

    public function edit($id)
    {
        $room = Room::findOrFail($id);
        $roomTypes = Room_Type::all()->where('delete_status','0');
        $views = View::all();

        return view('admin.edit_room', compact('room', 'roomTypes', 'views'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $room->update($request->all());

        return redirect()->route('admin.rooms.index')->with('success', 'Room updated successfully!');
    }

    public function destroy($id)
    {
        Room::findOrFail($id)->delete();

        return redirect()->route('admin.rooms.index')->with('success', 'Room deleted successfully!');
    }
    
}
