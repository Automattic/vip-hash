<?php
use PHPUnit\Framework\TestCase;

use automattic\vip\hash\HashRecord;

class HashRecordTest extends TestCase {

	public function testGetterSetters() {
		$note = 'Lorem Ipsum';
		$human_note = 'Lorem Ipsum dolors';
		$record = new HashRecord();

		$record->setNote( $note );
		$record->setHumanNote( $human_note );

		$this->assertEquals( $note, $record->getNote() );
		$this->assertEquals( $human_note, $record->getHumanNote() );
	}
}
