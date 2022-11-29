<?php

declare(strict_types = 1);

/**
 * Caldera Session
 * Session abstraction layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Session\Adapter;

use RuntimeException;
use SessionHandlerInterface;

use Caldera\Database\Database;
use Caldera\Database\Adapter\MySQLAdapter;
use Caldera\Database\Adapter\SQLiteAdapter;
use Caldera\Session\Adapter\AdapterInterface;

class DatabaseAdapter implements AdapterInterface, SessionHandlerInterface {

	/**
	 * Database instance
	 * @var Database
	 */
	protected $database;

	/**
	 * Session table name
	 * @var string
	 */
	protected $table;

	/**
	 * Setup flag
	 * @var bool
	 */
	protected $is_setup;

	/**
	 * Constructor
	 */
	public function __construct(Database $database, string $table = 'sessions') {
		$this->database = $database;
		$this->table = $table;
	}

	/**
	 * Get adapter handler
	 * @return SessionHandlerInterface
	 */
	public function getHandler(): SessionHandlerInterface {
		return $this;
	}

	/**
	 * Close the session
	 * @return bool
	 */
	public function close(): bool {
		return true;
	}

	/**
	 * Destroy a session
	 * @param  string $id The session ID being destroyed
	 * @return bool
	 */
	public function destroy(string $id): bool {
		$res = $this->database->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
		return $res === true ?: false;
	}

	/**
	 * Cleanup old sessions
	 * @param  int    $max_lifetime Sessions that have not updated for the last max_lifetime seconds will be removed
	 * @return int|false
	 */
	public function gc(int $max_lifetime): int|false {
		$limit = time() - intval($max_lifetime);
		$res = $this->database->query("DELETE FROM {$this->table} WHERE timestamp < ?", [$limit]);
		return $res === true ?: false;
	}

	/**
	 * Initialize session
	 * @param  string $path The path where to store/retrieve the session
	 * @param  string $name The session name
	 * @return bool
	 */
	public function open(string $path, string $name): bool {
		$this->setup();
		$limit = time() - (3600 * 24);
		$res = $this->database->query("DELETE FROM {$this->table} WHERE timestamp < ?", [$limit]);
		return $res === true ?: false;
	}

	/**
	 * Read session data
	 * @param  string $id The session id
	 * @return string|false
	 */
	public function read(string $id): string|false {
		$res = $this->database->select("SELECT data FROM {$this->table} WHERE id = ?", [$id]);
		if ($res !== false) {
			$row = $res[0] ?? null;
			return $row->data ?? '';
		} else {
			return false;
		}
	}

	/**
	 * Write session data
	 * @param  string $id   The session id
	 * @param  string $data The encoded session data
	 * @return bool
	 */
	public function write(string $id, string $data): bool {
		$time = time();
		$res = $this->database->query("REPLACE INTO {$this->table} (id, data, timestamp) VALUES(?, ?, ?)", [$id, $data, $time]);
		return $res === true ?: false;
	}

	/**
	 * Setup adapter
	 * @return void
	 */
	protected function setup(): void {
		if (! $this->is_setup ) {
			$adapter = $this->database->getAdapter();
			switch ( get_class($adapter) ) {
				case MySQLAdapter::class:
					$this->database->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (`id` varchar(32) NOT NULL, `timestamp` int(10) unsigned DEFAULT NULL, `data` mediumtext, PRIMARY KEY (`id`), KEY `timestamp` (`timestamp`))");
				break;
				case SQLiteAdapter::class:
					$this->database->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (`id` TEXT PRIMARY KEY NOT NULL, `timestamp` TEXT DEFAULT NULL, `data` TEXT)");
					$this->database->query("CREATE INDEX IF NOT EXISTS `timestamp` ON `{$this->table}` (`timestamp`);");
				break;
				default:
					throw new RuntimeException(sprintf("Unsupported database adapter '%s'", get_class($adapter)));
				break;
			}
			$this->is_setup = true;
		}
	}
}
