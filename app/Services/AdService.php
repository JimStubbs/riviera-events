<?php

namespace App\Services;

use App\Models\Ad;
use Illuminate\Support\Facades\Cache;

class AdService
{
    /**
     * Retrieve the highest-priority active ad for a given placement.
     * Results are cached 10 minutes per placement + location combo.
     */
    public function getAdsForPlacement(string $placement, ?int $locationId = null): ?Ad
    {
        $cacheKey = "ads_{$placement}_{$locationId}";

        return Cache::tags(['ads'])->remember($cacheKey, now()->addMinutes(10), function () use ($placement, $locationId) {
            return Ad::active()
                ->forPlacement($placement)
                ->when($locationId, fn ($q) => $q->forLocation($locationId))
                ->orderBy('order')
                ->first();
        });
    }

    /**
     * Inject native ad entries into a flat array of event items.
     * Every $frequency events, a native ad item is inserted if one exists.
     *
     * Returns a plain array mixing event objects/arrays and ad entries like:
     * ['_type' => 'ad', 'ad' => Ad $model]
     */
    public function injectNativeAds(iterable $events, int $frequency = 5): array
    {
        $ad = $this->getAdsForPlacement('native');

        $items  = is_array($events) ? $events : iterator_to_array($events, false);
        $result = [];

        foreach ($items as $index => $event) {
            $result[] = $event;

            if ($ad && ($index + 1) % $frequency === 0) {
                $result[] = ['_type' => 'ad', 'ad' => $ad];
            }
        }

        return $result;
    }
}
