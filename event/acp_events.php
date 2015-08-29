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
class acp_events implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions' => 'add_permissions',
		);
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	* Add permissions for setting topic based posts per page settings.
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_permissions($event)
	{
		$event['permissions'] = array_merge($event['permissions'], array(
			// Forum perms
			'f_survey'	=> array('lang' => 'ACL_F_SURVEY', 'cat' => 'content'),
		));
	}
}
