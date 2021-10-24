# Taqnyat PHP

[![Packagist](https://img.shields.io/badge/packagist-v1.0.1-blue)](https://packagist.org/packages/taqnyat/php)
[![Packagist](https://img.shields.io/badge/Download-12.4KB-Green)](https://packagist.org/packages/taqnyat/php)


## Documentation

The documentation for Taqnyat API can be found [here][apidocs].

The PHP library documentation can be found [here][libdocs].

## Versions

`Taqnyat` this is a beta version of Taqnyat to support the PHP technology , and we will continue adding more features to support the php community.
### Supported PHP Versions

Taqnyat library supports PHP 7.0+.

## Installation

You can install **Taqnyat-php** via composer or by downloading the source.

### Via Composer:

**Taqnyat-php** is available on Packagist as the
[`taqnyat/php`](https://packagist.org/packages/taqnyat/php) package:

```
composer require taqnyat/php
```

## Quickstart

### Get Services status

```php
<?php

$status = $taqnyt->sendStatus();
print $status;
```

### Get the account balance and status

```php
<?php
$bearer = '**************************0adc2b';
$taqnyt = new TaqnyatSms($bearer);


$balance = $taqnyt->balance();
print $balance;
```

### Get the account senders

```php
<?php
$bearer = '**************************0adc2b';
$taqnyt = new TaqnyatSms($bearer);


$senders = $taqnyt->senders();
print $senders;
```

### Send an SMS

```php
// Sending a SMS using Taqnyat API and PHP is easy as the following:
<?php
$bearer = '**************************0adc2b';
$taqnyt = new TaqnyatSms($bearer);

$body = 'message Content';
$recipients = ['966********'];
$sender = 'Sender Name';
$smsId = '25489';

$message =$taqnyt->sendMsg($body, $recipients, $sender, $smsId);
print $message;
```

### Send a schedule SMS

```php
// Sending a SMS using Taqnyat API and PHP is easy as the following:
<?php
$bearer = '**************************0adc2b';
$taqnyt = new TaqnyatSms($bearer);

$body = 'message Content';
$recipients = ['966********'];
$sender = 'Sender Name';
$smsId = '';
$schedule = '2020-09-30T14:26';
$deleteId = 100;

$message = $taqnyt->sendMsg($body, $recipients, $sender, $smsId, $scheduled, $deleteId);
print $message;
```

## Getting help

If you need help installing or using the library, please send us email to [Taqnyat Team](mailto:dev@taqnyat.sa) .

If you've instead found a bug in the library or would like new features added, go ahead and send us email , we are welcoming to any suggestion any time

[apidocs]: http://taqnyat.sa/documentation
[libdocs]: https://github.com/taqnyat/php/blob/master/README.md
