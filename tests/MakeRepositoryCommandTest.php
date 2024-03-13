<?php

namespace Salehhashemi\Repository\Tests;

use Illuminate\Support\Facades\File;

class MakeRepositoryCommandTest extends BaseTest
{
    protected string $model = 'TestModel';

    protected string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = config('repository.path', app_path('Repositories'));
    }

    protected function tearDown(): void
    {
        // Cleanup: Remove the directories and files created during the test
        File::deleteDirectory($this->basePath);

        parent::tearDown();
    }

    public function testRepositoryStubGeneration(): void
    {
        $this->artisan('make:repository', ['name' => $this->model]);

        $expectedFiles = [
            "{$this->basePath}/{$this->model}Repository.php",
            "{$this->basePath}/Contracts/{$this->model}RepositoryInterface.php",
            "{$this->basePath}/Filters/{$this->model}Filter.php",
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($file);
        }
    }

    public function testRepositoryStubGenerationWithFQCN(): void
    {
        $fqcn = 'App\Models\Special\\'.$this->model;

        $this->artisan('make:repository', ['name' => $fqcn]);

        $expectedNamespace = 'namespace App\Repositories';

        $expectedFiles = [
            "{$this->basePath}/{$this->model}Repository.php",
            "{$this->basePath}/Contracts/{$this->model}RepositoryInterface.php",
            "{$this->basePath}/Filters/{$this->model}Filter.php",
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($file);

            $content = file_get_contents($file);
            $this->assertStringContainsString($expectedNamespace, $content);
        }
    }
}
