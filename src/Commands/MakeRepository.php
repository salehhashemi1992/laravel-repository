<?php

declare(strict_types=1);

namespace Salehhashemi\Repository\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name : The name of the model}';

    protected $description = 'Creates a new repository, interface, and filter for the specified model';

    public function handle(Filesystem $filesystem): void
    {
        $name = $this->argument('name');
        $modelName = Str::studly(class_basename($name));
        $modelNamespace = 'App\\Models';

        // Configuration-based paths
        $repositoryPath = config('repository.path', app_path('Repositories'));

        $paths = [
            "{$repositoryPath}/{$modelName}Repository.php" => 'repository.stub',
            "{$repositoryPath}/Contracts/{$modelName}RepositoryInterface.php" => 'repository-interface.stub',
            "{$repositoryPath}/Filters/{$modelName}Filter.php" => 'filter.stub',
        ];

        foreach ($paths as $filePath => $stubName) {
            $this->ensureDirectoryExists(dirname($filePath), $filesystem);

            $replacements = [
                '{{namespace}}' => str_replace('/', '\\', $this->pathToNamespace(dirname($filePath))),
                '{{modelName}}' => $modelName,
                '{{modelNamespace}}' => $modelNamespace,
                '{{name}}' => $modelName,
            ];

            $content = $this->getStubContent($stubName, $replacements);
            $filesystem->put($filePath, $content);

            $this->info("Created: {$filePath}");
        }
    }

    protected function ensureDirectoryExists(string $path, Filesystem $filesystem): void
    {
        if (! $filesystem->isDirectory($path)) {
            $filesystem->makeDirectory($path, 0755, true);
        }
    }

    protected function pathToNamespace(string $path): string
    {
        $appNamespace = 'App';
        $relativePath = str_replace(app_path(), '', $path);
        $namespace = str_replace(['/', '\\'], '\\', $relativePath);

        return trim($appNamespace.$namespace, '\\');
    }

    protected function getStubContent(string $stubName, array $replacements): string
    {
        $stubPath = __DIR__."/stubs/{$stubName}";
        if (! file_exists($stubPath)) {
            throw new \RuntimeException("Stub not found: {$stubPath}");
        }

        $content = (string) file_get_contents($stubPath);
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        return $content;
    }
}
