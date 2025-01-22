<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RollbackAllProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:rollback-all-properties {--step=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback migrations for all property databases';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $steps = $this->option('step');
        $properties = Property::all(); // Get all properties from the master database
        $path = 'database/migrations/property_specific';

        foreach ($properties as $property) {
            $normalizedPropertyName = strtolower(str_replace(' ', '_', $property->property_name));

            // Configure the database connection for the property
            Config::set('database.connections.property', [
                'driver' => 'mysql',
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT'),
                'database' => $normalizedPropertyName,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
            ]);

            DB::purge('property');
            DB::reconnect('property');

            $this->info("Rolling back migrations for property database: {$normalizedPropertyName}");

            $this->call('migrate:rollback', [
                '--database' => 'property',
                '--path' => $path,
                '--step' => $steps,
            ]);

            $this->info("Rollback completed for property database: {$normalizedPropertyName}");
        }

        $this->info('Rollback completed for all property databases.');
    }
}
