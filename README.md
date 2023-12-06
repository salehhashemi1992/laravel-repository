<div align="center">

# Laravel Repository Pattern

[![Latest Version on Packagist](https://img.shields.io/packagist/v/salehhashemi/laravel-repository.svg?style=flat-square)](https://packagist.org/packages/salehhashemi/laravel-repository)
[![Total Downloads](https://img.shields.io/packagist/dt/salehhashemi/laravel-repository.svg?style=flat-square)](https://packagist.org/packages/salehhashemi/laravel-repository)
[![GitHub Actions](https://img.shields.io/github/actions/workflow/status/salehhashemi1992/laravel-repository/static-analysis.yml?branch=main&label=static-analysis)](https://github.com/salehhashemi1992/laravel-repository/actions/workflows/static-analysis.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)

</div>

This Laravel package abstracts the database layer using repository pattern with enhanced capabilities for filtering and searching. It simplifies the common tasks of data manipulation, along with advanced features for applying custom filters and search criteria.

## Features
* Repository Abstraction
* Dynamic Filtering
* Search Functionality

## Installation
To install the package, you can run the following command:
```bash
composer require salehhashemi/laravel-repository
```

## Configuration
To publish the config file, run the following command:
```bash
php artisan vendor:publish --provider="Salehhashemi\Repository\RepositoryServiceProvider" --tag="config"
```
After publishing, make sure to clear the config cache to apply your changes:
```bash
php artisan config:clear
```
Then, you can adjust the pagination limit in the `config/otp.php`

### Exceptions
* `\InvalidArgumentException` Thrown if page size is invalid.

## Docker Setup
This project uses Docker for local development and testing. Make sure you have Docker and Docker Compose installed on your system before proceeding.

### Build the Docker images
```bash
docker-compose build
```

### Start the services
```bash
docker-compose up -d
```
To access the PHP container, you can use:
```bash
docker-compose exec php bash
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](changelog.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](contributing.md) for details.

## Credits

- [Saleh Hashemi](https://github.com/salehhashemi1992)
- [IMahmood](https://github.com/imahmood)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](license.md) for more information.