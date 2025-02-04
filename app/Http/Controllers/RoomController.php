<?php

namespace App\Http\Controllers;

use App\Models\room_type;
use Illuminate\Http\Request;
use App\Models\view;
use App\Models\room;


class RoomController extends Controller
{
    public function showRoomTypes()
    {
        $roomTypes = room_type::all()->where('delete_status','0');
        return view('room_type', ['roomTypes' => $roomTypes]);
    }

    public function showDetails($roomTypeId)
    {
        $roomType = Room_Type::findOrFail($roomTypeId);

        $views = View::all()->map(function ($view) use ($roomTypeId) {

            $availableRooms = Room::where('room_type_id', $roomTypeId)
                ->where('view_id', $view->view_id)
                ->where('is_available', 0)
                ->where('is_held', 0)
                ->count();

            $heldRooms = Room::where('room_type_id', $roomTypeId)
                ->where('view_id', $view->view_id)
                ->where('is_held', 1)
                ->count();

            return [
                'view' => $view,
                'availableRooms' => $availableRooms,
                'heldRooms' => $heldRooms,
            ];
        });

        return view('room-details', compact('roomType', 'views'));
    }
}
