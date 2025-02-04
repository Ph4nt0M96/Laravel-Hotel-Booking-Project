<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with(['guest.user'])->get();
        return view('admin.feedback', compact('feedbacks'));
    }

    public function updateAll(Request $request)
    {
        $statuses = $request->input('statuses', []);

        foreach ($statuses as $id => $status) {
            // Update the status for each feedback using its ID
            Feedback::where('feedback_id', $id)->update(['status' => (int)$status]);
        }

        return redirect()->back()->with('success', 'All feedback statuses updated successfully.');
    }
}
