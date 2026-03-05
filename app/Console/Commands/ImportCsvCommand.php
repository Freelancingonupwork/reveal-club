<?php

namespace App\Console\Commands;

use App\Models\NutritionIngredient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from a CSV file into the ingredients table';

    /**
     * Execute the console command.
     */
    

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $csvFile = $this->argument('file');

        if (!file_exists($csvFile) || !is_readable($csvFile)) {
            $this->error('CSV file does not exist or is not readable.');
            return 1;
        }

        if (($handle = fopen($csvFile, 'r')) !== false) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Skip header row
                if ($row === 0) {
                    $row++;
                    continue;
                }

                $name = $data[0];
                $kcal = $this->processValue($data[1]);
                $protein = $this->processValue($data[2]);
                $carbs = $this->processValue($data[3]);
                $fats = $this->processValue($data[4]);

                $slug = Str::slug($name);

                // Create or update the ingredient record
                NutritionIngredient::updateOrCreate(
                    ['name' => $name],
                    [
                        'kcal' => $kcal,
                        'protein' => $protein,
                        'carbs' => $carbs,
                        'fats' => $fats,
                        'slug' => $slug,
                        'image' => null, // Modify if handling images
                        'status' => 1,
                    ]
                );

                $row++;
            }
            fclose($handle);
        }

        $this->info('CSV data has been successfully imported.');
        return 0;
    }

    private function processValue($value)
    {
        // Replace (-) with 0
        if ($value === '-' || empty($value)) {
            return '0';
        }

        // Remove (<) and keep only the numeric value
        if (strpos($value, '<') !== false) {
            $value = str_replace('<', '', $value);
        }

        // Replace (,) with (.)
        $value = str_replace(',', '.', $value);

        // Remove trailing .00
        if (strpos($value, '.00') !== false) {
            $value = intval($value); // Store as integer
        }

        // Convert non-numeric or unknown values like "traces" to 0
        if (!is_numeric($value)) {
            return '0';
        }

        return $value;
    }
}
