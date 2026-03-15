<?php

namespace App\Filament\Pages;

use App\Jobs\ProcessCsvImport;
use App\Jobs\ProcessCsvPreview;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class CsvImport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'CSV Import';
    protected static ?string $navigationGroup = 'Events';
    protected static ?int    $navigationSort  = 99;
    protected static string  $view            = 'filament.pages.csv-import';

    public ?array $data = [];

    /** Preview state */
    public array  $previewHeaders = [];
    public array  $previewRows    = [];
    public ?string $previewError  = null;
    public bool   $previewed      = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('csv_file')
                    ->label('CSV File')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                    ->disk('local')
                    ->directory('csv-imports')
                    ->required(),

                Toggle::make('publish')
                    ->label('Publish immediately (skip pending approval)')
                    ->default(false),
            ])
            ->statePath('data');
    }

    public function preview(): void
    {
        $this->form->validate();

        $path = Storage::disk('local')->path($this->data['csv_file']);

        $result = (new ProcessCsvPreview())->handle($path);

        $this->previewHeaders = $result['headers'];
        $this->previewRows    = $result['rows'];
        $this->previewError   = $result['error'];
        $this->previewed      = true;
    }

    public function import(): void
    {
        $this->form->validate();

        ProcessCsvImport::dispatch(
            $this->data['csv_file'],
            (bool) ($this->data['publish'] ?? false),
        );

        $this->previewed      = false;
        $this->previewHeaders = [];
        $this->previewRows    = [];
        $this->form->fill();

        Notification::make()
            ->title('Import queued')
            ->body('Events are being imported in the background.')
            ->success()
            ->send();
    }
}
