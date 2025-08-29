<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'logo',
        'full_name',
        'business_name',
        'address',
        'state',
        'city',
        'area',
        'pincode',
        'mobile_number',
        'telephone_number',
        'email',
        'website',
        'description',
        'category',
        'business_hours',
        'latitude',
        'longitude',
        'qr_code_path',
        'slug',
        'is_active',
        'total_scans',
    ];

    protected function casts(): array
    {
        return [
            'business_hours' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
            'total_scans' => 'integer',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platforms()
    {
        return $this->belongsToMany(ReviewPlatform::class, 'business_platforms')
            ->withPivot(['business_link', 'review_link', 'additional_data', 'is_active'])
            ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function analytics()
    {
        return $this->hasMany(Analytics::class);
    }

    public function businessPlatforms()
    {
        return $this->hasMany(BusinessPlatform::class);
    }

    // Accessors
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return Storage::url($this->logo);
        }
        
        return "https://placehold.co/200x200?text=" . urlencode(substr($this->business_name, 0, 2));
    }

    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path) {
            return Storage::url($this->qr_code_path);
        }
        
        return null;
    }

    public function getReviewPageUrlAttribute()
    {
        return route('review.show', $this->slug);
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->area,
            $this->city,
            $this->state,
            $this->pincode
        ]);
        
        return implode(', ', $parts);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()
            ->where('is_approved', true)
            ->avg('rating') ?? 0;
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()
            ->where('is_approved', true)
            ->count();
    }

    public function getStarDistributionAttribute()
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $this->reviews()
                ->where('is_approved', true)
                ->where('rating', $i)
                ->count();
        }
        
        return $distribution;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function getGooglePlatform()
    {
        return $this->platforms()
            ->where('name', 'Google')
            ->first();
    }

    public function getGoogleReviewLink()
    {
        $googlePlatform = $this->getGooglePlatform();
        
        if ($googlePlatform) {
            return $googlePlatform->pivot->review_link;
        }
        
        return null;
    }

    public function incrementScans()
    {
        $this->increment('total_scans');
    }

    public function generateQrCode()
    {
        if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            return null;
        }

        $qrPath = 'qr-codes/' . $this->slug . '.png';
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(300)
            ->generate($this->review_page_url);

        Storage::disk('public')->put($qrPath, $qrCode);
        
        $this->update(['qr_code_path' => $qrPath]);
        
        return $this->qr_code_url;
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($business) {
            if (empty($business->slug)) {
                $business->slug = Str::slug($business->business_name . '-' . time());
            }
        });

        static::updating(function ($business) {
            if ($business->isDirty('business_name') && empty($business->getOriginal('slug'))) {
                $business->slug = Str::slug($business->business_name . '-' . time());
            }
        });
    }
}