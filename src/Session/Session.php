<?php

declare(strict_types = 1);

/**
 * Caldera Session
 * Session abstraction layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Session;

use Caldera\Session\Adapter\AdapterInterface;

class Session {

	/**
	 * Session Adapter
	 * @var AdapterInterface
	 */
	protected $adapter;

	/**
	 * Constructor
	 * @param AdapterInterface $adapter Session adapter
	 */
	public function __construct(AdapterInterface $adapter) {
		$this->adapter = $adapter;
	}

	/**
	 * Start session
	 * @return $this
	 */
	public function start() {
		if (! session_id() ) {
			session_set_save_handler($this->adapter->getHandler(), false);
			session_start();
			# Get flashed and to-be-deleted items
			$flash = $this->get('_flash', []);
			$delete = $this->get('_delete', []);
			$keep = $this->get('_keep', []);
			# Remove items that are to be kept
			$delete = array_diff($delete, $keep);
			# Delete the rest
			foreach ($delete as $key) {
				$this->delete($key);
			}
			# Empty flash and to-keep list
			$this->set('_flash', []);
			$this->set('_keep', []);
			# Move flash items to delete list for the next request
			$this->set('_delete', $flash);
		}
		return $this;
	}

	/**
	 * Check if the session contains an item
	 * @param  string  $key Item key
	 * @return bool
	 */
	public function has(string $key): bool {
		return isset( $_SESSION[$key] );
	}

	/**
	 * Get an item from the session
	 * @param  string $key     Item name
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function get(string $key, $default = null) {
		return $_SESSION[$key] ?? $default;
	}

	/**
	 * Set a session item
	 * @param string $key   Item key
	 * @param mixed  $value Item value
	 * @return $this
	 */
	public function set(string $key, $value) {
		$_SESSION[$key] = $value;
		return $this;
	}

	/**
	 * Delete a session item
	 * @param string $key   Item key
	 * @return $this
	 */
	public function delete(string $key) {
		if ( isset( $_SESSION[$key] ) ) {
			unset( $_SESSION[$key] );
		}
		return $this;
	}

	/**
	 * Clear session data
	 * @return $this
	 */
	public function clear() {
		if ( session_id() ) {
			session_destroy();
			session_start();
		}
		return $this;
	}

	/**
	 * Destroy session
	 * @return $this
	 */
	public function destroy() {
		if ( session_id() ) {
			session_destroy();
		}
		return $this;
	}

	/**
	 * Regenerate session
	 * @return $this
	 */
	public function regenerate() {
		if ( session_id() ) {
			session_regenerate_id();
		}
		return $this;
	}

	/**
	 * Get all session items
	 * @return array
	 */
	public function all(): array {
		return $_SESSION;
	}

	/**
	 * Push a session item
	 * @param  string $name  Item name
	 * @param  mixed  $value Item value
	 * @return $this
	 */
	public function push(string $name, $value) {
		if (! isset( $_SESSION[$name] ) ) {
			$_SESSION[$name] = [];
		}
		$_SESSION[$name][] = $value;
		return $this;
	}

	/**
	 * Pull a session item
	 * @param  string $name    Item name
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function pull(string $name, $default = null) {
		$ret = $this->get($name, $default);
		$this->delete($name);
		return $ret;
	}

	/**
	 * Flash a session item
	 * @param  string $name  Item name
	 * @param  mixed  $value Item value
	 * @return $this
	 */
	public function flash(string $name, $value) {
		$this->set($name, $value);
		$this->push('_flash', $name);
		return $this;
	}

	/**
	 * Reflash session
	 * @return $this
	 */
	public function reflash() {
		$flash = $this->get('_flash', []);
		$this->set('_keep', $flash);
		return $this;
	}

	/**
	 * Keep a flashed item
	 * @param  mixed $name Item name
	 * @return $this
	 */
	public function keep($name) {
		if ( is_array($name) ) {
			foreach ($name as $key) {
				$this->keep($key);
			}
		} else {
			$this->push('_keep', $name);
		}
		return $this;
	}
}
