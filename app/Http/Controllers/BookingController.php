<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\room_type;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\view;
use App\Models\booking_detail;
use App\Models\extra_service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\booking_extraservice;

class BookingController extends Controller
{
    public function createBooking()
    {
        $cart = session()->get('cart', []);
        session()->forget('cart');

        $guest = Auth::user()->guest;
        $extraServices = Extra_Service::all();

        // Create a new array to handle multiple booking forms
        $bookingData = [];
        foreach ($cart as $item) {
            foreach ($item['room_ids'] as $room_id) {
                $bookingData[] = [
                    'room_type' => $item['room_type'],
                    'view_name' => $item['view_name'],
                    'room_type_id' => $item['room_type_id'],
                    'view_id' => $item['view_id'],
                    'room_id' => $room_id,
                ];
            }
        }

        return view('booking', [
            'bookingData' => $bookingData,
            'guest' => $guest,
            'extraServices' => $extraServices,
        ]);
    }
    public function storeMultipleBookings(Request $request)
    {
        Log::info('Store Multiple Bookings Raw Request Data:', $request->all());

        $validatedData = $request->validate([
            'room_type_id' => 'required|array',
            'view_id' => 'required|array',
            'room_id' => 'required|array',
            'check_in_date' => 'required|array',
            'check_out_date' => 'required|array',
            'extra_services' => 'nullable|array',
            'extra_services.*' => 'nullable|array',
            'confirm_until' => 'required|date',
        ]);
        Log::info('Validated Data:', $validatedData);

        $guest = Auth::user()->guest;

        $booking = Booking::create([
            'guest_id' => $guest->guest_id,
            'booking_date' => now(),
            'booking_status' => 'Pending',
            'confirm_until' => $validatedData['confirm_until'],
        ]);

        foreach ($validatedData['room_id'] as $index => $room_id) {
            $room = Room::findOrFail($room_id);

            $room->update(['is_available' => 1, 'is_held' => 0]);

            $basePrice = $room->roomType->base_price ?? 0;
            $extraServiceCost = 0;

            $bookingDetail = Booking_Detail::create([
                'booking_id' => $booking->booking_id,
                'room_id' => $room_id,
                'check_in_date' => $validatedData['check_in_date'][$index],
                'check_out_date' => $validatedData['check_out_date'][$index],
                'total_cost' => $basePrice,
            ]);

            if (!empty($validatedData['extra_services'][$index])) {
                foreach ($validatedData['extra_services'][$index] as $serviceId) {
                    $extraService = Extra_Service::find($serviceId);

                    if ($extraService) {
                        $extraServiceCost += $extraService->service_price;

                        Booking_ExtraService::create([
                            'detail_id' => $bookingDetail->detail_id,
                            'service_id' => $serviceId,
                            'quantity' => 1,
                        ]);
                    }
                }
            }

            $bookingDetail->update(['total_cost' => $basePrice + $extraServiceCost]);
        }

        return redirect()->back()->with('showModal', 'Your bookings have been successfully confirmed!');
    }

    public function confirmBooking(Request $request)
    {
        $bookingData = json_decode($request->input('bookingData'), true);

        $validatedData = $request->validate([
            'check_in_date' => 'required|array',
            'check_out_date' => 'required|array',
            'extra_services' => 'nullable|array',
            'extra_services.*' => 'nullable|array',
        ]);

        foreach ($bookingData as $index => &$data) {
            $data['check_in_date'] = $validatedData['check_in_date'][$index] ?? null;
            $data['check_out_date'] = $validatedData['check_out_date'][$index] ?? null;
            $data['extra_services'] = $validatedData['extra_services'][$index] ?? [];

            // Calculate total cost
            $roomType = Room_Type::find($data['room_type_id']);
            $basePrice = $roomType->base_price ?? 0;

            $extraServiceCost = 0;
            foreach ($data['extra_services'] as $serviceId) {
                $extraService = Extra_Service::find($serviceId);
                $extraServiceCost += $extraService->service_price ?? 0;
            }

            $data['total_cost'] = $basePrice + $extraServiceCost; // Total cost for this booking detail
        }

        $earliestCheckInDate = collect($validatedData['check_in_date'])->min();

        $earliestCheckIn = \Carbon\Carbon::parse($earliestCheckInDate);
        if ($earliestCheckIn->isToday()) {
            $confirmUntil = $earliestCheckIn->toDateString();
        } elseif ($earliestCheckIn->isTomorrow()) {
            $confirmUntil = $earliestCheckIn->toDateString();
        } elseif ($earliestCheckIn->diffInDays(now()) < 10) {
            $confirmUntil = $earliestCheckIn->subDays(2)->toDateString();
        } elseif ($earliestCheckIn->diffInDays(now()) <= 20) {
            $confirmUntil = $earliestCheckIn->subDays(7)->toDateString();
        } elseif ($earliestCheckIn->diffInDays(now()) <= 25) {
            $confirmUntil = $earliestCheckIn->subDays(10)->toDateString();
        }

        $totalPrice = collect($bookingData)->sum('total_cost');

        return view('booking-confirm', [
            'guest' => Auth::user()->guest,
            'bookingData' => $bookingData,
            'totalPrice' => $totalPrice,
            'confirmUntil' => $confirmUntil,
        ]);
    }

    public function cancelBooking(Request $request)
    {
        // Decode the room_ids sent in the query string
        $roomIds = json_decode($request->input('room_ids'), true);

        if (is_array($roomIds)) {
            foreach ($roomIds as $roomId) {
                $room = Room::findOrFail($roomId);
                $room->update(['is_held' => 0]);
            }

            return redirect()->route('room.index')->with('success', 'Booking has been canceled successfully!');
        }

        return redirect()->route('room.index')->with('error', 'Failed to cancel booking. Please try again.');
    }
    public function cancelProfileBooking(Request $request, Booking $booking)
    {
        if ($booking->booking_status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending bookings can be canceled.');
        }
        $booking->update(['booking_status' => 'Canceled']);

        $bookingDetails = $booking->bookingDetails;
        foreach ($bookingDetails as $detail) {
            $room = Room::find($detail->room_id);
            if ($room) {
                $room->update(['is_available' => 0]);
            }
        }

        return redirect()->route('profile.edit', ['activeTab' => 'booking-history']);
    }
}
