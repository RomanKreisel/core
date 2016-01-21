<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\DAV\Tests\Unit\Migration;

use DomainException;
use OCA\Dav\Migration\AddressBookAdapter;
use OCP\IDBConnection;
use Test\TestCase;

/**
 * Class AddressbookAdapterTest
 *
 * @group DB
 *
 * @package OCA\DAV\Tests\Unit\Migration
 */
class AddressbookAdapterTest extends TestCase {

	/** @var IDBConnection */
	private $db;
	/** @var AddressBookAdapter */
	private $adapter;
	/** @var array */
	private $books = [];
	/** @var array */
	private $cards = [];

	public function setUp() {
		parent::setUp();
		$this->db = \OC::$server->getDatabaseConnection();

		$manager = new \OC\DB\MDB2SchemaManager($this->db);
		$manager->createDbFromStructure(__DIR__ . '/contacts_schema.xml');

		$this->adapter = new AddressBookAdapter($this->db);
	}

	public function tearDown() {
		$this->db->dropTable('contacts_addressbooks');
		$this->db->dropTable('contacts_cards');
		return parent::tearDown();
	}

	/**
	 * @expectedException DomainException
	 */
	public function testOldTablesDoNotExist() {
		$adapter = new AddressBookAdapter(\OC::$server->getDatabaseConnection(), 'crazy_table_that_does_no_exist');
		$adapter->setup();
	}

	public function test() {

		// insert test data
		$book = [
			'userid' => 'test-user-666',
			'displayname' => 'Display Name',
			'uri' => 'contacts',
			'description' => 'An address book for testing',
			'ctag' => '112233',
			'active' => '1'
		];
		$this->db->insertIfNotExist('*PREFIX*contacts_addressbooks', $book);
		$card = [
			'addressbookid' => 6666,
			'fullname' => 'Full Name',
			'carddata' => 'datadatadata',
			'uri' => 'some-card.vcf',
			'lastmodified' => '112233',
		];
		$this->db->insertIfNotExist('*PREFIX*contacts_cards', $card);

		// test the adapter
		$this->adapter->foreachBook('test-user-666', function($row) {
			$this->books[] = $row;
		});
		$this->assertArrayHasKey('id', $this->books[0]);
		$this->assertEquals($book['userid'], $this->books[0]['userid']);
		$this->assertEquals($book['displayname'], $this->books[0]['displayname']);
		$this->assertEquals($book['uri'], $this->books[0]['uri']);
		$this->assertEquals($book['description'], $this->books[0]['description']);
		$this->assertEquals($book['ctag'], $this->books[0]['ctag']);

		$this->adapter->foreachCard(6666, function($row) {
			$this->cards[]= $row;
		});
		$this->assertArrayHasKey('id', $this->cards[0]);
	}

}
