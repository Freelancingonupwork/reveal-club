<?php

namespace App\Jobs;

use App\Models\NutritionIngredient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CacheNutritionSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $search;
    protected string $cacheKey;

    public function __construct(string $search, string $cacheKey)
    {
        $this->search = $search;
        $this->cacheKey = $cacheKey;
    }

    public function handle(): void
    {
        $offset = 0;
        $limit = 2000;
        $allIds = [];

        do {
            $batch = NutritionIngredient::where('name', 'LIKE', "{$this->search}%")
                ->select('id')
                ->offset($offset)
                ->limit($limit)
                ->pluck('id')
                ->toArray();

            $allIds = array_merge($allIds, $batch);
            $offset += $limit;

        } while (count($batch) === $limit && $offset < 50000); // safety cap: 50k max

        if (!empty($allIds)) {
            Cache::put($this->cacheKey, $allIds, 600); // 10 min cache
        }
    }
}
