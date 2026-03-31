<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    public function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel on Windows reads Vietnamese correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                $normalizedRow = array_map(function ($value) {
                    if (is_bool($value)) {
                        return $value ? 'Yes' : 'No';
                    }

                    if (is_array($value)) {
                        return implode(' | ', array_map('strval', $value));
                    }

                    return $value ?? '';
                }, $row);

                fputcsv($handle, $normalizedRow);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
