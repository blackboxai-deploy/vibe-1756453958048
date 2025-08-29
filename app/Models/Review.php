<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'rating',
        'review_text',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'location',
        'is_approved',
        'is_featured',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getCustomerInitialsAttribute()
    {
        if ($this->customer_name) {
            $names = explode(' ', $this->customer_name);
            return strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
        }
        
        return 'AN'; // Anonymous
    }

    public function getCustomerAvatarAttribute()
    {
        return "https://placehold.co/100x100?text=" . urlencode($this->customer_initials);
    }

    public function getStarRatingHtmlAttribute()
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $html .= '<i class="fas fa-star text-yellow-400"></i>';
            } else {
                $html .= '<i class="far fa-star text-gray-300"></i>';
            }
        }
        return $html;
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M d, Y');
    }

    public function getTruncatedReviewAttribute($length = 100)
    {
        return strlen($this->review_text) > $length 
            ? substr($this->review_text, 0, $length) . '...' 
            : $this->review_text;
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeHighRating($query)
    {
        return $query->whereIn('rating', [4, 5]);
    }

    public function scopeLowRating($query)
    {
        return $query->whereIn('rating', [1, 2, 3]);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeByDevice($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', '%' . $location . '%');
    }

    // Helper methods
    public function approve($approvedBy = null)
    {
        $this->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);
    }

    public function disapprove()
    {
        $this->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function toggleFeatured()
    {
        $this->update(['is_featured' => !$this->is_featured]);
    }

    public function isHighRating()
    {
        return in_array($this->rating, [4, 5]);
    }

    public function isLowRating()
    {
        return in_array($this->rating, [1, 2, 3]);
    }

    public function getRatingColorClass()
    {
        return match($this->rating) {
            5 => 'text-green-500',
            4 => 'text-green-400',
            3 => 'text-yellow-500',
            2 => 'text-orange-500',
            1 => 'text-red-500',
            default => 'text-gray-400'
        };
    }

    public function getRatingBgClass()
    {
        return match($this->rating) {
            5 => 'bg-green-100 text-green-800',
            4 => 'bg-green-100 text-green-700',
            3 => 'bg-yellow-100 text-yellow-800',
            2 => 'bg-orange-100 text-orange-800',
            1 => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function canBeDeleted()
    {
        // Reviews can be deleted if they are not approved or if they are low rating
        return !$this->is_approved || $this->isLowRating();
    }

    // Static methods
    public static function getAverageRatingForBusiness($businessId)
    {
        return static::approved()
            ->forBusiness($businessId)
            ->avg('rating') ?? 0;
    }

    public static function getReviewCountByRating($businessId)
    {
        $counts = [];
        for ($i = 1; $i <= 5; $i++) {
            $counts[$i] = static::approved()
                ->forBusiness($businessId)
                ->byRating($i)
                ->count();
        }
        return $counts;
    }
}