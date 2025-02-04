<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\admin;
use App\Models\guest;
use App\Models\room;
use App\Models\booking_detail;
use Illuminate\Support\Facades\Auth;

class BookingManagementController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->input('search');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $query = Booking::where('booking_status', 'Pending')->with(['guest', 'bookingDetails']);

        // Apply filters
        if ($searchTerm) {
            $query->whereHas('guest', function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchTerm . '%']);
            })->orWhere('booking_id', 'like', '%' . $searchTerm . '%');
        }
        if ($startDate && $endDate) {
            $query->whereRaw("DATE(booking_date) BETWEEN ? AND ?", [$startDate, $endDate]);
        }

        $pendingBookings = $query->paginate(10);

        // Calculate total cost for each booking
        $pendingBookings->getCollection()->transform(function ($booking) {
            $booking->total_cost = $booking->bookingDetails->sum('total_cost');
            return $booking;
        });

        return view('admin.booking_management', compact('pendingBookings', 'searchTerm', 'startDate', 'endDate'));
    }


    public function todayBookings()
    {
        $today = now()->toDateString();

        $pendingBookings = Booking::whereDate('confirm_until', $today)
            ->where('booking_status', 'Pending')
            ->with(['guest', 'bookingDetails'])
            ->paginate(10);

        $pendingBookings->getCollection()->transform(function ($booking) {
            $booking->total_cost = $booking->bookingDetails->sum('total_cost');
            return $booking;
        });

        return view('admin.booking_management', compact('pendingBookings'));
    }
    public function approve($bookingId)
    {
        $adminId = Auth::user()->admin->admin_id;
        $booking = Booking::findOrFail($bookingId);
        $booking->booking_status = 'Approved';
        $booking->admin_id = $adminId;
        $booking->save();

        return redirect()->back()->with('success', 'Booking approved successfully!');
    }

    public function decline($id)
    {
        $adminId = Auth::user()->admin->admin_id;
        $booking = Booking::findOrFail($id);
        $booking->booking_status = 'Declined';
        $booking->admin_id = $adminId;
        $booking->save();

        $bookingDetails = $booking->bookingDetails;
        foreach ($bookingDetails as $detail) {
            $room = Room::find($detail->room_id);
            if ($room) {
                $room->update(['is_available' => 0]);
            }
        }

        return redirect()->back();
    }

    public function histories()
    {
        $allBookings = Booking::with('guest')
            ->where('booking_status', '!=', 'Pending')
            ->paginate(10);

        return view('admin.booking_histories', compact('allBookings'));
    }

    public function filterHistories(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');

        $query = Booking::with('guest')
            ->where('booking_status', '!=', 'Pending');

        // Apply date filters if provided
        if ($startDate && $endDate) {
            $query->whereRaw("DATE(booking_date) BETWEEN ? AND ?", [$startDate, $endDate]);
        }

        // Apply status filter if provided
        if ($status) {
            $query->where('booking_status', $status);
        }

        $allBookings = $query->paginate(10);

        return view('admin.booking_histories', compact('allBookings', 'startDate', 'endDate', 'status'));
    }
}
