<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;

class ProcessCsvImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly string $path,
        public readonly bool   $publish = false,
    ) {}

    public function handle(): void
    {
        $csv = Reader::createFromPath(Storage::disk('local')->path($this->path), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $row) {
            $this->importRow($row);
        }

        Storage::disk('local')->delete($this->path);
    }

    private function importRow(array $row): void
    {
        $title = trim($row['title'] ?? '');
        if (empty($title)) {
            return;
        }

        // Duplicate detection: same title + same start_date
        $startDate = $this->parseDate($row['start_date'] ?? '');
        if (! $startDate) {
            return;
        }

        $dateOnly = \Carbon\Carbon::parse($startDate)->toDateString();

        $exists = Event::where('title', $title)
            ->whereDate('start_date', $dateOnly)
            ->exists();

        if ($exists) {
            return;
        }

        $location = $this->resolveLocation($row['location'] ?? '');
        $category = $this->resolveCategory($row['category'] ?? '');

        $imagePath = null;
        if (! empty($row['image_url'])) {
            $imagePath = $this->downloadImage($row['image_url']);
        }

        $endDate = $this->parseDate($row['end_date'] ?? '');

        Event::create([
            'title'       => $title,
            'description' => $row['description'] ?? '',
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'organizer'   => $row['organizer'] ?? '',
            'location_id' => $location?->id,
            'category_id' => $category?->id,
            'image'       => $imagePath,
            'is_premium'  => filter_var($row['is_premium'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_featured' => filter_var($row['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'status'      => $this->publish ? 'approved' : 'pending_approval',
        ]);
    }

    private function resolveLocation(string $name): ?Location
    {
        if (empty(trim($name))) {
            return null;
        }

        return Location::firstOrCreate(
            ['name' => trim($name)],
            ['city' => trim($name), 'state' => ''],
        );
    }

    private function resolveCategory(string $name): ?Category
    {
        if (empty(trim($name))) {
            return null;
        }

        return Category::firstOrCreate(
            ['name' => trim($name)],
        );
    }

    private function parseDate(string $value): ?string
    {
        if (empty(trim($value))) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename  = 'events/' . Str::uuid() . '.' . $extension;

            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Throwable) {
            return null;
        }
    }
}
