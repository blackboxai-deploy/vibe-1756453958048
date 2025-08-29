<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReviewPlatform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relationships
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_platforms')
            ->withPivot(['business_link', 'review_link', 'additional_data', 'is_active'])
            ->withTimestamps();
    }

    public function businessPlatforms()
    {
        return $this->hasMany(BusinessPlatform::class);
    }

    // Accessors
    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return Storage::url('platform-icons/' . $this->icon);
        }
        
        return "https://placehold.co/50x50?text=" . urlencode(substr($this->name, 0, 2));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    // Helper methods
    public function isGoogle()
    {
        return strtolower($this->name) === 'google';
    }

    public function isFacebook()
    {
        return strtolower($this->name) === 'facebook';
    }

    public function isTrustPilot()
    {
        return strtolower($this->name) === 'trustpilot';
    }

    public function isZomato()
    {
        return strtolower($this->name) === 'zomato';
    }

    public function isSwiggy()
    {
        return strtolower($this->name) === 'swiggy';
    }

    public function getDefaultFields()
    {
        $fields = [
            'business_link' => [
                'label' => 'Business Profile Link',
                'type' => 'url',
                'required' => true,
                'placeholder' => 'https://example.com/business-profile'
            ],
            'review_link' => [
                'label' => 'Review Link',
                'type' => 'url',
                'required' => true,
                'placeholder' => 'https://example.com/leave-review'
            ]
        ];

        // Platform-specific fields
        switch (strtolower($this->name)) {
            case 'google':
                $fields['business_link']['placeholder'] = 'https://maps.google.com/place/...';
                $fields['review_link']['placeholder'] = 'https://search.google.com/local/writereview?...';
                break;
                
            case 'facebook':
                $fields['business_link']['placeholder'] = 'https://facebook.com/your-page';
                $fields['review_link']['placeholder'] = 'https://facebook.com/your-page/reviews';
                break;
                
            case 'trustpilot':
                $fields['business_link']['placeholder'] = 'https://trustpilot.com/review/your-domain.com';
                $fields['review_link']['placeholder'] = 'https://trustpilot.com/evaluate/your-domain.com';
                break;
                
            case 'zomato':
                $fields['business_link']['placeholder'] = 'https://zomato.com/restaurant/...';
                $fields['review_link']['placeholder'] = 'https://zomato.com/restaurant/.../reviews';
                break;
                
            case 'swiggy':
                $fields['business_link']['placeholder'] = 'https://swiggy.com/restaurants/...';
                $fields['review_link']['placeholder'] = 'https://swiggy.com/restaurants/.../reviews';
                break;
        }

        return $fields;
    }
}