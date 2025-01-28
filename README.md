#  Role-Craft for spatie/laravel-permission
[![Latest Version on Packagist](https://img.shields.io/packagist/v/i74ifa/role-craft.svg?style=flat-square)](https://packagist.org/packages/i74ifa/role-craft)
[![Total Downloads](https://img.shields.io/packagist/dt/i74ifa/role-craft.svg?style=flat-square)](https://packagist.org/packages/i74ifa/role-craft)
![GitHub Actions](https://github.com/i74ifa/role-craft/actions/workflows/main.yml/badge.svg)

## Installation

```bash
composer require i74ifa/role-craft
```

### optional

publish config

```bash
php artisan vendor:publish --tag=role-craft-config
```

## Usage


### Generate role and Permissions
```bash
php artisan role-craft:generate
```
this command will be generate all permissions and `role-craft.default_role` will be created
if you want to change default role name, you can change it in config/role-craft.php after publish config


### Sync permissions

```bash
php artisan role-craft:sync manager --all
```
this will be sync all permissions to `manager` role if it exists
if not exists you want to use `--create` option

```bash
php artisan role-craft:sync manager --create
```

if you want to sync some role from models use `--models` option

```bash
php artisan role-craft:sync manager --models=User --models=Post

# OR Custom Directory
php artisan role-craft:sync manager --models=App\Models\Directory\User
```

Generate role if not exists
```bash
php artisan role-craft:sync manager --create
```


## License

This package is released under the [MIT license](https://github.com/i74ifa/role-craft/blob/main/LICENSE).