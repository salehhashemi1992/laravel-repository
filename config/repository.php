<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Repository Paths
    |--------------------------------------------------------------------------
    |
    | This option defines the path where your repositories are located. This allows the
    | application to dynamically resolve repository paths based on your configuration,
    | making it easy to organize and manage your application's data access layer.
    |
    */
    'path' => app_path('Repositories'), // Default repository path

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Settings
    |--------------------------------------------------------------------------
    |
    | The 'limit' value acts as the default number of items per page when 'perPage' is passed as null.
    | It also serves as the maximum limit for 'perPage' to control pagination size.
    | Adjust this value to set the default and maximum items displayed per page.
    |
    */
    'perPage' => env('REPOSITORY_PAGE_SIZE', 20),
];
