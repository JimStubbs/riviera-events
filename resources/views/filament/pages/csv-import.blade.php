<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Upload Form --}}
        <x-filament::section>
            <x-slot name="heading">Upload CSV</x-slot>
            <x-slot name="description">
                Required columns: <code>title</code>, <code>description</code>, <code>start_date</code>,
                <code>end_date</code>, <code>location</code>, <code>category</code>,
                <code>image_url</code>, <code>organizer</code>, <code>is_premium</code>, <code>is_featured</code>
            </x-slot>

            <form wire:submit.prevent="preview">
                {{ $this->form }}

                <div class="mt-4 flex gap-3">
                    <x-filament::button type="submit" icon="heroicon-o-eye">
                        Preview (first 5 rows)
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Preview Table --}}
        @if ($this->previewed)
            <x-filament::section>
                <x-slot name="heading">Preview</x-slot>

                @if ($this->previewError)
                    <x-filament::badge color="danger">
                        {{ $this->previewError }}
                    </x-filament::badge>
                @elseif (empty($this->previewRows))
                    <p class="text-sm text-gray-500 dark:text-gray-400">No rows found in CSV.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead>
                                <tr>
                                    @foreach ($this->previewHeaders as $header)
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">
                                            {{ $header }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($this->previewRows as $row)
                                    <tr>
                                        @foreach ($this->previewHeaders as $header)
                                            <td class="px-3 py-2 text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                                {{ $row[$header] ?? '' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <x-filament::button
                            wire:click="import"
                            wire:loading.attr="disabled"
                            color="success"
                            icon="heroicon-o-arrow-up-tray"
                        >
                            <span wire:loading.remove wire:target="import">Import All Rows</span>
                            <span wire:loading wire:target="import">Importing…</span>
                        </x-filament::button>
                    </div>
                @endif
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
