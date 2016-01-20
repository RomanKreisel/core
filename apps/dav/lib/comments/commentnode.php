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
use Sabre\DAV\PropPatch;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class CommentNode implements \Sabre\DAV\INode, XmlSerializable,\Sabre\DAV\IProperties {
	const NS_OWNCLOUD = 'http://owncloud.org/ns';

	/** @var ICommentsManager */
	protected $commentsManager;

	/** @var  IComment */
	public $comment;

	/** @var array list of properties with key being their name and value their setter */
	protected $properties = [];

	public function __construct(ICommentsManager $commentsManager, IComment $comment) {
		$this->commentsManager = $commentsManager;
		$this->comment = $comment;

		$methods = get_class_methods($this->comment);
		$methods = array_filter($methods, function($name){
			return strpos($name, 'get') === 0;
		});
		foreach($methods as $getter) {
			$name = '{'.self::NS_OWNCLOUD.'}' . lcfirst(substr($getter, 3));
			$this->properties[$name] = $getter;
		}
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

	/**
	 * The xmlSerialize method is called during xml writing.
	 *
	 * Use the $writer argument to write its own xml serialization.
	 *
	 * An important note: do _not_ create a parent element. Any element
	 * implementing XmlSerializble should only ever write what's considered
	 * its 'inner xml'.
	 *
	 * The parent of the current element is responsible for writing a
	 * containing element.
	 *
	 * This allows serializers to be re-used for different element names.
	 *
	 * If you are opening new elements, you must also close them again.
	 *
	 * @param Writer $writer
	 * @return void
	 */
	function xmlSerialize(Writer $writer) {
		$ns = '{' . self::NS_OWNCLOUD . '}';
		$writer->startElement($ns . 'comment');

		//$writer->writeElement($ns . ':href', );	TODO: determine and return URL to this comment
		$writer->writeElement($ns . 'id', $this->comment->getId());
		$writer->writeElement($ns . 'parentId', $this->comment->getParentId());
		$writer->writeElement($ns . 'topmostParentId', $this->comment->getTopmostParentId());
		$writer->writeElement($ns . 'childrenCount', $this->comment->getChildrenCount());
		$writer->writeElement($ns . 'message', $this->comment->getMessage());
		$writer->writeElement($ns . 'verb', $this->comment->getVerb());
		$writer->writeElement($ns . 'actorType', $this->comment->getActorType());
		$writer->writeElement($ns . 'actorId', $this->comment->getActorId());
		$writer->writeElement($ns . 'objectType', $this->comment->getObjectType());
		$writer->writeElement($ns . 'objectId', $this->comment->getObjectId());
		$writer->writeElement($ns . 'creationDateTime', $this->comment->getCreationDateTime()->format('Y-m-d H:m:i'));
		$writer->writeElement($ns . 'latestChildDateTime', $this->comment->getLatestChildDateTime()->format('Y-m-d H:m:i'));

		$writer->endElement();
	}

	/**
	 * Updates properties on this node.
	 *
	 * This method received a PropPatch object, which contains all the
	 * information about the update.
	 *
	 * To update specific properties, call the 'handle' method on this object.
	 * Read the PropPatch documentation for more information.
	 *
	 * @param PropPatch $propPatch
	 * @return void
	 */
	function propPatch(PropPatch $propPatch) {
		// TODO: Implement propPatch() method.
	}

	/**
	 * Returns a list of properties for this nodes.
	 *
	 * The properties list is a list of propertynames the client requested,
	 * encoded in clark-notation {xmlnamespace}tagname
	 *
	 * If the array is empty, it means 'all properties' were requested.
	 *
	 * Note that it's fine to liberally give properties back, instead of
	 * conforming to the list of requested properties.
	 * The Server class will filter out the extra.
	 *
	 * @param array $properties
	 * @return array
	 */
	function getProperties($properties) {
		$ns = '{' . self::NS_OWNCLOUD . '}';

		//pre-check.
		//when requiring all properties, file-related stuff ends up here for
		//some reason.
		$properties = array_filter($properties, function($property) {
			return isset($this->properties[$property]);
		});

		// BUG/FIXME? If <D:allprop/> are requested from the client, file
		// related properties will end up in the list. For whatever reason.
		// Ignoring them and returning everything does not turn out to work.
		if(count($properties) === 0) {
			$properties = array_keys($this->properties);
		}

		$result = [];
		foreach($properties as $property) {
			$getter = $this->properties[$property];
			if(method_exists($this->comment, $getter)) {
				$result[$property] = $this->comment->$getter();
			}

		}
		return $result;
	}
}
