<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $serviceName = $this->qualifyServiceName($name);
        $path = app_path("Services/{$serviceName}.php");

        if (File::exists($path)) {
            $this->error('Service already exists!');
            return;
        }

        File::ensureDirectoryExists(app_path('Services'));

        File::put($path, $this->getStub($serviceName));

        $this->info("Service {$serviceName} created successfully.");
    }

    protected function qualifyServiceName(string $name): string
    {
        return str($name)->studly()->finish('Service');
    }

    protected function getStub(string $class): string
    {
        return <<<PHP
<?php

namespace App\Services;

class {$class}
{
    //
}

PHP;
    }
}
