<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

if (!function_exists('renderCachedView')) {
    function renderCachedView(string $viewName, array $data = [], int $ttl = 3600): string
    {
        $cacheKey = 'cached_view_' . md5($viewName . serialize($data));

        return Cache::remember($cacheKey, $ttl, fn () => View::make($viewName, $data)->render());
    }
}
