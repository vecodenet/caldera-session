<?php

declare(strict_types = 1);

/**
 * Caldera Session
 * Session abstraction layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Tests\Session;

use SessionHandler;

use Caldera\Session\Adapter\NativeAdapter;
use Caldera\Session\Session;

use PHPUnit\Framework\TestCase;

class SessionWithNativeAdapterTest extends TestCase {

	function testCreateHandler() {
		$adapter = new NativeAdapter();
		$this->assertInstanceOf(SessionHandler::class, $adapter->getHandler());
	}

	function testSetAndGetItem() {
		$adapter = new NativeAdapter();
		$session = new Session($adapter);
		$session->start();
		$session->set('foo', '37b51d194a7513e45b56f6524f2d51f2');
		$session->set('bar', ['207c91804d4cbf698be032d2dd7ff735', 'a63743936f0a537ce6a333be23151fc85']);
		$this->assertTrue( $session->has('foo') );
		$foo = $session->get('foo');
		$this->assertEquals('37b51d194a7513e45b56f6524f2d51f2', $foo);
		$session->delete('foo');
		$this->assertFalse( $session->has('foo') );
		$session->clear();
		$this->assertEquals( [], $session->all() );
	}

	function testPushPull() {
		$adapter = new NativeAdapter();
		$session = new Session($adapter);
		$session->start();
		$session->push('foo', '37b51d194a7513e45b56f6524f2d51f2');
		$session->push('foo', '207c91804d4cbf698be032d2dd7ff735');
		$session->push('foo', 'a63743936f0a537ce6a333be23151fc85');
		$this->assertCount( 3, $session->get('foo') );
		$val = $session->pull('foo');
		$this->assertCount( 3, $val );
		$this->assertFalse( $session->has('foo') );
	}

	function testFlash() {
		$adapter = new NativeAdapter();
		$session = new Session($adapter);
		$session->start();
		$session->flash('foo', '37b51d194a7513e45b56f6524f2d51f2');
		$this->assertCount( 1, $session->get('_flash') );
		$session->reflash();
		$this->assertCount( 1, $session->get('_keep') );
		$session->flash('bar', '207c91804d4cbf698be032d2dd7ff735');
		$session->keep('bar');
		$this->assertCount( 2, $session->get('_keep') );
		$session->keep(['foo', 'bar']);
		$this->assertCount( 4, $session->get('_keep') );
		# Cleanup for other adapters
		$session->regenerate();
		$session->destroy();
	}
}
