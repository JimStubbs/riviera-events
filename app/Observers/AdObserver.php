<?php

namespace App\Observers;

use App\Models\Ad;
use Illuminate\Support\Facades\Cache;

class AdObserver
{
    public function created(Ad $ad): void
    {
        $this->flushCache();
    }

    public function updated(Ad $ad): void
    {
        $this->flushCache();
    }

    public function deleted(Ad $ad): void
    {
        $this->flushCache();
    }

    private function flushCache(): void
    {
        Cache::tags(['ads'])->flush();
    }
}
