<?php
/**
 * Helper functions that wrap for the methods exposed by the Transient class.
 */

namespace TenUp\AsyncTransients;

/**
 * Deletes a given transient
 *
 * @param string $transient The key for the transient to delete
 *
 * @return bool Result of delete_option
 */
function delete_async_transient( $transient ) {
	return Transient::instance()->delete( $transient );
}

/**
 * Returns the value of an async transient.
 *
 * @param string $transient The key of the transient to return
 * @param Callable $regenerate_function The function to call to regenerate the transient when it is expired
 * @param array $regenerate_params Array of parameters to pass to the callback when regenerating the transient.
 *
 * @return mixed|void
 */
function get_async_transient( $transient, $regenerate_function, $regenerate_params = array() ) {
	return Transient::instance()->get( $transient, $regenerate_function, $regenerate_params );
}

/**
 * Set the value of an async transient.
 *
 * @param string $transient Unique key for the transient
 * @param mixed $value The value to store for the transient
 * @param int $expiration Number of seconds until the transient should be considered expired.
 *
 * @return bool
 */
function set_async_transient( $transient, $value, $expiration ) {
	return Transient::instance()->set( $transient, $value, $expiration );
}
