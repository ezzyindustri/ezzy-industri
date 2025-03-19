<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OeeRecord;
use App\Models\Production;

class UpdateOeeRecords extends Command
{
    protected $signature = 'oee:update-records';
    protected $description = 'Update existing OEE records with last_updated and is_initial_record values';

    public function handle()
    {
        $this->info('Updating OEE records...');
        
        $records = OeeRecord::all();
        $count = 0;
        
        foreach ($records as $record) {
            $production = Production::find($record->production_id);
            
            if ($production) {
                $record->last_updated = now();
                $record->is_initial_record = $production->end_time ? false : true;
                $record->save();
                $count++;
            }
        }
        
        $this->info("Updated {$count} OEE records.");
    }
}