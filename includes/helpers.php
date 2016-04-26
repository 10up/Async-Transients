<?php

namespace TenUp\AsyncTransients;

function delete_async_transient( $transient ) {
	return Transient::instance()->delete( $transient );
}

function get_async_transient( $transient, $regenerate_function, $regenerate_params = array() ) {
	return Transient::instance()->get( $transient, $regenerate_function, $regenerate_params );
}

function set_async_transient( $transient, $value, $expiration ) {
	return Transient::instance()->set( $transient, $value, $expiration );
}
