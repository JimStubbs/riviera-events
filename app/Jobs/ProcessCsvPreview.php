<?php

namespace App\Jobs;

use League\Csv\Reader;

class ProcessCsvPreview
{
    /**
     * Synchronously parse the first 5 data rows of an uploaded CSV.
     *
     * Returns an array with:
     *   - headers: string[]
     *   - rows:    array of associative arrays (up to 5 rows)
     *   - error:   ?string
     */
    public function handle(string $path): array
    {
        try {
            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0);

            $headers = $csv->getHeader();
            $rows    = [];

            foreach ($csv->getRecords() as $record) {
                $rows[] = $record;
                if (count($rows) >= 5) {
                    break;
                }
            }

            return [
                'headers' => $headers,
                'rows'    => $rows,
                'error'   => null,
            ];
        } catch (\Throwable $e) {
            return [
                'headers' => [],
                'rows'    => [],
                'error'   => 'Could not parse CSV: ' . $e->getMessage(),
            ];
        }
    }
}
