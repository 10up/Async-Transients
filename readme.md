Async Transients
================

Transients that serve stale data while regenerating the new transients in the background. 

Requires support for fastcgi_finish_request, or else transients will regenerate expired data immediately.

## Background & Purpose

Transients are great for storing data that is expensive to regenerate, but we still run in to the problem of needing to regenerate that data synchronously once the transient expires. This library solves that problem by serving stale data once the transient is expired, and processing the regenerate callback after the request has finished, so that end users never see the impact of regenerating transients.

## Installation

This library is meant to be included with composer. To install, run `composer require 10up/async-transients`. The
library is set up to use composer's autoloader, so make to you are loading your `vendor/autoload.php` file.

## Usage

Usage is similar to standard WordPress transient functions, in that you provide a transient key and an expiration time,
but its different in that you must also provide a callback function, as well as any (optional) parameters to pass to the
callback function, that should be called to regenerate the transient data if it is expired.

Example Usage:

```php
// Function to regenerate expired transient
function get_data_from_api( $user_id ) {
	// Fake function, that we assume is really time consuming to run
	$my_result = get_api_data_for_user( $user_id );

	\TenUp\AsyncTransients\set_async_transient( 'transient-key-' . $user_id, $my_result, MINUTE_IN_SECONDS );
}

// This would very likely not be hardcoded...
$user_id = 1;

$transient_value = \TenUp\AsyncTransients\get_async_transient( 'transient-key-' . $user_id, 'get_data_from_api', array( $user_id ) );

// Outputs the value stored in the transient
// If the transient is expired, it will still show the last known data, while queueing the transient to be updated behind the scenes.
var_dump( $transient_value );

```

## Issues

If you identify any errors or have an idea for improving the plugin, please [open an issue](https://github.com/10up/Async-Transients/issues). We're excited to see what the community thinks of this project, and we would love your input!

## License

Async Transients is free software; you can redistribute it and/or modify it under the terms of the MIT License.
