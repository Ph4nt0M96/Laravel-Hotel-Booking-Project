<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guest;
use App\Models\Booking;
use App\Models\room_type;
use App\Models\Room;
use App\Models\booking_detail;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get the count of registered guests
        $guestCount = Guest::count();

        // Get the count of pending bookings
        $pendingBookings = Booking::where('booking_status', 'pending')->count();

        // Find the most booked room type
        $mostBookedRoomType = Booking_Detail::join('room', 'booking_detail.room_id', '=', 'room.room_id')
            ->join('room_type', 'room.room_type_id', '=', 'room_type.room_type_id')
            ->select('room_type.room_type', DB::raw('count(booking_detail.detail_id) as booking_count'))
            ->groupBy('room_type.room_type')
            ->orderBy('booking_count', 'desc')
            ->first();

        // Get counts for each room type
        $standardCount = Room::where('room_type_id', Room_Type::where('room_type', 'Standard')->first()->room_type_id)->count();
        $deluxeCount = Room::where('room_type_id', Room_Type::where('room_type', 'Deluxe')->first()->room_type_id)->count();
        $suiteCount = Room::where('room_type_id', Room_Type::where('room_type', 'Suite')->first()->room_type_id)->count();
        $familyCount = Room::where('room_type_id', Room_Type::where('room_type', 'Family')->first()->room_type_id)->count();

        return view('admin.dashboard', [
            'guestCount' => $guestCount,
            'pendingBookings' => $pendingBookings,
            'mostBookedRoomType' => $mostBookedRoomType->room_type ?? 'N/A',
            'deluxeCount' => $deluxeCount,
            'standardCount' => $standardCount,
            'suiteCount'=>$suiteCount,
            'familyCount' => $familyCount,
        ]);
    }
}
