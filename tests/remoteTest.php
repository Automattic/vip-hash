<?php
use PHPUnit\Framework\TestCase;
use automattic\vip\hash\Remote;

class RemoteTest extends TestCase {

	public function testGetterSetters() {
		$name = 'Lorem Ipsum';
		$uri = 'https://example.com';

		$r = new Remote();
		$r->setName( $name );
		$r->setUri( $uri );

		$this->assertEquals( $name, $r->getName() );
		$this->assertEquals( $uri, $r->getUri() );
	}
}
