<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favoritedByUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['keyword']}%")
                    ->orWhere('author', 'like', "%{$filters['keyword']}%");
            });
        }

        if (! empty($filters['genre'])) {
            $query->whereHas('genres', function ($q) use ($filters) {
                $q->where('genres.id', $filters['genre']);
            });
        }

        if (! empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'title':
                    $query->orderBy('title', 'asc');
                    break;
                
                case 'rating':
                    $query->orderBy('reviews_avg_rating', 'desc')
                        ->orderBy('created_at', 'desc');
                    break;
            }
        }

        return $query;
    }

    public function scopeApiFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['keyword']}%")
                    ->orWhere('author', 'like', "%{$filters['keyword']}%");
            });
        }

        if (! empty($filters['genres'])) {
            $query->whereHas('genres', function ($q) use ($filters) {
                $q->whereIn('genres.id', $filters['genres']);
            });
        }

        if (! empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'latest':
                    $query->orderBy('created_at', 'desc');
                    break;
                
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'title':
                    $query->orderBy('title', 'asc');
                    break;
                
                case 'rating':
                    $query->orderBy('reviews_avg_rating', 'desc')
                        ->orderBy('created_at', 'desc');
                    break;
            }
        }

        return $query;
    }
}
