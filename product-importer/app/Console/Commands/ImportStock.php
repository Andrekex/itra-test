<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CsvReader;
use App\Services\StockImporter;

class ImportStock extends Command
{
    protected $signature = 'stock:import {--test}';
    protected $description = 'Import stock data from a CSV file';

    private $csvReader;
    private $stockImporter;

    public function __construct(CsvReader $csvReader, StockImporter $stockImporter)
    {
        parent::__construct();

        $this->csvReader = $csvReader;
        $this->stockImporter = $stockImporter;
    }

    public function handle()
    {
        $filePath = storage_path('app/stock.csv');

        if (!file_exists($filePath)) {
            $this->error('CSV file not found.');
            return;
        }

        $rows = $this->csvReader->read($filePath);
        if (empty($rows)) {
            $this->error('No data found in the CSV file.');
            return;
        }

        DB::beginTransaction();

        $processed = $successful = $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $processed++;

            try {
                if ($this->stockImporter->import($row)) {
                    $successful++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                ];
                $skipped++;
            }
        }

        if ($this->option('test')) {
            DB::rollBack();
            $this->info('Test mode: Transaction rolled back.');
        } else {
            DB::commit();
        }

        $this->info("Processed: $processed, Successful: $successful, Skipped: $skipped");

        if (!empty($errors)) {
            $this->warn('Failed rows:');
            foreach ($errors as $error) {
                $this->warn("Row {$error['row']}: {$error['error']}");
            }
        }
    }
}
