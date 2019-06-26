# Async Transients

> Transients that serve stale data while regenerating the new transients in the background.

[![Support Level](https://img.shields.io/badge/support-active-green.svg)](#support-level) [![Release Version](https://img.shields.io/github/tag/10up/Async-Transients.svg?label=release)](https://github.com/10up/Async-Transients/releases/latest) [![MIT License](https://img.shields.io/github/license/10up/Async-Transients.svg)](https://github.com/10up/Async-Transients/blob/develop/LICENSE.md)

## Background & Purpose

Transients are great for storing data that is expensive to regenerate, but we still run in to the problem of needing to regenerate that data synchronously once the transient expires. This library solves that problem by serving stale data once the transient is expired, and processing the regenerate callback after the request has finished, so that end users never see the impact of regenerating transients.

## Requirements

Requires support for `fastcgi_finish_request`, or else transients will regenerate expired data immediately.

## Installation

This library is meant to be included with composer. To install, run `composer require 10up/async-transients`. The
library is set up to use composer's autoloader, so make certain you are loading your `vendor/autoload.php` file.

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

// If the transient is expired get_data_from_api() is called, with $user_id as a parameter
$transient_value = \TenUp\AsyncTransients\get_async_transient( 'transient-key-' . $user_id, 'get_data_from_api', array( $user_id ) );

// Outputs the value stored in the transient
// If the transient is expired, it will still show the last known data, while queueing the transient to be updated behind the scenes.
var_dump( $transient_value );

```

## How does all of this work?

First, when calling `get_async_transient`, you now have to pass a callback function, and optionally, any parameters to
pass to the callback function. The transient is then retrieved, much like how WordPress core would retrieve it, but
with a key difference. Instead of returning nothing if the transient is expired, we return the last known value, and
add the callback function and params to a queue, to process later. By the end of the request, we have a queue of that
contains callback functions for all transients that were accessed, that had expired data.

Next, we hook into the WordPress `shutdown` action. The `shutdown` action runs just before PHP shuts down execution. The
Transient class hooks into that action, and calls the [`fastcgi_finish_request` function](http://php.net/manual/en/function.fastcgi-finish-request.php), if it is available.
That function flushes all response data to the client, and as far as the browser is concerned, the request is done,
however, php is allowed to keep running in the background.

At this point, we iterate over all the callback functions in the queue, which then regenerate any transient data
that was accessed, but was expired.

## Issues

If you identify any errors or have an idea for improving the plugin, please [open an issue](https://github.com/10up/Async-Transients/issues). We're excited to see what the community thinks of this project, and we would love your input!

## Support Level

**Active:** 10up is actively working on this, and we expect to continue work for the foreseeable future including keeping tested up to the most recent version of WordPress.  Bug reports, feature requests, questions, and pull requests are welcome.

## Like what you see?

<p align="center">
<a href="http://10up.com/contact/"><img src="https://10updotcom-wpengine.s3.amazonaws.com/uploads/2016/10/10up-Github-Banner.png" width="850"></a>
</p>
