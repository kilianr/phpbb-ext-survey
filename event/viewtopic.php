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

class viewtopic implements EventSubscriberInterface
{

	/**
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_assign_template_vars_before'	=> 'show_survey_viewtopic',
		);
	}

	/** @var \kilianr\survey\functions\survey */
	protected $survey;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $phpEx;

	/** @var string */
	protected $survey_path;

	/** @var string */
	protected $action_name;

	/**
	 * Constructor
	 *
	 * @param \kilianr\survey\functions\survey $survey
	 * @param \phpbb\template\template $template
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\user $user
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\request\request_interface $request
	 * @param string $phpbb_root_path
	 * @param string $phpEx
	 * @param string $survey_path
	 */
	function __construct(\kilianr\survey\functions\survey $survey, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\request\request_interface $request, $phpbb_root_path, $phpEx, $survey_path)
	{
		$this->survey			= $survey;
		$this->template			= $template;
		$this->db				= $db;
		$this->user				= $user;
		$this->auth				= $auth;
		$this->request			= $request;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->phpEx			= $phpEx;
		$this->survey_path		= $survey_path;
		$this->action_name		= 'survey_action';
		$this->form_key			= 'survey_form_key';
	}

	/**
	 * Process all survey stuff in viewtopic
	 *
	 * @param \phpbb\event\data $event
	 */
	public function show_survey_viewtopic($event)
	{
		// Check auth
		if (!$this->auth->acl_get('f_survey', $event['forum_id']) && !$this->auth->acl_get('m_edit', $event['forum_id']))
		{
			return;
		}

		$topic_id = $event['topic_id'];
		if (!$this->survey->load_survey($topic_id, $event['forum_id']))
		{
			// No survey for this topic
			return;
		}

		// Load Language file
		$this->user->add_lang_ext('kilianr/survey', 'survey');

		// Now process all submits, if any

		$survey_errors = $this->process_submit($event);

		// If the survey is disabled, then return now (also if we just disabled it)
		if (!$this->survey->survey_enabled)
		{
			return;
		}

		// Some frequently used data:
		$forum_id = $event['forum_id'];
		$user_id = $this->user->data['user_id'];
		$is_owner  = $event['topic_data']['topic_poster'] == $user_id || $this->auth->acl_gets('f_edit', 'm_edit', $event['forum_id']);
		$is_member = $this->survey->is_participating($user_id);
		$viewtopic_url = append_sid("{$this->phpbb_root_path}viewtopic.{$this->phpEx}?f=$forum_id&t=$topic_id");
		$action_url = $viewtopic_url . '&amp;' . $this->action_name . '=';
		$can_add_new_entry = $this->survey->can_add_new_entry($user_id);

		if (count($this->survey->survey_questions) == 0)
		{
			$survey_errors[] = $this->user->lang['SURVEY_NO_QUESTIONS'];
		}
		if (count($this->survey->survey_entries) == 0)
		{
			$survey_errors[] = $this->user->lang['SURVEY_NO_ENTRIES'];
		}

		$template_vars = array(
			'S_HAS_SURVEY'					=> true,
			'S_IS_SURVEY_OWNER'				=> $is_owner,
			'S_IS_SURVEY_MEMBER'			=> $is_member,
			'S_HAS_QUESTIONS'				=> empty($this->survey->survey_questions) ? false : true,
			'S_HAS_ENTRIES'					=> empty($this->survey->survey_entries) ? false : true,
			'S_HAS_RIGHT_TO_PARTICIPATE'	=> $this->survey->has_right_to_participate($user_id),
			'S_CAN_ADD_ENTRY'				=> $can_add_new_entry,
			'S_CAN_MODIFY_OWN_ENTRY'		=> $this->survey->can_modify_entry($user_id),
			'S_SURVEY_ACTION'				=> $viewtopic_url,
			'S_SURVEY_ACTION_NAME'			=> $this->action_name,
			'U_FIND_USERNAME'				=> append_sid("{$this->phpbb_root_path}memberlist.{$this->phpEx}", 'mode=searchuser&amp;form=ucp&amp;field=usernames'),
			'UA_FIND_USERNAME'				=> append_sid("{$this->phpbb_root_path}memberlist.{$this->phpEx}", 'mode=searchuser&form=ucp&field=usernames', false),
			'SURVEY_ERRORS'					=> (count($survey_errors) > 0) ? implode('<br />', $survey_errors) : false,
			'S_ROOT_PATH'					=> $this->phpbb_root_path,
			'S_EXT_PATH'					=> $this->survey_path,
			'S_IS_CLOSED'					=> $this->survey->is_closed(),
			'U_CHANGE_OPEN'					=> $action_url . ($this->survey->is_closed() ? 'reopen' : 'close'),
		);
		foreach ($this->survey->settings as $key => $value)
		{
			$template_vars['S_SURVEY_' . strtoupper($key)] = $value;
		}
		$this->template->assign_vars($template_vars);

		// Output questions
		foreach ($this->survey->survey_questions as $question_id => $question)
		{
			$template_vars = array();
			foreach ($question as $key => $value)
			{
				if ($key != 'choices')
				{
					$template_vars[strtoupper($key)] = $value;
				}
			}
			$template_vars['DELETE_LINK'] =  $action_url . 'question_deletion&amp;question_to_delete=' . $question_id;
			$this->template->assign_block_vars('questions', $template_vars);
			foreach ($question['choices'] as $choice)
			{
				$template_vars = array();
				foreach ($choice as $key => $value)
				{
					$template_vars[strtoupper($key)] = $value;
				}
				$this->template->assign_block_vars('questions.choices', $template_vars);
			}
		}

		// Fetch User details
		$user_details = array();
		foreach ($this->survey->survey_entries as $entry)
		{
			$user_details[$entry['user_id']] = true;
		}
		$user_details[$user_id] = true;
		$sql = 'SELECT user_id, username, user_colour FROM ' . USERS_TABLE .
			' WHERE ' . $this->db->sql_in_set('user_id', array_keys($user_details));
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_details[$row['user_id']] = $row;
		}

