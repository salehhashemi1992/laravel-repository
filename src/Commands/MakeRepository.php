<?php

declare(strict_types=1);

namespace Salehhashemi\Repository\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name : The name of the model}';

    protected $description = 'Creates a new repository, interface, and filter for the specified model';

    public function handle(Filesystem $filesystem): void
    {
        $name = Str::studly($this->argument('name'));
        $modelName = class_basename($name);
        $modelNamespace = 'App\\Models'; // Assuming models are under the 'App\Models' namespace

        // Derive paths and namespaces from configuration
        $repositoryPath = config('repository.path', app_path('Repositories'));
        $repositoryNamespace = $this->pathToNamespace($repositoryPath);

        // Check and create the necessary directories
        $this->ensureDirectoryExists($repositoryPath, $filesystem);

        // Paths for generated files
        $repositoryFilePath = "{$repositoryPath}/{$modelName}Repository.php";
        $interfaceFilePath = "{$repositoryPath}/Contracts/{$modelName}RepositoryInterface.php";
        $filterFilePath = "{$repositoryPath}/Filters/{$modelName}Filter.php";

        // Generate repository, interface, and filter
        $this->createFileFromStub('repository.stub', $repositoryFilePath, [
            '{{namespace}}' => $repositoryNamespace,
            '{{modelName}}' => $modelName,
            '{{modelNamespace}}' => $modelNamespace,
            '{{name}}' => "{$modelName}",
        ], $filesystem);

        $this->createFileFromStub('repository-interface.stub', $interfaceFilePath, [
            '{{namespace}}' => "{$repositoryNamespace}\\Contracts",
            '{{modelName}}' => $modelName,
            '{{modelNamespace}}' => $modelNamespace,
            '{{name}}' => "{$modelName}",
        ], $filesystem);

        $this->createFileFromStub('filter.stub', $filterFilePath, [
            '{{namespace}}' => "{$repositoryNamespace}\\Filters",
            '{{modelName}}' => $modelName,
            '{{modelNamespace}}' => $modelNamespace,
            '{{name}}' => "{$modelName}",
        ], $filesystem);

        $this->info("Repository, interface, and filter for {$modelName} created successfully.");
    }

    protected function createFileFromStub(
        string $stubName,
        string $filePath,
        array $replacements,
        Filesystem $filesystem
    ): void {
        $stubPath = __DIR__."/stubs/{$stubName}";
        $content = $filesystem->get($stubPath);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        if (! $filesystem->put($filePath, $content)) {
            throw new RuntimeException("Failed to create file at {$filePath}");
        }

        $this->info("Created: {$filePath}");
    }

    protected function ensureDirectoryExists(string $path, Filesystem $filesystem): void
    {
        if (! $filesystem->isDirectory($path)) {
            $filesystem->makeDirectory($path, 0755, true);
        }
    }

    protected function pathToNamespace(string $path): string
    {
        // Convert file path to namespace
        $namespace = str_replace([app_path(), '/', '\\'], ['', '\\', '\\'], $path);

        return 'App'.($namespace ? "\\{$namespace}" : '');
    }
}
