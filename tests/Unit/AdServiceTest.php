<?php

namespace Tests\Unit;

use App\Models\Ad;
use App\Models\Location;
use App\Services\AdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdService();
    }

    public function test_get_ads_for_placement_returns_active_ad(): void
    {
        $ad = Ad::create([
            'title'     => 'Sidebar Banner',
            'image'     => 'ads/test.jpg',
            'url'       => 'https://example.com',
            'placement' => 'sidebar',
            'active'    => true,
        ]);

        $result = $this->service->getAdsForPlacement('sidebar');

        $this->assertNotNull($result);
        $this->assertEquals($ad->id, $result->id);
    }

    public function test_get_ads_for_placement_ignores_inactive_ads(): void
    {
        Ad::create([
            'title'     => 'Inactive Ad',
            'image'     => 'ads/test.jpg',
            'url'       => 'https://example.com',
            'placement' => 'leaderboard',
            'active'    => false,
        ]);

        $result = $this->service->getAdsForPlacement('leaderboard');

        $this->assertNull($result);
    }

    public function test_get_ads_for_placement_returns_null_when_no_ads(): void
    {
        $result = $this->service->getAdsForPlacement('native');

        $this->assertNull($result);
    }

    public function test_inject_native_ads_inserts_ad_every_n_events(): void
    {
        Ad::create([
            'title'     => 'Native Ad',
            'image'     => 'ads/native.jpg',
            'url'       => 'https://example.com',
            'placement' => 'native',
            'active'    => true,
        ]);

        $events = range(1, 10);

        $result = $this->service->injectNativeAds($events, frequency: 5);

        // 10 events + 2 injected ads (after index 4 and 9)
        $this->assertCount(12, $result);

        $adItems = array_filter($result, fn ($item) => is_array($item) && ($item['_type'] ?? null) === 'ad');
        $this->assertCount(2, $adItems);
    }

    public function test_inject_native_ads_returns_plain_events_when_no_native_ad(): void
    {
        $events = range(1, 5);

        $result = $this->service->injectNativeAds($events, frequency: 2);

        // No ad exists, so no injection
        $this->assertCount(5, $result);
    }

    public function test_geo_targeted_ad_is_preferred_for_location(): void
    {
        $location = Location::create(['name' => 'Nice', 'city' => 'Nice', 'state' => 'FR']);

        $genericAd = Ad::create([
            'title'       => 'Generic',
            'image'       => 'ads/generic.jpg',
            'url'         => 'https://generic.com',
            'placement'   => 'sidebar',
            'active'      => true,
            'location_id' => null,
        ]);

        $geoAd = Ad::create([
            'title'       => 'Nice Ad',
            'image'       => 'ads/nice.jpg',
            'url'         => 'https://nice.com',
            'placement'   => 'sidebar',
            'active'      => true,
            'location_id' => $location->id,
        ]);

        $result = $this->service->getAdsForPlacement('sidebar', $location->id);

        // When filtering by location, only geo-targeted ad should match
        $this->assertEquals($geoAd->id, $result->id);
    }
}
