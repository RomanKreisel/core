<?php
/**
 * @author Arthur Schiwon <blizzz@owncloud.com>
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

namespace OCA\DAV\Comments;


use OCP\Comments\IComment;
use OCP\Comments\ICommentsManager;
use Sabre\DAV\Exception\MethodNotAllowed;

class CommentNode implements \Sabre\DAV\INode {

	/** @var ICommentsManager */
	protected $commentsManager;

	/** @var  IComment */
	protected $comment;

	public function __construct(ICommentsManager $commentsManager, IComment $comment) {
		$this->commentsManager = $commentsManager;
		$this->comment = $comment;
	}

	/**
	 * Deleted the current node
	 *
	 * @return void
	 */
	function delete() {
		$this->commentsManager->delete($this->comment->getId());
	}

	/**
	 * Returns the name of the node.
	 *
	 * This is used to generate the url.
	 *
	 * @return string
	 */
	function getName() {
		return $this->comment->getId();
	}

	/**
	 * Renames the node
	 *
	 * @param string $name The new name
	 * @throws MethodNotAllowed
	 */
	function setName($name) {
		throw new MethodNotAllowed();
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	function getLastModified() {
		// FIXME: Figure out whether this can be an issue with comment updates
		return $this->comment->getCreationDateTime();
	}
}
