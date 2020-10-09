<?php

namespace WebTechNick\LaravelGlow\Traits;

use Illuminate\Support\Facades\Schema;

trait FindOrCreateByNameable
{
    /**
     * Find or create a new self by name
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function findOrCreateByName($name)
    {
        $retval = self::byName($name)->first();
        if (!$retval) {
            $retval = self::create(['name' => $name]);
        }
        return $retval;
    }

    /**
     * Find a self by name or slug
     * @param  [type] $query [description]
     * @param  [type] $name  [description]
     * @return [type]        [description]
     */
    public function scopeByName($query, $name)
    {
        $query->where('name', $name);
        if (Schema::hasColumn($this->getTable(), 'slug')) {
            $query->orWhere('slug', $name);
        }

        return $query;
    }
}
