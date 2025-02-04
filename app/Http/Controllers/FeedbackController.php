<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\feedback;
use App\Models\guest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::where('status', 1)->with('guest')->latest()->get();
        $totalReviews = $feedbacks->count();
        $averageRating = $totalReviews > 0 ? round($feedbacks->avg('rating'), 1) : 0;

        $ratingCounts = Feedback::selectRaw('rating, COUNT(*) as count')
            ->where('status', 1)
            ->groupBy('rating')
            ->pluck('count', 'rating');

        return view('feedback', [
            'feedbacks' => $feedbacks,
            'totalReviews' => $totalReviews,
            'averageRating' => $averageRating,
            'ratingCounts' => $ratingCounts,
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:255',
        ]);
        $guest = auth()->user()->guest;
        Feedback::create([
            'guest_id' => $guest->guest_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);
        return redirect()->route('feedback.index')->with('success', 'Thank you for your feedback!');
    }
}
