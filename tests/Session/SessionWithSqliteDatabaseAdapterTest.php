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

use Caldera\Database\Adapter\SQLiteAdapter;
use Caldera\Database\Database;
use Caldera\Session\Adapter\DatabaseAdapter;
use Caldera\Session\Session;

use PHPUnit\Framework\TestCase;

class SessionWithSqliteDatabaseAdapterTest extends TestCase {

	public static function setUpBeforeClass(): void {
		$path = dirname(__DIR__) . '/output/database_test.sqlite';
		# Create an empty SQLite database file
		$data = '7dHBSsNAEAbg2WjxJClI8DpHBdvG3UvtRdO6SjCNmqxgb0YToWhtifHi0XfwAXw'.
				'jH8mkKPYQMHf/D35YZobZhY0vg2mR8f08nyUFK2qTEHTETCR6RGR9R5T5KLNOvw'.
				'T9qdzRjW82q2ObyH4gAAAAAAAAgH/gfdtxxNtGkdw+ZkX2XFSxRpH2jGbjDQPNV'.
				'YV3pin7odGnOuKLyB970YTP9ITDc8PhVRDs8VMyy9joa7NSu8uzpMhSPi63GX+s'.
				'V1ovi7S+tfvzMvvTPrQX+CEAAAAAAACAGj2rRY5SRsdGulJ23H5HHvB+fyDVQMq'.
				'aUnetRVtKDZPXZvPlHe5yPm+4Xyz3n8znzea/AA==';
		file_put_contents( $path, gzinflate( base64_decode($data) ) );
	}

	public static function tearDownAfterClass(): void {
		unlink( dirname(__DIR__) . '/output/database_test.sqlite' );
	}

	function testWithSqliteAdapter() {
		$options = [
			'file' => dirname(__DIR__) . '/output/database_test.sqlite'
		];
		$database_adapter = new SQLiteAdapter($options);
		$database = new Database($database_adapter);
		$session_adapter = new DatabaseAdapter($database);
		$session = new Session($session_adapter);
		$session->start();
		$this->assertInstanceOf(SessionHandlerInterface::class, $session_adapter->getHandler());
		#
		$session->set('foo', '098f6bcd4621d373cade4e832627b4f6');
		$foo = $session->get('foo');
		$this->assertEquals('098f6bcd4621d373cade4e832627b4f6', $foo);
		# Cleanup for other adapters
		$session->regenerate();
		$session->destroy();
	}
}
