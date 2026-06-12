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

        if (! empty($filters['genres'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['genres'] as $genre) {
                    $q->orWhereHas('genres', fn($sub) => $sub->where('name', $genre));
                }
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
                    $query->orderByRaw('title COLLATE utf8mb4_ja_0900_as_cs ASC');
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
