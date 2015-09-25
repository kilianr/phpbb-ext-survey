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

class posting implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.submit_post_modify_sql_data'	=> 'submit_post_modify_sql_data',
			'core.submit_post_end'				=> 'submit_post_end',
			'core.posting_modify_template_vars'	=> 'posting_display_template',
		);
	}

	/** @var \kilianr\survey\functions\survey */
	protected $survey;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	public function __construct(\kilianr\survey\functions\survey $survey, \phpbb\template\template $template, \phpbb\user $user, \phpbb\request\request_interface $request)
	{
		$this->survey = $survey;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
	}

	/**
	 * Stores the survey data given in posting.php if necessary.
	 *
	 * @param unknown $event
	 */
	public function submit_post_modify_sql_data($event)
	{
		if (!$this->survey->can_access($event['data']['forum_id']))
		{
			return;
		}

		// We store only if we are creating a new topic or editing the first post of an existing one
		if (($event['post_mode'] != 'post' && $event['post_mode'] != 'edit_topic' && $event['post_mode'] != 'edit_first_post'))
		{
			return;
		}

		$sql_data = $event['sql_data'];
		$survey_enabled = $this->request->is_set_post('survey_enabled');

		$sql_data[TOPICS_TABLE]['sql'] = array_merge($sql_data[TOPICS_TABLE]['sql'], array('survey_enabled' => $survey_enabled,));
		$event['sql_data'] = $sql_data;
	}

	/**
	 * Initialized the survey data if necessary.
	 *
	 * @param unknown $event
	 */
	public function submit_post_end($event)
	{
		if (!$this->survey->can_access($event['data']['forum_id']))
		{
			return;
		}

		if ($this->request->is_set_post('survey_enabled') && ($event['mode'] == 'post' || ($event['mode'] == 'edit' && $event['data']['topic_first_post_id'] == $event['data']['post_id'] && $this->survey->is_enabled($event['data']['topic_id']))))
		{
			$this->survey->initialize($event['data']['topic_id']);
		}
	}

	/**
	 * Displays survey data in posting.php
	 *
	 * @param unknown $event
	 */
	public function posting_display_template($event)
	{
		if (!$this->survey->can_access($event['forum_id']))
		{
			return;
		}

		// Check for first post
		if (isset($event['post_data']['topic_first_post_id']) && (!isset($event['post_data']['post_id']) || $event['post_data']['topic_first_post_id'] != $event['post_data']['post_id']))
		{
			return;
		}

		$this->user->add_lang_ext('kilianr/survey', 'survey');

		if (isset($event['topic_id']) && $event['topic_id'])
		{
			$this->survey->load_survey($event['topic_id']);
		}

		$is_inactive = !((empty($this->survey->entries) && empty($this->survey->questions)) || $this->survey->enabled);

		$this->template->assign_vars(array(
			'S_SURVEY_ALLOWED'				=> true,
			'S_TOPIC_HAS_SURVEY'			=> $this->survey->enabled,
			'S_TOPIC_HAS_INACTIVE_SURVEY'	=> $is_inactive,
			'S_SURVEY_CHECKED'				=> $this->survey->enabled ? "checked='checked'" : '',
		));
	}
}
