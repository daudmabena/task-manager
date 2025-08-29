<?php

namespace App\Console\Commands;

use App\Models\System;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateSystemSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'systems:generate-slugs {--force : Force regenerate all slugs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for existing systems that don\'t have slugs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating slugs for systems...');
        
        $query = System::query();
        
        if (!$this->option('force')) {
            $query->where(function($q) {
                $q->whereNull('slug')
                  ->orWhere('slug', '');
            });
        }
        
        $systems = $query->get();
        
        if ($systems->count() === 0) {
            $this->info('No systems need slug generation.');
            return;
        }
        
        $bar = $this->output->createProgressBar($systems->count());
        $bar->start();
        
        $updated = 0;
        
        foreach ($systems as $system) {
            // Generate slug manually using Str::slug()
            $baseSlug = Str::slug($system->name);
            $slug = $baseSlug;
            $counter = 1;
            
            // Ensure uniqueness
            while (System::where('slug', $slug)->where('id', '!=', $system->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $system->slug = $slug;
            $system->save();
            $updated++;
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully generated slugs for {$updated} systems.");
    }
}
