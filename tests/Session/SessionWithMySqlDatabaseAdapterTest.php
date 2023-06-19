<?php

declare(strict_types = 1);

/**
 * Caldera Session
 * Session abstraction layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Tests\Session;

use SessionHandlerInterface;

use Caldera\Database\Adapter\MySQLAdapter;
use Caldera\Database\Database;
use Caldera\Session\Adapter\DatabaseAdapter;
use Caldera\Session\Session;

use PHPUnit\Framework\TestCase;

class SessionWithMySqlDatabaseAdapterTest extends TestCase {

	function testWithMySqlAdapter() {
		$options = [
			'host' => 'localhost',
			'user' => 'root',
			'name' => 'caldera',
		];
		$database_adapter = new MySQLAdapter($options);
		$database = new Database($database_adapter);
		$session_adapter = new DatabaseAdapter($database);
		$session = new Session($session_adapter);
		$session->start();
		$this->assertInstanceOf(SessionHandlerInterface::class, $session_adapter->getHandler());
		#
		$session->set('foo', '098f6bcd4621d373cade4e832627b4f6');
		$foo = $session->get('foo');
		$this->assertEquals('098f6bcd4621d373cade4e832627b4f6', $foo);
		#
		$session->set('bar', ['207c91804d4cbf698be032d2dd7ff735', 'a63743936f0a537ce6a333be23151fc85']);
		$bar = $session->get('bar');
		$this->assertIsArray($bar);
		# Cleanup for other adapters
		$session->regenerate();
		$session->destroy();
	}
}
