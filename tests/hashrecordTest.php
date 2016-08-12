<?php
use PHPUnit\Framework\TestCase;

use automattic\vip\hash\HashRecord;

class HashRecordTest extends TestCase {

	public function testGetterSetters() {
		$note = 'Lorem Ipsum';
		$human_note = 'Lorem Ipsum dolors';
		$username = 'john smith';
		$status = 'great';
		$hash = '1234';
		$date = 1470957266;

		$record = new HashRecord();

		$record->setNote( $note );
		$record->setHumanNote( $human_note );
		$record->setUsername( $username );
		$record->setStatus( $status );
		$record->setHash( $hash );
		$record->setDate( $date );
		$data = $record->getData();

		$this->assertEquals( $note, $record->getNote() );
		$this->assertEquals( $human_note, $record->getHumanNote() );
		$this->assertEquals( $username, $record->getUsername() );
		$this->assertEquals( $status, $record->getStatus() );
		$this->assertEquals( $hash, $record->getHash() );
		$this->assertEquals( $date, $record->getDate() );

		$this->assertEquals( $data['hash'], $hash );

		$newdata = [1,2,3,4];
		$record->setData( $newdata );
		$this->assertEquals( $newdata, $record->getData() );
	}
}
