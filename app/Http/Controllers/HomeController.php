<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Review;
use App\Models\Plan;

class HomeController extends Controller
{
    /**
     * Show the application homepage
     */
    public function index()
    {
        // Get some statistics for the homepage
        $stats = [
            'total_businesses' => Business::count(),
            'total_reviews' => Review::approved()->count(),
            'average_rating' => Review::approved()->avg('rating') ?? 0,
            'active_plans' => Plan::active()->count(),
        ];

        // Get featured plans
        $plans = Plan::active()->ordered()->take(3)->get();

        // Get recent reviews for testimonials
        $recentReviews = Review::with('business')
            ->approved()
            ->highRating()
            ->latest()
            ->take(6)
            ->get();

        return view('home', compact('stats', 'plans', 'recentReviews'));
    }
}