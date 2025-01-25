<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class ImportStockTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Use fake storage to isolate the test
        Storage::fake('local');
    }

    public function test_import_stock()
    {
        // Prepare a mock CSV file
        $mockCsvContent = <<<CSV
Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued
P0001,TV,32” Tv,10,399.99,
P0002,Cd Player,Nice CD player,11,50.12,yes
P0003,VCR,Top notch VCR,1,4.33,
CSV;

        // Write the mock CSV to fake storage
        Storage::disk('local')->put('stock.csv', $mockCsvContent);

        // Ensure the mock CSV is written correctly
        $this->assertEquals($mockCsvContent, Storage::disk('local')->get('stock.csv'));

        // Execute the artisan command and assert expected output
        $this->artisan('stock:import --test')
            ->expectsOutput('Test mode: Transaction rolled back.')
            ->expectsOutput('Processed: 3, Successful: 2, Skipped: 1')
            ->assertExitCode(0);
    }
}
