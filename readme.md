Async Transients
================

Transients that serve stale data while regenerating the new transients in the background. 

Requires support for fastcgi_finish_request, or else transients will regenerate expired data immediately.

## Background & Purpose

Transients are great for storing data that is expensive to regenerate, but we still run in to the problem of needing to regenerate that data synchronously once the transient expires. This library solves that problem by serving stale data once the transient is expired, and processing the regenerate callback after the request has finished, so that end users never see the impact of regenerating transients.

## Installation



## Usage



## Issues

If you identify any errors or have an idea for improving the plugin, please [open an issue](https://github.com/10up/Async-Transients/issues). We're excited to see what the community thinks of this project, and we would love your input!

## License

Async Transients is free software; you can redistribute it and/or modify it under the terms of the MIT License.
