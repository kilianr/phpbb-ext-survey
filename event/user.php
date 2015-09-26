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
class user implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.delete_user_before' => 'delete_user',
			'core.delete_group_after' => 'delete_group',
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
	 * Delete all entries of the users in all existing surveys
	 *
	 * @param object $event The event object
	 * @access public
	 */
	public function delete_user($event)
	{
		$this->survey->delete_user($event['mode'], $event['user_ids'], $event['retain_username']);
	}

	/**
	 * Delete all groupaccess entries of the group in all existing surveys
	 *
	 * @param object $event The event object
	 * @access public
	 */
	public function delete_group($event)
	{
		$this->survey->delete_group($event['group_id']);
	}
}
