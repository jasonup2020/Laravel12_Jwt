<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeDTOCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new DTO class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $dtoName = $this->qualifyDTOName($name);
        $path = app_path("DTOs/{$dtoName}.php");

        if (File::exists($path)) {
            $this->error('DTO already exists!');
            return;
        }

        File::ensureDirectoryExists(app_path('DTOs'));

        File::put($path, $this->getStub($dtoName));

        $this->info("DTO {$dtoName} created successfully.");
    }

    protected function qualifyDTOName(string $name): string
    {
        return str($name)->studly()->finish('Data');
    }

    protected function getStub(string $class): string
    {
        return <<<PHP
<?php

namespace App\DTOs;

class {$class}
{
    // Define your public properties and constructor
}

PHP;
    }
}
