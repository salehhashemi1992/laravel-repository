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

        // Ensure the target directory exists
        if (! File::isDirectory($this->basePath)) {
            File::makeDirectory($this->basePath, 0755, true);
        }
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
            $this->assertTrue(File::exists($file), "$file does not exist.");
        }
    }
}
