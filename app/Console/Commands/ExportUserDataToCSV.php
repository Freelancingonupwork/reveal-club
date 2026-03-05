<?php

namespace App\Console\Commands;

use App\Models\User;
use League\Csv\Writer;
use League\Csv\Reader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportUserDataToCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:users-to-csv';
    protected $description = 'Export user data to a CSV file weekly';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $csvFilePath = storage_path('export/subscriber-data.csv');
        $csvDirectory = storage_path('export');
        
        if (!File::exists($csvDirectory)) {
            File::makeDirectory($csvDirectory, 0777, true);
            $this->info("Created directory: {$csvDirectory}");
        }

        $csv = Writer::createFromPath($csvFilePath, 'a+');
        $existingData = [];
        if (File::exists($csvFilePath)) {
            $csvReader = Reader::createFromPath($csvFilePath, 'r');
            $existingData = iterator_to_array($csvReader->getRecords());
        }

        $correctHeader = ['First Name', 'Last Name', 'Email', 'Address', 'Country', 'City', 'Postal Code'];

        if (empty($existingData)) {
            $csv->insertOne($correctHeader);
        } else {
            $header = $existingData[0];

            if (count($header) !== count($correctHeader)) {
                $this->info("Header columns are incomplete or incorrect. Adjusting...");
                
                $existingData[0] = $correctHeader;
            }
        }

        $users = User::select('first_name', 'last_name', 'email', 'address', 'country', 'city', 'postal_code')->get();

        $existingEmails = array_column($existingData, 1);

        foreach ($users as $user) {
            $userData = [
                $user->name,
                $user->email,
                $user->address,
                $user->country,
                $user->city,
                $user->postal_code
            ];

            $updated = false;
            foreach ($existingData as &$row) {
                if ($row[1] == $user->email) {
                    $row = $userData;
                    $updated = true;
                    break;
                }
            }

            if (!$updated) {
                $existingData[] = $userData;
            }
        }

        $csv = Writer::createFromPath($csvFilePath, 'w');

        $csv->insertAll($existingData);

        $this->info('User data exported and updated successfully to CSV.');
        \Log::info("User data exported and updated successfully to CSV.");
    }
}
