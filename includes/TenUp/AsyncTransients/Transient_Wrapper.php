<?php

namespace TenUp\AsyncTransients;

/**
 * Class Transient_Wrapper
 * @package TenUp\AsyncTransients
 */
class Transient_Wrapper {

	protected $key;
	protected $default_value;
	protected $length;
	protected $callback;
	protected $args;
	protected $internal_callback;

	/**
	 * Cache constructor.
	 *
	 * @param string $key cache key
	 * @param mixed $default_value value to return if the cache is not yet warm
	 * @param int $length number of seconds before value expires
	 * @param callable $callback callback to regenerate the cache
	 * @param array $args optional, arguments to pass into the callback
	 */
	public function __construct( $key, $default_value, $length, callable $callback, $args = [] ) {
		$this->key           = $key;
		$this->default_value = $default_value;
		$this->length        = $length;
		$this->args          = $args;
		$this->set_callback( $callback );


	}

	/**
	 * Mutator for default value
	 *
	 * @param $default_value
	 */
	public function set_default_value( $default_value ) {
		$this->default_value = $default_value;
	}

	/**
	 * Mutator for cache length
	 *
	 * @param $length
	 */
	public function set_length( $length ) {
		$this->length = $length;
	}

	/**
	 * Mutator for callback
	 *
	 * @param callable $callback
	 */
	public function set_callback( callable $callback ) {

		$key    = $this->key;
		$length = $this->length;
		$args   = $this->args;

		$this->internal_callback = function () use ( $callback, $key, $length, $args ) {
			$value = call_user_func_array( $callback, (array) $args );

			return \TenUp\AsyncTransients\set_async_transient( $key, $value, $length );


		};
	}

	/**
	 * Mutator for arguments
	 *
	 * @param array $args
	 */
	public function set_arguments( array $args ) {
		$this->args = $args;
	}

	/**
	 * If the default value is false or we get a result from the \TenUp\AsyncTransients\get_async_transient call
	 * return it, otherwise return the default value
	 *
	 * @return mixed
	 */
	public function get() {

		$value = \TenUp\AsyncTransients\get_async_transient( $this->key, $this->internal_callback,
			(array) $this->args );

		return false === $value ? $this->default_value : $value;
	}
}