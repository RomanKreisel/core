<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OCA\Files_External\Tests\Command;

use OCA\Files_External\Command\Applicable;

class ApplicableTest extends CommandTest {

	public function testListEmpty() {
		$mount = $this->getMount(1, '', '');

		$storageService = $this->getGlobalStorageService([$mount]);
		$command = new Applicable($storageService);

		$input = $this->getInput($command, [
			'mount_id' => 1,
			'type' => 'users'
		], [
			'output' => 'json'
		]);

		$result = json_decode($this->executeCommand($command, $input));

		$this->assertEquals([], $result);
	}

	public function testList() {
		$mount = $this->getMount(1, '', '', '', [], [], ['test', 'asd']);

		$storageService = $this->getGlobalStorageService([$mount]);
		$command = new Applicable($storageService);

		$input = $this->getInput($command, [
			'mount_id' => 1,
			'type' => 'users'
		], [
			'output' => 'json'
		]);

		$result = json_decode($this->executeCommand($command, $input));

		$this->assertEquals(['test', 'asd'], $result);
	}

	public function testAddSingle() {
		$mount = $this->getMount(1, '', '', '', [], [], []);

		$storageService = $this->getGlobalStorageService([$mount]);
		$command = new Applicable($storageService);

		$input = $this->getInput($command, [
			'mount_id' => 1,
			'type' => 'users'
		], [
			'output' => 'json',
			'add' => ['foo']
		]);

		$this->executeCommand($command, $input);

		$this->assertEquals(['foo'], $mount->getApplicableUsers());
	}

	public function testAddDuplicate() {
		$mount = $this->getMount(1, '', '', '', [], [], ['foo']);

		$storageService = $this->getGlobalStorageService([$mount]);
		$command = new Applicable($storageService);

		$input = $this->getInput($command, [
			'mount_id' => 1,
			'type' => 'users'
		], [
			'output' => 'json',
			'add' => ['foo', 'bar']
		]);

		$this->executeCommand($command, $input);

		$this->assertEquals(['foo', 'bar'], $mount->getApplicableUsers());
	}

	public function testRemoveSingle() {
		$mount = $this->getMount(1, '', '', '', [], [], ['foo', 'bar']);

		$storageService = $this->getGlobalStorageService([$mount]);
		$command = new Applicable($storageService);

		$input = $this->getInput($command, [
			'mount_id' => 1,
			'type' => 'users'
		], [
			'output' => 'json',
			'remove' => ['bar']
		]);

		$this->executeCommand($command, $input);

		$this->assertEquals(['foo'], $mount->getApplicableUsers());
	}

	public function testRemoveNonExisting() {
		$mount = $this->getMount(1, '', '', '', [], [], ['foo', 'bar']);

		$storageService = $this->getGlobalStorageService([$mount]);
		$command = new Applicable($storageService);

		$input = $this->getInput($command, [
			'mount_id' => 1,
			'type' => 'users'
		], [
			'output' => 'json',
			'remove' => ['bar', 'asd']
		]);

		$this->executeCommand($command, $input);

		$this->assertEquals(['foo'], $mount->getApplicableUsers());
	}

	public function testRemoveAddRemove() {
		$mount = $this->getMount(1, '', '', '', [], [], ['foo', 'bar']);

		$storageService = $this->getGlobalStorageService([$mount]);
		$command = new Applicable($storageService);

		$input = $this->getInput($command, [
			'mount_id' => 1,
			'type' => 'users'
		], [
			'output' => 'json',
			'remove' => ['bar', 'asd'],
			'add' => ['test']
		]);

		$this->executeCommand($command, $input);

		$this->assertEquals(['foo', 'test'], $mount->getApplicableUsers());
	}
}
