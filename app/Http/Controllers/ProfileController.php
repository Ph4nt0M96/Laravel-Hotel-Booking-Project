<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\booking_detail;

use function Ramsey\Uuid\v1;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $bookings = Booking::where('guest_id', $user->guest->guest_id)
            ->with(['bookingDetails.room.roomType', 'bookingDetails.room.view','bookingDetails.extraServices.extraService'])
            ->get();

        return view('profile.edit', [
            'user' => $user,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|in:Mr.,Mrs.',
                'first_name' => 'required|string|max:30',
                'last_name' => 'required|string|max:30',
                'email' => ['required', 'string', 'lowercase', 'email', 'max:30', Rule::unique('users', 'email')->ignore($request->user()->id)],
                'gender' => 'required|string|in:Male,Female,Other',
                'date_of_birth' => 'required|date|before:today',
                'phone_number' => 'required|string',
                'nrc_no' => 'required|string',
            ]);

            $user = $request->user();

            $user->email = $validatedData['email'];
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();

            $guestUpdated = $user->guest()->update([
                'title' => $validatedData['title'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'gender' => $validatedData['gender'],
                'date_of_birth' => $validatedData['date_of_birth'],
                'phone_number' => $validatedData['phone_number'],
                'nrc_no' => $validatedData['nrc_no'],
            ]);

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (\Exception $e) {
            Log::error('Error occurred during profile update:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return Redirect::route('profile.edit')->with('status', 'update-failed');
        }
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
