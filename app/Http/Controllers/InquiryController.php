<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\inquiry;

class InquiryController extends Controller
{
    public function create()
    {
        return view('inquiry');
    }
    public function index()
    {
        $inquiry_es = inquiry::paginate(10);
        return view('admin.inquiry', compact('inquiry_es'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);
        if (Auth::check()) {
            $guest_id = Auth::user()->guest->guest_id;
        } else {
            return redirect()->route('login')->with('error', 'Please log in to send an inquiry.');
        }
        DB::table('inquiry')->insert([
            'guest_id' => $guest_id,
            'message' => $request->message,
            'sentdate' => now(),
        ]);
        return redirect()->back()->with('success', 'Your inquiry has been sent successfully!');
    }
    public function destroy($id)
    {
        inquiry::findOrFail($id)->delete();

        return redirect()->route('admin.inquiry.index')->with('success', 'Room Type deleted successfully!');
    }
}
