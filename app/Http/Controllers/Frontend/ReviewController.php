<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Review;
use App\Models\Setting;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;

class ReviewController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Show the review page for a business
     */
    public function show(Request $request, $slug)
    {
        $business = Business::with(['platforms', 'user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Check if this is a QR scan
        $isQrScan = $request->has('qr') || $request->has('scan');

        // Get settings
        $settings = Setting::getPublicSettings();

        // Get recent approved reviews for display
        $recentReviews = $business->reviews()
            ->approved()
            ->with([])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate review statistics
        $reviewStats = [
            'average_rating' => $business->average_rating,
            'total_reviews' => $business->total_reviews,
            'star_distribution' => $business->star_distribution,
        ];

        return view('frontend.review', compact(
            'business',
            'isQrScan',
            'settings',
            'recentReviews',
            'reviewStats'
        ));
    }

    /**
     * Store a new review
     */
    public function store(Request $request, $slug)
    {
        $business = Business::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:' . Setting::get('min_review_length', 10) . '|max:' . Setting::get('max_review_length', 500),
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        // Get location data (you might want to use a GeoIP service)
        $locationData = $this->getLocationData($request->ip());

        $reviewData = [
            'business_id' => $business->id,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($agent),
            'browser' => $agent->browser(),
            'os' => $agent->platform(),
            'location' => $locationData['city'] ?? null,
            'is_approved' => Setting::get('auto_approve_reviews', false),
        ];

        $review = Review::create($reviewData);

        // Determine next action based on rating
        $response = [
            'success' => true,
            'review_id' => $review->id,
            'rating' => $review->rating,
        ];

        if ($review->rating >= 4) {
            // High rating (4-5 stars) - redirect to Google Reviews
            $googlePlatform = $business->getGooglePlatform();
            
            if ($googlePlatform && $googlePlatform->pivot->review_link) {
                $response['action'] = 'redirect';
                $response['redirect_url'] = $googlePlatform->pivot->review_link;
                $response['redirect_delay'] = Setting::get('redirect_delay', 3);
                $response['message'] = 'Thank you for your positive review! You will be redirected to leave a review on Google.';
            } else {
                $response['action'] = 'thank_you';
                $response['message'] = 'Thank you for your positive review!';
            }
        } else {
            // Low rating (1-3 stars) - save internally and thank
            $response['action'] = 'thank_you';
            $response['message'] = 'Thank you for your feedback. We appreciate your input and will work to improve.';
        }

        return response()->json($response);
    }

    /**
     * Get AI-powered review suggestions
     */
    public function getSuggestions(Request $request, $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        if (!Setting::get('review_suggestions_enabled', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Review suggestions are currently disabled.',
            ]);
        }

        $rating = $request->get('rating', 5);
        $keywords = $request->get('keywords', []);
        
        if (is_string($keywords)) {
            $keywords = explode(',', $keywords);
        }
        
        $keywords = array_filter(array_map('trim', $keywords));

        try {
            $suggestions = $this->openAIService->generateReviewSuggestions(
                $business,
                $rating,
                $keywords
            );

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to generate suggestions at the moment.',
            ], 500);
        }
    }

    /**
     * Redirect to external review platform
     */
    public function redirect(Request $request, $slug, $platform = 'google')
    {
        $business = Business::with('platforms')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $platformData = $business->platforms()
            ->where('name', ucfirst($platform))
            ->first();

        if (!$platformData || !$platformData->pivot->review_link) {
            abort(404, 'Review platform not found or not configured.');
        }

        // Track the redirect
        if (class_exists('App\Models\Analytics')) {
            \App\Models\Analytics::create([
                'business_id' => $business->id,
                'event_type' => 'redirect',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'additional_data' => [
                    'platform' => $platform,
                    'redirect_url' => $platformData->pivot->review_link,
                ],
            ]);
        }

        return redirect($platformData->pivot->review_link);
    }

    /**
     * Get device type from agent
     */
    protected function getDeviceType(Agent $agent): string
    {
        if ($agent->isMobile()) {
            return 'mobile';
        }

        if ($agent->isTablet()) {
            return 'tablet';
        }

        if ($agent->isDesktop()) {
            return 'desktop';
        }

        return 'unknown';
    }

    /**
     * Get location data from IP (placeholder implementation)
     */
    protected function getLocationData(string $ip): array
    {
        // In production, integrate with a GeoIP service
        return [
            'country' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }
}