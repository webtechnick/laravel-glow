<?php

namespace WebTechNick\LaravelGlow;

use \Illuminate\Support\ServiceProvider as LaravelProvider;

class ServiceProvider extends LaravelProvider
{
    public function register()
    {
        //
    }

    /**
     * Add views to list
     * @return [type] [description]
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'glow');
    }
}