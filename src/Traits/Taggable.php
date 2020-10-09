<?php
namespace WebTechNick\LaravelGlow\Traits;

use WebTechNick\LaravelGlow\Events\TagAttached;
use WebTechNick\LaravelGlow\Models\Tag;

trait Taggable
{
    /**
     * A post has many tags
     * @return morphTo
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withPivot('taggable_id','taggable_type');
    }

    /**
     * get a tagList of tags
     * @return [type] [description]
     */
    public function getTagListAttribute()
    {
        return $this->tags()->pluck('tag_id')->toArray();
    }

    /**
     * Add a tag
     * @param Tag $tag [description]
     */
    public function addTag(Tag $tag)
    {
        return $this->tags()->attach($tag);
    }

    /**
     * Remove a tag
     * @param  Tag    $tag [description]
     * @return [type]      [description]
     */
    public function removeTag(Tag $tag)
    {
        return $this->tags()->detach($tag);
    }

    /**
     * Find or create tag based on csv list
     * @param  [type] $mixed [description]
     * @return [type]        [description]
     */
    public function syncTagString($string)
    {
        // Do nothing if we're not concrete
        if (!$this->id) {
            return $this;
        }

        $current_tags = $this->tags()->pluck('slug'); // Find current tags as array

        $this->tags()->detach(); // clear tags, prepping for adding.

        $string = trim($string);
        if (empty($string)) {
            return $this;
        }

        // Go through tags to safe,
        $tags = explode(',', $string);
        foreach($tags as $name) {
            $name = ucwords(trim($name));

            // Make sure not to save empty or unsluggable tag names
            if (empty($name) || !Tag::canSlug($name)) {
                continue;
            }

            // Find or create the attaching tag
            $tag = Tag::findOrCreateByName($name);

            // Attach the tag
            $this->tags()->attach($tag);
        }

        return $this;
    }

    /**
     * Set the tagString will associate tags to the item, creating what we need
     * @param [type] $value [description]
     */
    public function setTagStringAttribute($value)
    {
        return $this->syncTagString($value);
    }

    /**
     * tagString as CSV
     * @return [type] [description]
     */
    public function getTagStringAttribute()
    {
        return $this->tags()->pluck('name')->implode(',');
    }

    /**
     * TagString as slugs for use with byInputTags
     * @return [type] [description]
     */
    public function getTagSlugStringAttribute()
    {
        return $this->tags()->pluck('slug')->implode(',');
    }

    /**
     * Determine if we have the tag
     * @param  Tag    $tag [description]
     * @return [type]      [description]
     */
    public function hasTag($tag)
    {
        if ($tag instanceof Tag) {
            $tag = $tag->slug;
        }
        return $this->tags()->byName($tag)->exists();
    }

    /**
     * Takes in a tags CSV string of slugs
     * @param  [type] $query [description]
     * @param  [type] $tags  [description]
     * @return [type]        [description]
     */
    public function scopeByInputTags($query, $tagstring = null, $matchAll = false)
    {
        $tagstring = trim($tagstring);
        $tagstring = str_replace(' ', '', $tagstring);
        $tags = explode(',', $tagstring);
        if (count($tags)) {
            return $query->byTags($tags, $matchAll);
        }
        return $query;
    }

    /**
     * Scope find items by tags, takes in an array of tag slugs
     * as well as a boolean matchAll (default false)
     * @param  [type]  $query    passed in automatically
     * @param  array   $tags     [description]
     * @param  boolean $matchAll [if true, will restrict to items with all tags attached to it]
     * @return [type]            [description]
     */
    public function scopeByTags($query, $tags = [], $matchAll = false)
    {
        if (!$matchAll) {
            // Find all with any tag given
            $query->whereHas('tags', function($query) use($tags) {
                $query->whereIn('slug', $tags);
            });
        } else {
            // Find all with exactly tags given
            $query->whereHas('tags', function($query) use($tags) {
                $query->whereIn('slug', $tags);
            }, '=', count($tags));
        }
        return $query;
    }
}
