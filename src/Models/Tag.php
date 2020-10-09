<?php

namespace App;

use App\Traits\Models\Filterable;
use App\Traits\Models\FindOrCreateByNameable;
use App\Traits\Models\Followable;
use App\Traits\Models\ToggleActivatable;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Tag extends Model
{
    use Sluggable,
        Filterable,
        FindOrCreateByNameable,
        Followable,
        ToggleActivatable;

    public $fillable = [
        'name',
        'description',
    ];

    /**
     * Configuration for sluggable
     * @return [type] [description]
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function getFilters()
    {
        return [
            'name', 'slug'
        ];
    }

    /**
     * A tag is attached to many posts
     * @return [type] [description]
     */
    public function blogs()
    {
        return $this->morphedByMany(Blog::class, 'taggable');
    }

    /**
     * Get the routeKey for tag
     * @return [type] [description]
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Link to the tag view
     *
     * @return [type] [description]
     */
    public function link()
    {
        return route('tags.show', [$this->slug]);
    }

    /**
     * Can we slug the name?
     *
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    static public function canSlug($name)
    {
        return SlugService::createSlug(self::class, 'slug', $name, ['unique' => false]);
    }

    /**
     * Follow tags from request
     *
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    static public function followFromRequest($name)
    {
        if (!Auth::check()) {
            return;
        }

        $retval = collect([]);

        $tags = explode(',', $name);
        foreach ($tags as $name) {
            $name = ucwords(trim($name));
            // Make sure not to save empty or unsluggable tag names
            if (empty($name) || !self::canSlug($name)) {
                continue;
            }

            // Find or create tag
            $tag = self::findOrCreateByName($name);

            // Follow tag
            Auth::user()->follow($tag);

            // Append to return list
            $retval->push($tag);
        }

        return $retval;
    }

    /**
     * Find popular by type or in general.
     * @param  [type] $query [description]
     * @param  [type] $type  types of tagables.
     * @return [type]        [description]
     */
    public function scopePopular($query, $type = null)
    {
        if ($type) {
            // Specific type events, users, etc..
            return $query->withCount($type)
                         ->groupBy('id')
                         ->orderBy($type . '_count', 'desc');
        }
        // Default will return popular tags across entire site.
        return $query->select(DB::raw('count(taggables.tag_id) as tag_count, tags.*'))
                     ->join('taggables', 'taggables.tag_id', '=', 'tags.id')
                     ->groupBy('tags.id')
                     ->orderBy('tag_count', 'desc');
    }
}
