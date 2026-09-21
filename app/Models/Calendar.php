<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'user_id',
        'is_base',
        'parent_id',
        'school_year_id',
        'start_date',
        'end_date',
        'feed_token',
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getFeedTokenAttribute($value)
    {
        if (empty($value)) {
            $value = \Illuminate\Support\Str::random(32);
            $this->attributes['feed_token'] = $value;
            $this->save();
        }
        return $value;
    }

    public function getFeedUrlAttribute(): string
    {
        return route('calendar.feed', ['token' => $this->feed_token]);
    }
    /**
     * Accessor to decode HTML entities in the name attribute.
     */
    public function getNameAttribute($value)
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function parent()
    {
        return $this->belongsTo(Calendar::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Calendar::class, 'parent_id');
    }

    /**
     * Get all events: own events + inherited base events (if this is a derived calendar)
     */
    public function allEvents()
    {
        $events = $this->events()->get();

        if ($this->parent_id) {
            $parentEvents = CalendarEvent::where('calendar_id', $this->parent_id)->get();
            // Merge: own events override parent events on the same date
            $events = $events->merge($parentEvents->filter(function ($pe) use ($events) {
                return !$events->contains(function ($e) use ($pe) {
                    return $e->start_date == $pe->start_date && $e->title === $pe->title;
                });
            }));
        }

        return $events;
    }

    public static function base()
    {
        return static::where('is_base', true)->first();
    }
}
