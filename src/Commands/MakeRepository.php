<?php

declare(strict_types=1);

namespace Salehhashemi\Repository\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name : The (fully qualified) name of the model}';

    protected $description = 'Creates a new repository, interface, and filter for the specified model';

    public function __construct(private readonly Filesystem $filesystem)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $name = $this->argument('name');

        if (Str::contains($name, '\\')) {
            $modelName = Str::afterLast($name, '\\');
            $modelNamespace = Str::beforeLast($name, '\\');
        } else {
            $modelName = Str::studly(class_basename($name));
            $modelNamespace = app()->getNamespace().'Models';
        }

        // Configuration-based paths
        $repositoryPath = config('repository.path', app_path('Repositories'));

        $paths = [
            "{$repositoryPath}/{$modelName}Repository.php" => 'repository.stub',
            "{$repositoryPath}/Contracts/{$modelName}RepositoryInterface.php" => 'repository-interface.stub',
            "{$repositoryPath}/Filters/{$modelName}Filter.php" => 'filter.stub',
        ];

        foreach ($paths as $filePath => $stubName) {
            $this->ensureDirectoryExists(dirname($filePath));

            $replacements = [
                '{{namespace}}' => str_replace('/', '\\', $this->pathToNamespace(dirname($filePath))),
                '{{modelName}}' => $modelName,
                '{{modelNamespace}}' => $modelNamespace,
                '{{name}}' => $modelName,
            ];

            $content = $this->getStubContent($stubName, $replacements);
            $this->filesystem->put($filePath, $content);

            $this->info("Created: {$filePath}");
        }
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! $this->filesystem->isDirectory($path)) {
            $this->filesystem->makeDirectory($path, 0755, true);
        }
    }

    protected function pathToNamespace(string $path): string
    {
        $appNamespace = app()->getNamespace();
        $relativePath = str_replace(app_path(), '', $path);
        $namespace = str_replace(['/', '\\'], '\\', $relativePath);
        $namespace = ltrim($namespace, '\\');

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