		// Output entries
		$entries_modifyable = '';
		$can_see_or_add_entries = false;
		foreach (array_merge($this->survey->survey_entries, $can_add_new_entry ? array("new_entry") : array()) as $entry)
		{
			$template_vars = array();
			$last = false;
			if ($entry == "new_entry")
			{
				$last = true;
				$entry = array(
					'entry_id'	=> -1,
					'user_id'	=> $user_id,
					'answers'	=> array(),
				);
			}
			else if($entry['user_id'] != $user_id && $this->survey->settings['hide_results'] && !$is_owner)
			{
				continue;
			}
			$can_see_or_add_entries = true;
			foreach ($entry as $key => $value)
			{
				if ($key != 'answers')
				{
					$template_vars[strtoupper($key)] = $value;
				}
				if ($key == 'entry_id')
				{
					$template_vars['IS_NEW'] = ($value == -1) ? true : false;
					$template_vars['DELETE_LINK'] =  $action_url . 'own_entry_deletion&amp;entry_to_delete=' . $value;
				}
			}
			$uid = $entry['user_id'];
			$user_detail = $user_details[$uid];
			$user_detail['is_self'] = ($uid == $user_id);
			if ($uid == $user_id)
			{
				if ($entries_modifyable != '')
				{
					$entries_modifyable .= ',';
				}
				$entries_modifyable .= $entry['entry_id'];
			}
			$user_detail['username_full'] = get_username_string('full', $uid, $user_detail['username'], $user_detail['user_colour']);
			foreach ($user_detail as $key => $value)
			{
				$template_vars[strtoupper($key)] = $value;
			}
			$this->template->assign_block_vars('entries', $template_vars);
			foreach ($this->survey->survey_questions as $question_id => $question)
			{
				$template_vars = array();
				if (isset($entry['answers'][$question_id]))
				{
					$template_vars['VALUE'] = $entry['answers'][$question_id];
					$template_vars['IS_SET'] = true;
				}
				else
				{
					$template_vars['IS_SET'] = false;
				}
				/*foreach($question as $key => $value)
				{
					if ($key != 'choices')
					{
						$template_vars['QUESTION_' . strtoupper($key)] => $value;
					}
				}*/
				$template_vars['S_INPUT_NAME'] = 'answer_' . $entry['entry_id'] . '_' . $question_id;
				$this->template->assign_block_vars('entries.questions', $template_vars);
				/*foreach($question['choices'] as $choice)
				{
					$template_vars = array();
					foreach($choice as $key => $value)
					{
						$template_vars[strtoupper($key)] => $value;
					}
					$this->template->assign_block_vars('entries.questions.choices', $template_vars);
				}*/
			}
		}
		$this->template->assign_vars(array(
			'S_SURVEY_MODIFYABLE_ENTRIES'		=> $entries_modifyable,
			'S_SURVEY_CAN_SEE_OR_ADD_ENTRIES'	=> $can_see_or_add_entries,
		));
		add_form_key($this->form_key);
	}

	/**
	 * Process config change of survey
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_config_change($event)
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		$new_settings = array();
		foreach (array_diff_key($this->survey->settings, array('s_id' => 0, 'topic_id' => 0, 'start_time' => 0)) as $setting => $entry)
		{
			$new_settings[$setting] = $this->request->is_set_post('survey_setting_'. $setting) ? $this->request->variable('survey_setting_'. $setting, '') : 0;
		}
		foreach ($new_settings as $new_setting => $new_value)
		{
			$right_type = gettype($this->survey->settings[$new_setting]);
			if ($new_setting == 'stop_time' && $right_type == "NULL" && $new_value != '' && gettype($new_value) != "NULL")
			{
				$right_type = "integer";
			}
			else if ($new_setting == 'stop_time' && $right_type != "NULL" && ($new_value == '' || gettype($new_value) == "NULL"))
			{
				$right_type = "NULL";
				$new_settings[$new_setting] = null;
			}
			if ($right_type != gettype($new_value))
			{
				if (!settype($new_settings[$new_setting], $right_type))
				{
					return array($this->user->lang('FORM_INVALID'));
				}
			}
		}
		$this->survey->change_config($new_settings);
		return array();
	}

	/**
	 * Process close of survey
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_close($event)
	{
		if (confirm_box(true))
		{
			$this->survey->close();
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'			=> $event['topic_id'],
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_CLOSE_CONFIRM'], $s_hidden_fields);
		}
		return array();
	}

	/**
	 * Process reopen of survey
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_reopen($event)
	{
		if (confirm_box(true))
		{
			$this->survey->reopen();
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'			=> $event['topic_id'],
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_REOPEN_CONFIRM'], $s_hidden_fields);
		}
		return array();
	}

	/**
	 * Process deletion of own entry
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_own_entry_deletion($event)
	{
		$entry_id = (int) $this->request->variable('entry_to_delete', '');
		if (!$this->survey->entry_exists($entry_id))
		{
			return array();
		}
		if (!$this->survey->can_modify_entry($this->user->data['user_id'], $this->survey->survey_entries[$entry_id]['user_id']))
		{
			return array($this->user->lang('NO_AUTH_OPERATION'));
		}

		if (confirm_box(true))
		{
			$this->survey->delete_entry($entry_id);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'					=> $event['topic_id'],
				'entry_to_delete'	=> $entry_id,
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_ENTRY_DELETION_CONFIRM'], $s_hidden_fields);
		}
		return array();
	}

	/**
	 * Process modification of own entry
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_own_entry_modification($event)
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		if (!$this->request->is_set_post('entries_to_modify'))
		{
			return array();
		}
		$entry_ids = array_unique(explode(",", $this->request->variable('entries_to_modify', '')));
		$user_id = $this->user->data['user_id'];
		$errors = array();
		foreach ($entry_ids as $entry_id)
		{
			$entry_id = (int) $entry_id;
			if ($entry_id == -1 && !$this->survey->can_add_new_entry($user_id))
			{
				$errors = array_merge($errors, array($this->user->lang('NO_AUTH_OPERATION')));
				continue;
			}
			else if ($entry_id > -1 && !$this->survey->entry_exists($entry_id))
			{
				continue;
			}
			else if ($entry_id > -1 && !$this->survey->can_modify_entry($this->user->data['user_id'], $this->survey->survey_entries[$entry_id]['user_id']))
			{
				$errors = array_merge($errors, array($this->user->lang('NO_AUTH_OPERATION')));
				continue;
			}
			$answers = array();
			foreach ($this->survey->survey_questions as $question_id => $question)
			{
				if ($this->request->is_set('answer_' . $entry_id . '_'. $question_id))
				{
					$answers[$question_id] = $this->request->variable('answer_' . $entry_id . '_'. $question_id, '');
				}
			}
			if ($entry_id == -1)
			{
				$this->survey->add_entry($user_id, $answers);
			}
			else
			{
				$this->survey->modify_entry($entry_id, $answers);
			}
		}
		return $errors;
	}

	/**
	 * Process addition of other entry
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_other_entry_addition($event)
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		$this->survey->add_entry();
		return array();
	}

	/**
	 * Process deletion of other entry
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_other_entry_deletion($event)
	{
		$this->survey->delete_entry();
		return array();
	}

	/**
	 * Process modification of other entry
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_other_entry_modification($event)
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		$this->survey->modify_entry();
		return array();
	}

	/**
	 * Process addition of question
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_question_addition($event)
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		$question = array(
			'label'		=> null,
			'type'		=> 0,
			'sum_type'	=> 0,
			'sum_by'	=> '',
			'cap'		=> 0,
		);
		foreach ($question as $key => $value)
		{
			if ($this->request->is_set_post('question_'. $key))
			{
				$question[$key] = $this->request->variable('question_'. $key, '');
			}
			if ($question[$key] == '')
			{
				unset($question[$key]);
			}
		}
		$question = array_map('trim', $question);
		if (!isset($question['label']) || $question['label'] == '')
		{
			return array($this->user->lang('SURVEY_INVALID_QUESTION'));
		}
		if ($this->survey->get_question_id_from_label($question['label'], -1) != -1)
		{
			return array($this->user->lang('SURVEY_QUESTION_ALREADY_ADDED'));
		}
		if ($this->request->is_set('question_choices'))
		{
			$choices = array_unique(explode(",", $this->request->variable('question_choices', '')));
		}
		$choices = array_map('trim', $choices);
		$this->survey->add_question($question, $choices);
		return array();
	}

	/**
	 * Process deletion of question
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_question_deletion($event)
	{
		$question_id = (int) $this->request->variable('question_to_delete', '');
		if (!$this->survey->question_exists($question_id))
		{
			return array();
		}

		if (confirm_box(true))
		{
			$this->survey->delete_question($question_id);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'			=> $event['topic_id'],
				'question_to_delete'	=> $question_id,
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang('SURVEY_DELETE_QUESTION_CONFIRM', $this->survey->survey_questions[$question_id]['label']), $s_hidden_fields);
		}

		return array();
	}

	/**
	 * Process modification of question
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_question_modification($event)
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		$question_id = (int) $this->request->variable('question_to_modify', '');
		if (!$this->survey->question_exists($question_id))
		{
			return array();
		}
		$question = array(
			'label'		=> null,
			'type'		=> 0,
			'sum_type'	=> 0,
			'sum_by'	=> '',
			'cap'		=> 0,
		);
		if (!$this->request->is_set('question_label'))
		{
			return array($this->user->lang('SURVEY_INVALID_QUESTION'));
		}
		foreach ($question as $key => $value)
		{
			if ($this->request->is_set_post('question_'. $key))
			{
				$question[$key] = $this->request->variable('question_'. $key, '');
			}
		}
		$question = array_map('trim', $question);
		if ($question['label'] == '')
		{
			return array($this->user->lang('SURVEY_INVALID_QUESTION'));
		}
		if ($question_id != $this->survey->get_question_id_from_label($question['label'], $question_id))
		{
			return array($this->user->lang('SURVEY_QUESTION_ALREADY_ADDED'));
		}
		if ($this->request->is_set('question_choices'))
		{
			$choices = array_unique(explode(",", $this->request->variable('question_choices', '')));
		}
		$choices = array_map('trim', $choices);
		$this->survey->modifiy_question($question_id, $question, $choices);
		return array();
	}

	/**
	 * Process disable
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_disable($event)
	{
		if (confirm_box(true))
		{
			$this->survey->disable();
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'					=> $event['topic_id'],
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_DISABLE_CONFIRM'], $s_hidden_fields);
		}

		return array();
	}

	/**
	 * Process delete
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_delete($event)
	{
		if (confirm_box(true))
		{
			$this->survey->delete($event['topic_id']);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'					=> $event['topic_id'],
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_DELETE_ALL_CONFIRM'], $s_hidden_fields);
		}

		return array();
	}

	/**
	 * Processes all actions
	 *
	 * @param \phpbb\event\data $event
	 * @return array errors
	 */
	protected function process_submit($event)
	{
		if (!$this->request->is_set($this->action_name))
		{
			return array();
		}
		$action = $this->request->variable($this->action_name, '');

		if (!$this->survey->survey_enabled)
		{
			return array($this->user->lang('SURVEY_IS_DISABLED'));
		}

		$is_owner  = $event['topic_data']['topic_poster'] == $this->user->data['user_id'] || $this->auth->acl_get('m_edit', $event['forum_id']);

		if (!$is_owner && preg_match("/^(config_change|close|reopen|other_entry_addition|other_entry_deletion|other_entry_modification|question_addition|question_deletion|question_modification|delete|disable)$/", $action))
		{
			return array($this->user->lang('NO_AUTH_OPERATION'));
		}

		if ($this->survey->is_closed() && !$is_owner)
		{
			return array($this->user->lang('SURVEY_IS_CLOSED'));
		}

		if ($action == "config_change")
		{
			return $this->process_config_change($event);
		}

		if ($action == "reopen")
		{
			if (!$this->survey->is_closed())
			{
				return array($this->user->lang('SURVEY_IS_NOT_CLOSED'));
			}
			return $this->process_reopen($event);
		}

		if ($this->survey->is_closed())
		{
			return array($this->user->lang('SURVEY_IS_CLOSED'));
		}

		if ($action == "close")
		{
			return $this->process_close($event);
		}

		if ($action == "own_entry_deletion")
		{
			return $this->process_own_entry_deletion($event);
		}

		if ($action == "own_entry_modification")
		{
			return $this->process_own_entry_modification($event);
		}

		if ($action == "other_entry_addition")
		{
			return $this->process_other_entry_addition($event);
		}

		if ($action == "other_entry_deletion")
		{
			return $this->process_other_entry_deletion($event);
		}

		if ($action == "other_entry_modification")
		{
			return $this->process_other_entry_modification($event);
		}

		if ($action == "question_addition")
		{
			return $this->process_question_addition($event);
		}

		if ($action == "question_deletion")
		{
			return $this->process_question_deletion($event);
		}

		if ($action == "question_modification")
		{
			return $this->process_question_modification($event);
		}

		if ($action == "disable")
		{
			return $this->process_disable($event);
		}

		if ($action == "delete")
		{
			return $this->process_delete($event);
		}

		return array();
	}
}
