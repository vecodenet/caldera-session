<?php

declare(strict_types = 1);

/**
 * Caldera Session
 * Session abstraction layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Session\Adapter;

use SessionHandlerInterface;

interface AdapterInterface {

	/**
	 * Get adapter handler
	 * @return SessionHandlerInterface
	 */
	public function getHandler(): SessionHandlerInterface;
}
