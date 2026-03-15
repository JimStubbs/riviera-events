<?php

namespace Tests\Feature\Admin;

use App\Jobs\ProcessCsvImport;
use App\Jobs\ProcessCsvPreview;
use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_preview_returns_headers_and_rows(): void
    {
        Storage::fake('local');

        $csv = implode("\n", [
            'title,description,start_date,end_date,location,category,image_url,organizer,is_premium,is_featured',
            'Jazz Night,A jazz event,2026-06-01,2026-06-01,Nice,Music,,Jazz Club,false,false',
            'Art Show,An art show,2026-06-15,2026-06-15,Cannes,Art,,Gallery,true,false',
        ]);

        $path = 'csv-imports/test.csv';
        Storage::disk('local')->put($path, $csv);

        $result = (new ProcessCsvPreview())->handle(Storage::disk('local')->path($path));

        $this->assertNull($result['error']);
        $this->assertCount(10, $result['headers']);
        $this->assertCount(2, $result['rows']);
        $this->assertEquals('Jazz Night', $result['rows'][0]['title']);
    }

    public function test_csv_preview_returns_max_five_rows(): void
    {
        Storage::fake('local');

        $rows = ['title,description,start_date,end_date,location,category,image_url,organizer,is_premium,is_featured'];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = "Event {$i},Desc,2026-06-{$i},2026-06-{$i},Venue,Music,,Org,false,false";
        }

        $path = 'csv-imports/big.csv';
        Storage::disk('local')->put($path, implode("\n", $rows));

        $result = (new ProcessCsvPreview())->handle(Storage::disk('local')->path($path));

        $this->assertCount(5, $result['rows']);
    }

    public function test_csv_import_creates_events(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $csv = implode("\n", [
            'title,description,start_date,end_date,location,category,image_url,organizer,is_premium,is_featured',
            'Sunset Gala,A gala,2026-07-01,2026-07-01,Nice,Music,,Gala Org,false,false',
            'Film Fest,Cinema event,2026-07-10,2026-07-10,Cannes,Film,,Fest Org,true,true',
        ]);

        $path = 'csv-imports/import.csv';
        Storage::disk('local')->put($path, $csv);

        (new ProcessCsvImport($path, publish: false))->handle();

        $this->assertDatabaseHas('events', ['title' => 'Sunset Gala', 'status' => 'pending_approval']);
        $this->assertDatabaseHas('events', ['title' => 'Film Fest', 'is_premium' => true]);
    }

    public function test_csv_import_skips_duplicate_events(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $location = Location::create(['name' => 'Nice', 'city' => 'Nice', 'state' => 'FR']);
        $category = Category::create(['name' => 'Music']);

        Event::create([
            'title'       => 'Jazz Night',
            'description' => '-',
            'start_date'  => '2026-06-01 00:00:00',
            'end_date'    => '2026-06-01 23:59:00',
            'status'      => 'approved',
            'location_id' => $location->id,
            'category_id' => $category->id,
            'organizer'   => 'Jazz Club',
        ]);

        $csv = implode("\n", [
            'title,description,start_date,end_date,location,category,image_url,organizer,is_premium,is_featured',
            'Jazz Night,Duplicate,2026-06-01,2026-06-01,Nice,Music,,Jazz Club,false,false',
        ]);

        $path = 'csv-imports/dup.csv';
        Storage::disk('local')->put($path, $csv);

        (new ProcessCsvImport($path))->handle();

        $this->assertDatabaseCount('events', 1);
    }

    public function test_csv_import_with_publish_sets_approved_status(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $csv = implode("\n", [
            'title,description,start_date,end_date,location,category,image_url,organizer,is_premium,is_featured',
            'Instant Event,Published immediately,2026-08-01,2026-08-01,Nice,Music,,Org,false,false',
        ]);

        $path = 'csv-imports/publish.csv';
        Storage::disk('local')->put($path, $csv);

        (new ProcessCsvImport($path, publish: true))->handle();

        $this->assertDatabaseHas('events', ['title' => 'Instant Event', 'status' => 'approved']);
    }

    public function test_csv_preview_handles_malformed_file(): void
    {
        Storage::fake('local');

        $path = 'csv-imports/broken.csv';
        Storage::disk('local')->put($path, 'not,a,valid csv file without proper structure');

        $result = (new ProcessCsvPreview())->handle(Storage::disk('local')->path($path));

        // Should not throw, just return empty/error result
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
    }
}
