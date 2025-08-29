<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Analytics;
use App\Models\Business;
use Jenssegers\Agent\Agent;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track specific routes
        if ($this->shouldTrack($request)) {
            $this->trackVisitor($request);
        }

        return $response;
    }

    /**
     * Determine if the request should be tracked
     */
    protected function shouldTrack(Request $request): bool
    {
        $route = $request->route();
        
        if (!$route) {
            return false;
        }

        $routeName = $route->getName();
        
        // Track review page visits and QR code scans
        return $routeName === 'review.show' || 
               $routeName === 'qr.scan' ||
               str_contains($routeName, 'review.');
    }

    /**
     * Track the visitor
     */
    protected function trackVisitor(Request $request): void
    {
        try {
            $route = $request->route();
            $routeName = $route->getName();
            
            // Get business from route parameter
            $business = null;
            if ($request->route('slug')) {
                $business = Business::where('slug', $request->route('slug'))->first();
            } elseif ($request->route('business')) {
                $business = Business::find($request->route('business'));
            }

            if (!$business) {
                return;
            }

            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());

            // Determine event type
            $eventType = $this->getEventType($routeName, $request);

            // Get location data (you might want to use a GeoIP service)
            $locationData = $this->getLocationData($request->ip());

            // Create analytics record
            Analytics::create([
                'business_id' => $business->id,
                'event_type' => $eventType,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $this->getDeviceType($agent),
                'browser' => $agent->browser(),
                'os' => $agent->platform(),
                'country' => $locationData['country'] ?? null,
                'region' => $locationData['region'] ?? null,
                'city' => $locationData['city'] ?? null,
                'latitude' => $locationData['latitude'] ?? null,
                'longitude' => $locationData['longitude'] ?? null,
                'referrer' => $request->headers->get('referer'),
                'additional_data' => [
                    'route' => $routeName,
                    'method' => $request->method(),
                    'is_mobile' => $agent->isMobile(),
                    'is_tablet' => $agent->isTablet(),
                    'is_desktop' => $agent->isDesktop(),
                    'is_robot' => $agent->isRobot(),
                ],
            ]);

            // Increment business scan count for QR scans
            if ($eventType === 'scan') {
                $business->incrementScans();
            }

        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('Failed to track visitor: ' . $e->getMessage());
        }
    }

    /**
     * Get the event type based on route
     */
    protected function getEventType(string $routeName, Request $request): string
    {
        if (str_contains($routeName, 'qr') || $request->has('qr')) {
            return 'scan';
        }

        if ($request->isMethod('POST') && str_contains($routeName, 'review')) {
            return 'review_submit';
        }

        if ($request->has('redirect') || str_contains($routeName, 'redirect')) {
            return 'redirect';
        }

        return 'view';
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
     * Get location data from IP
     * You might want to integrate with a GeoIP service like MaxMind or ipapi
     */
    protected function getLocationData(string $ip): array
    {
        // For now, return empty data
        // In production, integrate with a GeoIP service
        
        // Example with ipapi.co (you would need to make HTTP request)
        /*
        try {
            $response = Http::timeout(5)->get("https://ipapi.co/{$ip}/json/");
            $data = $response->json();
            
            return [
                'country' => $data['country_name'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        } catch (\Exception $e) {
            return [];
        }
        */
        
        return [
            'country' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }
}