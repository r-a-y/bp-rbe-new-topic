<?php

namespace BP_RBE_New_Topic;

/**
 * Abstract initializer.
 *
 * @since 0.1
 */
abstract class Init {
	/**
	 * Starts the extended class.
	 *
	 * @since 0.1
	 */
	public static function init() {
		return new static();
	}

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	final protected function __construct() {
		$this->hooks();
	}

	/**
	 * Hooks method.
	 *
	 * Meant to be extended.
	 *
	 * @since 0.1
	 */
	abstract protected function hooks();
}