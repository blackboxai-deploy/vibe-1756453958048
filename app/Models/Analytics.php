<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'event_type',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'region',
        'city',
        'latitude',
        'longitude',
        'referrer',
        'additional_data',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'additional_data' => 'array',
        ];
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Scopes
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeScans($query)
    {
        return $query->where('event_type', 'scan');
    }

    public function scopeReviewSubmissions($query)
    {
        return $query->where('event_type', 'review_submit');
    }

    public function scopeRedirects($query)
    {
        return $query->where('event_type', 'redirect');
    }

    public function scopeByDevice($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeByBrowser($query, $browser)
    {
        return $query->where('browser', $browser);
    }

    public function scopeByOS($query, $os)
    {
        return $query->where('os', $os);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
    }

    public function scopeLastMonth($query)
    {
        $lastMonth = Carbon::now()->subMonth();
        return $query->whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Helper methods
    public function getLocationAttribute()
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        return implode(', ', $parts);
    }

    public function getDeviceIconAttribute()
    {
        return match(strtolower($this->device_type)) {
            'mobile' => 'fas fa-mobile-alt',
            'tablet' => 'fas fa-tablet-alt',
            'desktop' => 'fas fa-desktop',
            default => 'fas fa-question-circle'
        };
    }

    public function getBrowserIconAttribute()
    {
        $browser = strtolower($this->browser);
        
        if (str_contains($browser, 'chrome')) return 'fab fa-chrome';
        if (str_contains($browser, 'firefox')) return 'fab fa-firefox';
        if (str_contains($browser, 'safari')) return 'fab fa-safari';
        if (str_contains($browser, 'edge')) return 'fab fa-edge';
        if (str_contains($browser, 'opera')) return 'fab fa-opera';
        
        return 'fas fa-globe';
    }

    public function getOSIconAttribute()
    {
        $os = strtolower($this->os);
        
        if (str_contains($os, 'windows')) return 'fab fa-windows';
        if (str_contains($os, 'mac') || str_contains($os, 'ios')) return 'fab fa-apple';
        if (str_contains($os, 'android')) return 'fab fa-android';
        if (str_contains($os, 'linux')) return 'fab fa-linux';
        
        return 'fas fa-question-circle';
    }

    public function getEventTypeColorAttribute()
    {
        return match($this->event_type) {
            'scan' => 'bg-blue-100 text-blue-800',
            'review_submit' => 'bg-green-100 text-green-800',
            'redirect' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getEventTypeIconAttribute()
    {
        return match($this->event_type) {
            'scan' => 'fas fa-qrcode',
            'review_submit' => 'fas fa-star',
            'redirect' => 'fas fa-external-link-alt',
            default => 'fas fa-circle'
        };
    }

    // Static methods for analytics reporting
    public static function getDeviceStats($businessId = null, $days = 30)
    {
        $query = static::recent($days);
        
        if ($businessId) {
            $query->forBusiness($businessId);
        }

        return $query->selectRaw('device_type, COUNT(*) as count')
                    ->groupBy('device_type')
                    ->orderByDesc('count')
                    ->get();
    }

    public static function getBrowserStats($businessId = null, $days = 30)
    {
        $query = static::recent($days);
        
        if ($businessId) {
            $query->forBusiness($businessId);
        }

        return $query->selectRaw('browser, COUNT(*) as count')
                    ->groupBy('browser')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get();
    }

    public static function getOSStats($businessId = null, $days = 30)
    {
        $query = static::recent($days);
        
        if ($businessId) {
            $query->forBusiness($businessId);
        }

        return $query->selectRaw('os, COUNT(*) as count')
                    ->groupBy('os')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get();
    }

    public static function getLocationStats($businessId = null, $days = 30)
    {
        $query = static::recent($days);
        
        if ($businessId) {
            $query->forBusiness($businessId);
        }

        return $query->selectRaw('country, region, city, COUNT(*) as count')
                    ->groupBy('country', 'region', 'city')
                    ->orderByDesc('count')
                    ->limit(20)
                    ->get();
    }

    public static function getEventTypeStats($businessId = null, $days = 30)
    {
        $query = static::recent($days);
        
        if ($businessId) {
            $query->forBusiness($businessId);
        }

        return $query->selectRaw('event_type, COUNT(*) as count')
                    ->groupBy('event_type')
                    ->orderByDesc('count')
                    ->get();
    }

    public static function getDailyStats($businessId = null, $days = 30)
    {
        $query = static::recent($days);
        
        if ($businessId) {
            $query->forBusiness($businessId);
        }

        return $query->selectRaw('DATE(created_at) as date, event_type, COUNT(*) as count')
                    ->groupBy('date', 'event_type')
                    ->orderBy('date', 'desc')
                    ->get();
    }

    public static function getHourlyStats($businessId = null, $date = null)
    {
        $query = static::query();
        
        if ($businessId) {
            $query->forBusiness($businessId);
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        } else {
            $query->whereDate('created_at', Carbon::today());
        }

        return $query->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get();
    }
}