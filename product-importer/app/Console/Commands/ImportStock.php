<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ImportStock extends Command
{
    protected $signature = 'stock:import {--test}';
    protected $description = 'Import stock data from a CSV file';

    public function handle()
    {
        $file = storage_path('app/stock.csv');
        if (!file_exists($file)) {
            $this->error('CSV file not found.');
            return;
        }

        $csvData = array_map('str_getcsv', file($file));
        $header = array_map('trim', $csvData[0]);
        unset($csvData[0]);

        $processed = $successful = $skipped = 0;

        DB::beginTransaction();

        foreach ($csvData as $rowIndex => $row) {
            $processed++;

            // Skip rows with mismatched columns
            if (count($header) !== count($row)) {
                Log::warning("Row skipped due to mismatched columns", ['rowIndex' => $rowIndex + 1, 'row' => $row]);
                $skipped++;
                continue;
            }

            // Map data to header
            $data = array_combine($header, array_map('trim', $row));

            // Validate data
            if (!$this->isValidRow($data)) {
                Log::warning("Row skipped due to invalid data", ['rowIndex' => $rowIndex + 1, 'data' => $data]);
                $skipped++;
                continue;
            }

            // Sanitize data
            $data['price'] = $this->sanitizePrice($data['Cost in GBP']);
            $data['stock'] = $this->sanitizeStock($data['Stock']);
            $data['discontinued'] = strtolower($data['Discontinued']) === 'yes';

            try {
                if ($this->shouldSkip($data)) {
                    $skipped++;
                    continue;
                }

                Product::updateOrCreate(
                    ['sku' => $data['Product Code']],
                    [
                        'name' => $data['Product Name'],
                        'description' => $data['Product Description'],
                        'price' => $data['price'],
                        'stock' => $data['stock'],
                        'discontinued_date' => $data['discontinued'] ? Carbon::now() : null,
                    ]
                );

                $successful++;
            } catch (\Exception $e) {
                Log::error('Error importing row', ['data' => $data, 'error' => $e->getMessage()]);
                $skipped++;
            }
        }

        if ($this->option('test')) {
            DB::rollBack();
            $this->info("Test mode: Transaction rolled back.");
        } else {
            DB::commit();
        }

        $this->info("Processed: $processed, Successful: $successful, Skipped: $skipped");
    }

    private function shouldSkip($data)
    {
        return ($data['price'] < 5 && $data['stock'] < 10) || $data['price'] > 1000;
    }

    private function isValidRow($data)
    {
        return isset($data['Product Code'], $data['Product Name'], $data['Cost in GBP'], $data['Stock']) &&
               is_numeric($this->sanitizePrice($data['Cost in GBP'])) &&
               is_numeric($this->sanitizeStock($data['Stock']));
    }

    private function sanitizePrice($price)
    {
        // Remove non-numeric characters and convert to float
        return (float)preg_replace('/[^\d.]/', '', $price);
    }

    private function sanitizeStock($stock)
    {
        // Convert empty or invalid stock values to 0
        return is_numeric($stock) ? (int)$stock : 0;
    }
}
