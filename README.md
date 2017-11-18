# Laravel Payments

[![Build Status](https://travis-ci.org/bcismariu/laravel-payments.svg?branch=master)](https://travis-ci.org/bcismariu/laravel-payments)
[![Latest Stable Version](https://poser.pugx.org/bcismariu/laravel-payments/v/stable)](https://packagist.org/packages/bcismariu/laravel-payments)
[![License](https://poser.pugx.org/bcismariu/laravel-payments/license)](https://packagist.org/packages/bcismariu/laravel-payments)
[![Total Downloads](https://poser.pugx.org/bcismariu/laravel-payments/downloads)](https://packagist.org/packages/bcismariu/laravel-payments)

A very basic alternative to Laravel Cashier built to allow lighter implementations.

### Installation

Use composer to install the package.

`composer require bcismariu/laravel-payments`

Edit `config/app.php` and add the following line to your `providers` list:

`Bcismariu\Laravel\Payments\PaymentsServiceProvider::class`

Publish the package configuration files.

```
php artisan vendor:publish --provider="Bcismariu\\Laravel\\Payments\\PaymentsServiceProvider" --tag="config" --force
```

Migrate the database

```
php artisan migrate
```

#### Attention!

If you plan using the `konnektive` driver, you should also require its dependencies.

Edit your `composer.json` file to reflect the following:

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/bcismariu/konnektive-crm"
        }
    ],
    "require": {
        "hassletauf/konnektive-crm": "dev-validation"
    },
```

Add the following lines to your `.env` file:

```
KONNEKTIVE_LOGIN=your-konnektive-loginId
KONNEKTIVE_PASSWORD=your-konnektive-password
```


### Usage

Add the `Billable` trait on your User model:

```php
use Bcismariu\Laravel\Payments\Billable;

class User
{
    use Billable;
```

Import the Credit Card info into your User object and charge it:

```php
$user->setCreditCard(new Card([
    'brand'     => 'visa',
    'number'    => '0000000000000000',
    'exp_month' => '02',
    'exp_year'  => '2017',
    'cvc_check' => '123', 
]));

$response = $user->charge(5, [
        'product_id'    => 1234
    ]);
```