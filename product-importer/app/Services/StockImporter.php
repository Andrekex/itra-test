<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StockImporter
{
    /**
     * Import a single row of stock data.
     *
     * @param array $data Parsed row data from the CSV file.
     * @return bool True if the row is successfully imported, false otherwise.
     */
    public function import(array $data): bool
    {
        // Validate data
        if (!$this->isValidRow($data)) {
            Log::warning('Invalid row data.', ['data' => $data]);
            return false;
        }

        // Sanitize data
        $data['price'] = $this->sanitizePrice($data['Cost in GBP']);
        $data['stock'] = $this->sanitizeStock($data['Stock']);
        $data['discontinued'] = strtolower($data['Discontinued']) === 'yes';

        // Apply import rules
        if ($this->shouldSkip($data)) {
            return false;
        }

        try {
            // Insert or update the product
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

            return true;
        } catch (\Exception $e) {
            Log::error('Error importing row.', ['data' => $data, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if a row is valid for processing.
     *
     * @param array $data Row data.
     * @return bool True if the row is valid, false otherwise.
     */
    private function isValidRow(array $data): bool
    {
        return isset($data['Product Code'], $data['Product Name'], $data['Cost in GBP'], $data['Stock'])
            && is_numeric($this->sanitizePrice($data['Cost in GBP']))
            && is_numeric($this->sanitizeStock($data['Stock']));
    }

    /**
     * Sanitize the price field.
     *
     * @param string $price Price value from the row.
     * @return float Sanitized price.
     */
    private function sanitizePrice(string $price): float
    {
        return (float)preg_replace('/[^\\d.]/', '', $price);
    }

    /**
     * Sanitize the stock field.
     *
     * @param string $stock Stock value from the row.
     * @return int Sanitized stock.
     */
    private function sanitizeStock(string $stock): int
    {
        return is_numeric($stock) ? (int)$stock : 0;
    }

    /**
     * Check if a row should be skipped based on business rules.
     *
     * @param array $data Row data.
     * @return bool True if the row should be skipped, false otherwise.
     */
    private function shouldSkip(array $data): bool
    {
        return ($data['price'] < 5 && $data['stock'] < 10) || $data['price'] > 1000;
    }
}
