# RabbitMQ for Laravel

We provide a separate package for the use of [RabbitMQ](https://www.rabbitmq.com) because we want to use it for communication between microservices, written in any language. The existing packages are bound to laravel so we have the whole data - with class names and so on - within the message body. Our package sends only the raw data through RabbitMQ.

[![Latest Stable Version](https://poser.pugx.org/ipunkt/laravel-rabbitmq/v/stable.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![Latest Unstable Version](https://poser.pugx.org/ipunkt/laravel-rabbitmq/v/unstable.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![License](https://poser.pugx.org/ipunkt/laravel-rabbitmq/license.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![Total Downloads](https://poser.pugx.org/ipunkt/laravel-rabbitmq/downloads.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq)

## Quickstart

```
composer require ipunkt/laravel-rabbitmq
```

We support package auto-discovery for laravel, so you are ready to use the package.


## Installation

Add to your composer.json following lines

	"require": {
		"ipunkt/laravel-rabbitmq": "*"
	}

You can publish all provided files by typing `php artisan vendor:publish` and select to package provider (or one of the provided tags - but be careful, tags are global).

## Configuration

Here are the configuration options:

## Usage

You can use the package like so...
