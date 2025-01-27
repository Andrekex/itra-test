<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CsvReader
{
    /**
     * Read and parse a CSV file.
     *
     * @param string $filePath Path to the CSV file.
     * @return array Parsed rows as associative arrays.
     */
    public function read(string $filePath): array
    {
        $rows = [];

        // Check if the file exists and is readable
        if (!file_exists($filePath) || !is_readable($filePath)) {
            Log::error("CSV file not found or not readable: $filePath");
            return $rows;
        }

        try {
            // Open the file for reading
            $file = fopen($filePath, 'r');

            // Get the header row
            $header = fgetcsv($file);
            if (!$header) {
                Log::error('CSV header row missing or invalid.');
                return $rows;
            }

            // Read and process each row
            while (($data = fgetcsv($file)) !== false) {
                // Skip rows with mismatched column counts
                if (count($header) !== count($data)) {
                    Log::warning('Skipping row with mismatched column count.', ['data' => $data]);
                    continue;
                }

                // Map row data to the header
                $rows[] = array_combine($header, $data);
            }

            // Close the file
            fclose($file);
        } catch (\Exception $e) {
            Log::error('Error reading CSV file.', ['error' => $e->getMessage()]);
        }

        return $rows;
    }
}
