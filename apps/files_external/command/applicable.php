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

namespace OCA\Files_External\Command;

use OC\Core\Command\Base;
use OCA\Files_external\Lib\StorageConfig;
use OCA\Files_external\NotFoundException;
use OCA\Files_external\Service\GlobalStoragesService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Applicable extends Base {
	/**
	 * @var GlobalStoragesService
	 */
	protected $globalService;

	function __construct(GlobalStoragesService $globalService) {
		parent::__construct();
		$this->globalService = $globalService;
	}

	protected function configure() {
		$this
			->setName('files_external:applicable')
			->setDescription('Manage applicable users and groups for a mount')
			->addArgument(
				'mount_id',
				InputArgument::REQUIRED,
				'The id of the mount to edit'
			)->addArgument(
				'type',
				InputArgument::REQUIRED,
				'The type of applicable to manage ("groups" or "users")'
			)->addOption(
				'add',
				'a',
				InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
				'users or groups to add as applicable'
			)->addOption(
				'remove',
				'r',
				InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
				'users or groups to remove as applicable'
			);
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$mountId = $input->getArgument('mount_id');
		$type = $input->getArgument('type');
		try {
			$mount = $this->globalService->getStorage($mountId);
		} catch (NotFoundException $e) {
			$output->writeln('<error>Mount with id "' . $mountId . ' not found, check "occ files_external:list" to get available mounts"</error>');
			return 404;
		}

		if ($type !== 'users' && $type !== 'groups') {
			$output->writeln('<error>Invalid applicable type "' . $type . '"</error>');
			return 1;
		}

		$add = $input->getOption('add');
		$remove = $input->getOption('remove');

		if ($type === 'users') {
			$applicables = $mount->getApplicableUsers();
		} else {
			$applicables = $mount->getApplicableGroups();
		}

		if ($add || $remove) {
			$applicables = array_unique(array_merge($applicables, $add));
			$applicables = array_values(array_diff($applicables, $remove));
			if ($type === 'users') {
				$mount->setApplicableUsers($applicables);
			} else {
				$mount->setApplicableGroups($applicables);
			}
			$this->globalService->updateStorage($mount);
		}

		$this->writeArrayInOutputFormat($input, $output, $applicables);
	}
}
