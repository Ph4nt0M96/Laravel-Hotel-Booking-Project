<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\booking;
use Illuminate\Support\Facades\DB;
use App\Models\guest;

class GuestManagmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Active guests search
        $users = User::where('role', 'guest')
            ->where('ban_status', 0)
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhereHas('guest', function ($query) use ($search) {
                        $query->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%")
                            ->orWhere('nrc_no', 'LIKE', "%{$search}%");
                    });
            })
            ->get();

        // Banned guests search
        $bannedUsers = User::where('role', 'guest')
            ->where('ban_status', 1)
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhereHas('guest', function ($query) use ($search) {
                        $query->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%")
                            ->orWhere('nrc_no', 'LIKE', "%{$search}%");
                    });
            })
            ->get();

        return view('admin.guest_management', compact('users', 'bannedUsers'));
    }

    public function banGuest($id)
    {
        $guest = Guest::findOrFail($id);
        $user = $guest->user;
        $user->ban_status = 1;
        $user->save();

        return response()->json(['success' => 'Guest banned successfully.']);
    }

    public function unbanGuest($id)
    {
        $guest = Guest::findOrFail($id);
        $user = $guest->user;
        $user->ban_status = 0;
        $user->save();

        return response()->json(['success' => 'Guest unbanned successfully.']);
    }

    public function getGuestBookings($id)
    {
        $bookings = DB::table('booking_detail')
            ->join('booking', 'booking_detail.booking_id', '=', 'booking.booking_id')
            ->join('room', 'booking_detail.room_id', '=', 'room.room_id')
            ->join('room_type', 'room_type.room_type_id', '=', 'room.room_type_id')
            ->where('booking.guest_id', $id)
            ->select(
                'booking.booking_id',
                'booking.booking_status',
                'booking_detail.detail_id',
                'booking_detail.check_in_date',
                'booking_detail.check_out_date',
                'booking_detail.room_id',
                'room_type.room_type',
            )
            ->paginate(7);

        return response()->json($bookings);
    }
}
