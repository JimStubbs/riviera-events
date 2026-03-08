<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model
{
    use HasSlug;

    protected $fillable = [
        'user_id', 'location_id', 'category_id', 'title', 'slug',
        'description', 'start_date', 'end_date', 'image', 'organizer',
        'website', 'is_all_day', 'is_premium', 'is_featured', 'featured_order', 'status',
        'is_paid', 'stripe_payment_id', 'views_count', 'submitter_email',
        'verification_token', 'verified_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'verified_at' => 'datetime',
            'is_all_day' => 'boolean',
            'is_premium' => 'boolean',
            'is_featured' => 'boolean',
            'is_paid' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function featuredEvent(): HasOne
    {
        return $this->hasOne(FeaturedEvent::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(EventView::class);
    }

    // Scopes

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('end_date', '>=', now());
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    // Accessors

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function getGoogleCalendarUrlAttribute(): string
    {
        $end = $this->end_date ?? $this->start_date;

        $params = http_build_query([
            'action'   => 'TEMPLATE',
            'text'     => $this->title,
            'dates'    => $this->start_date->format('Ymd\THis\Z') . '/' . $end->format('Ymd\THis\Z'),
            'details'  => strip_tags($this->description ?? ''),
            'location' => $this->location?->city,
        ]);

        return 'https://calendar.google.com/calendar/render?' . $params;
    }
}
