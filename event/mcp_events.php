<?php
/**
 *
 * @package survey
 * @copyright (c) 2015 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class mcp_events implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.delete_topics_after_query' => 'delete_topic',
		);
	}

	/** @var \kilianr\survey\functions\survey */
	protected $survey;

	/**
	 * Constructor
	 */
	public function __construct(\kilianr\survey\functions\survey $survey)
	{
		$this->survey = $survey;
	}

	/**
	* Delete the survey which belongs to the topic by deleting all parts.
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function delete_topic($event)
	{
		$this->survey->delete($event['topic_ids'], true);
	}
}
