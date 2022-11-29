<?php

declare(strict_types = 1);

/**
 * Caldera Session
 * Session abstraction layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Session\Adapter;

use SessionHandler;
use SessionHandlerInterface;

use Caldera\Session\Adapter\AdapterInterface;

class NativeAdapter implements AdapterInterface {

	/**
	 * Adapter handler
	 * @var SessionHandler
	 */
	protected $handler;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->handler = new SessionHandler();
	}

	/**
	 * Get adapter handler
	 * @return SessionHandlerInterface
	 */
	public function getHandler(): SessionHandlerInterface {
		return $this->handler;
	}
}
