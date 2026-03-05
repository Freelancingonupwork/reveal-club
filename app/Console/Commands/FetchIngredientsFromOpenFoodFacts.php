<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\NutritionIngredient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchIngredientsFromOpenFoodFacts extends Command
{
    protected $signature = 'fetch:ingredients {startPage} {endPage}';
    protected $description = 'Fetch ingredients data from Open Food Facts and insert it into the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ini_set('memory_limit', '-1');

        $baseUrl = 'https://world.openfoodfacts.org/cgi/search.pl';
        $startPage = $this->argument('startPage'); // Get starting page from the command argument
        $endPage = $this->argument('endPage'); // Get ending page from the command argument
        $page = $startPage;

        while ($page <= $endPage) {
            $response = Http::withOptions(['verify' => false])
                ->retry(3, 1000)
                ->timeout(30)
                ->get($baseUrl, [
                    'search_terms' => '',
                    'search_simple' => 1,
                    'action' => 'process',
                    'json' => 1,
                    'page_size' => 50,
                    'page' => $page,
                    // 'lc' => 'en',
                    // 'tagtype_0' => 'ingredients',
                    // 'tag_contains_0' => 'contains',
                ]);

            if ($response->successful()) {
                $ingredients = $response->json()['products'];

                if (empty($ingredients)) {
                    break; // Exit loop if no more ingredients
                }

                foreach ($ingredients as $ingredientData) {
                    if (!isset($ingredientData['product_name']) || trim($ingredientData['product_name']) === '') {
                        continue; // Skip if the name is not present or is empty
                    }

                    $name = $ingredientData['product_name'];
                    $slug = Str::slug($name);

                    // Process nutritional values
                    $carbs = $this->formatNutrient($ingredientData['nutriments']['carbohydrates_100g'] ?? null);
                    $fats = $this->formatNutrient($ingredientData['nutriments']['fat_100g'] ?? null);
                    $protein = $this->formatNutrient($ingredientData['nutriments']['proteins_100g'] ?? null);
                    $kcal = $this->formatNutrient($ingredientData['nutriments']['energy-kcal_100g'] ?? null);

                    // Skip if all nutritional values are zero
                    if (($carbs === 0 || is_null($carbs)) && ($fats === 0 || is_null($fats)) && ($protein === 0 || is_null($protein)) && ($kcal === 0 || is_null($kcal))) {
                        continue;
                    }

                    $imageUrl = $ingredientData['image_url'] ?? null;
                    $imagePath = null;

                    if ($imageUrl) {
                        try {
                            $imageContents = file_get_contents($imageUrl);
                            $imageName = $slug . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
                            $imagePath = 'ingredient_images/' . $imageName;
                            Storage::put($imagePath, $imageContents);
                        } catch (\Exception $e) {
                            Log::error("Failed to download image for $name: " . $e->getMessage());
                            $imagePath = null;
                        }
                    }

                    NutritionIngredient::updateOrCreate(
                        ['name' => $name],
                        [
                            'carbs' => $carbs,
                            'fats' => $fats,
                            'protein' => $protein,
                            'kcal' => $kcal,
                            'slug' => $slug,
                            'image' => $imagePath,
                            'status' => 1,
                        ]
                    );

                    $this->info("Ingredient '$name' added or updated.");
                }
                $this->warn("Page $page completed successfully.");
                $page++;
            } else {
                $this->error('Failed to fetch ingredients.');
                Log::error('Failed to fetch ingredients on page ' . $page);
                break;
            }
        }
        $this->info('Ingredients fetching completed successfully.');
    }

    /**
     * Format the nutrient value.
     *
     * @param float|null $value
     * @return mixed
     */
    private function formatNutrient($value)
    {
        if (is_null($value)) {
            return 0;
        }

        // Remove the ".00" if it's a whole number
        if (intval($value) == $value) {
            return intval($value);
        }

        return $value;
    }
}