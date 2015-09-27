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
use kilianr\survey\functions\survey;

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

	/** @var string */
	protected $question_to_load;

	/** @var string */
	protected $base_url;

	/** @var int */
	protected $topic_id;

	const ADDUSER_ENTRY_ID = "adduser";
	const NEW_ENTRY_ID = -1;
	const NEW_QUESTION_ID = -1;

	/**
	 * Constructor
	 *
	 * @param \kilianr\survey\functions\survey $survey
	 * @param \phpbb\template\template $template
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\user $user
	 * @param \phpbb\request\request_interface $request
	 * @param string $phpbb_root_path
	 * @param string $phpEx
	 * @param string $survey_path
	 */
	public function __construct(\kilianr\survey\functions\survey $survey, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\request\request_interface $request, $phpbb_root_path, $phpEx, $survey_path)
	{
		$this->survey			= $survey;
		$this->template			= $template;
		$this->db				= $db;
		$this->user				= $user;
		$this->request			= $request;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->phpEx			= $phpEx;
		$this->survey_path		= $survey_path;
		$this->action_name		= 'survey_action';
		$this->form_key			= 'survey_form_key';
		$this->question_to_load	= false;
	}

	/**
	 * Process all survey stuff in viewtopic
	 *
	 * @param \phpbb\event\data $event
	 */
	public function show_survey_viewtopic($event)
	{
		$forum_id = $event['forum_id'];
		$this->base_url = $event['base_url'];
		$this->topic_id = $event['topic_id'];
		$this->survey->set_env($forum_id, $event['topic_data']['topic_poster'], $event['topic_data']['topic_status'], $event['topic_data']['forum_status']);
		if (!$this->survey->load_survey($this->topic_id))
		{
			// No survey for this topic
			return;
		}

		// Load Language file
		$this->user->add_lang_ext('kilianr/survey', 'survey');

		// Now process all submits, if any
		$survey_errors = $this->process_submit($event);

		// If the survey is disabled, then return now (also if we just disabled it)
		if (!$this->survey->enabled)
		{
			return;
		}

		// Some frequently used data:
		$user_id = $this->user->data['user_id'];
		$is_read_owner  = $this->survey->is_read_owner($user_id);
		$is_write_owner  = $this->survey->is_write_owner($user_id);
		$is_member = $this->survey->is_participating($user_id);
		$is_closed = $this->survey->is_closed();
		$viewtopic_url = append_sid("{$this->phpbb_root_path}viewtopic.{$this->phpEx}?f=$forum_id&t={$this->topic_id}");
		$action_url = "{$viewtopic_url}&amp;{$this->action_name}=";
		$can_add_new_entry = $this->survey->can_add_new_entry($user_id);

		if (empty($this->survey->questions))
		{
			$survey_errors[] = $this->user->lang['SURVEY_NO_QUESTIONS'];
		}
		if (empty($this->survey->entries))
		{
			$survey_errors[] = $this->user->lang['SURVEY_NO_ENTRIES'];
		}

		if ($is_closed)
		{
			$this->template->assign_var('S_IS_CLOSED_DESC', $this->user->lang('SURVEY_IS_CLOSED' . ($is_write_owner ? '_DESC_OWNER' : ''), $this->user->format_date($this->survey->settings['stop_time'])));
		}
		else if ($this->survey->settings['stop_time'])
		{
			$this->template->assign_var('S_WILL_CLOSE_DESC', $this->user->lang('SURVEY_DESC_STOP', $this->user->format_date($this->survey->settings['stop_time'])));
		}

		// Output settings
		$template_vars = array();
		foreach ($this->survey->settings as $key => $value)
		{
			if ($key == 'start_time' || ($key == 'stop_time' && $value != ''))
			{
				$value = $this->user->format_date($value, $this->user->lang['SURVEY_DATEFORMAT']);
			}
			$template_vars['S_SURVEY_' . strtoupper($key)] = $value;
		}
		$this->template->assign_vars($template_vars);

		// Output show_order
		foreach (survey::$SHOW_ORDER_TYPES as $type)
		{
			$template_vars = array(
				'NUM'		=> $type,
				'SELECTED'	=> ($this->survey->settings['show_order'] == $type) ? true : false,
				'DESC'		=> $this->user->lang('SURVEY_SHOW_ORDER_DESC_' . $type),
			);
			$this->template->assign_block_vars('show_order', $template_vars);
		}

		// Output visibility types
		foreach (survey::$VISIBILITY_TYPES as $type)
		{
			$template_vars = array(
				'NUM'		=> $type,
				'SELECTED'	=> ($this->survey->settings['visibility'] == $type) ? true : false,
				'DESC'		=> $this->user->lang('SURVEY_VISIBILITY_DESC_' . $type),
			);
			$this->template->assign_block_vars('visibility', $template_vars);
		}

		// Output topic poster rights
		foreach (survey::$TOPIC_POSTER_RIGHTS as $right)
		{
			$template_vars = array(
				'NUM'		=> $right,
				'SELECTED'	=> ($this->survey->settings['topic_poster_right'] == $right) ? true : false,
				'DESC'		=> $this->user->lang('SURVEY_TOPIC_POSTER_RIGHT_DESC_' . $right),
			);
			$this->template->assign_block_vars('topic_poster_right', $template_vars);
		}

		// Output question types
		foreach (survey::$QUESTION_TYPES as $type)
		{
			$template_vars = array(
				'NUM'		=> $type,
				'SELECTED'	=> ($this->question_to_load !== false && $this->survey->questions[$this->question_to_load]['type'] == $type ? true : false),
				'DESC'		=> $this->user->lang('SURVEY_QUESTION_TYPE_DESC_' . $type),
			);
			$this->template->assign_block_vars('question_type', $template_vars);
		}

		// Output question sum types
		foreach (survey::$QUESTION_SUM_TYPES as $type)
		{
			$template_vars = array(
				'NUM'		=> $type,
				'SELECTED'	=> ($this->question_to_load !== false && $this->survey->questions[$this->question_to_load]['sum_type'] == $type ? true : false),
				'DESC'		=> $this->user->lang('SURVEY_QUESTION_SUM_TYPE_DESC_' . $type),
			);
			$this->template->assign_block_vars('question_sum_type', $template_vars);
		}

		// Output questions
		$entry_count = $this->survey->get_entry_count();
		$can_see_sums = false;
		$can_see_averages = false;
		$some_cap_set = false;
		foreach ($this->survey->questions as $question_id => $question)
		{
			$template_vars = array();
			foreach ($question as $key => $value)
			{
				if ($key != 'choices')
				{
					$template_vars[strtoupper($key)] = $value;
				}
			}
			$template_vars['LOADED'] = ($this->question_to_load !== false && $this->question_to_load == $question_id ? true : false);
			$template_vars['DELETE_LINK'] =  $action_url . 'question_deletion&amp;question_to_delete=' . $question_id;
			$template_vars['SUM_STRING'] = $this->survey->get_sum_string($question_id);
			$template_vars['AVERAGE_STRING'] = $this->survey->get_average_string($question_id, $entry_count);
			$template_vars['CAP_REACHED'] = $this->survey->cap_reached($question_id);
			$template_vars['HAS_CHOICES'] = !empty($question['choices']);
			if ($template_vars['SUM_STRING'] != '')
			{
				$can_see_sums = true;
			}
			if ($template_vars['AVERAGE_STRING'] != '')
			{
				$can_see_averages = true;
			}
			if ($this->survey->has_cap($question_id))
			{
				$some_cap_set = true;
			}
			$this->template->assign_block_vars('questions', $template_vars);
			foreach ($question['choices'] as $choice)
			{
				$can_see_sums = true;
				$template_vars = array();
				foreach ($choice as $key => $value)
				{
					$template_vars[strtoupper($key)] = $value;
				}
				$this->template->assign_block_vars('questions.choices', $template_vars);
			}
		}
		if ($entry_count == 0 || ($this->survey->hide_everything() && !$is_read_owner))
		{
			$can_see_sums = $can_see_averages = false;
		}

		// Fetch User details
		$user_details = array();
		$anonymous = false;
		foreach ($this->survey->entries as $entry)
		{
			if ($entry['user_id'] != ANONYMOUS)
			{
				$user_details[$entry['user_id']] = true;
			}
			else
			{
				$anonymous = true;
			}
		}
		$user_details[$user_id] = true;
		$sql = 'SELECT user_id, username, user_colour FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_in_set('user_id', array_keys($user_details));
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_details[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		// Output entries
		$entries_modifyable = array();
		$can_see_or_add_entries = false;
		$entries_to_assign = array();
		$extra_rows = array();
		$adduser_entry_template_vars = array();
		$questions = $this->survey->questions;
		foreach ($questions as $question_id => $question)
		{
			if ($question['random_choice_order'])
			{
				$choice_ids = array_keys($question['choices']);
				shuffle($choice_ids);
				$randomized_choices = array();
				foreach ($choice_ids as $choice_id)
				{
					$randomized_choices[$choice_id] = $question['choices'][$choice_id];
				}
				$questions[$question_id]['choices'] = $randomized_choices;
			}
		}
		if ($can_add_new_entry)
		{
			$extra_rows[] = self::NEW_ENTRY_ID;
		}
		if ($is_write_owner)
		{
			$extra_rows[] = self::ADDUSER_ENTRY_ID;
		}
		foreach (array_merge($this->survey->entries, $extra_rows) as $entry)
		{
			$template_vars = array();
			if ($entry == self::ADDUSER_ENTRY_ID)
			{
				$entry = array(
					'entry_id'	=> self::ADDUSER_ENTRY_ID,
					'answers'	=> array(),
				);
			}
			else if ($entry == self::NEW_ENTRY_ID)
			{
				$entry = array(
					'entry_id'	=> self::NEW_ENTRY_ID,
					'user_id'	=> $user_id,
					'answers'	=> array(),
				);
			}
			else if ($entry['user_id'] != $user_id && $this->survey->hide_entries() && !$is_read_owner)
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
					$template_vars['IS_ADDUSER'] = ($value == self::ADDUSER_ENTRY_ID) ? true : false;
					$template_vars['IS_NEW'] = ($value == self::NEW_ENTRY_ID) ? true : false;
					$template_vars['DELETE_LINK'] =  "{$action_url}entry_deletion&amp;entry_to_delete=$value";
				}
			}
			if ($entry['entry_id'] != self::ADDUSER_ENTRY_ID)
			{
				$uid = $entry['user_id'];
				$user_detail = array();
				if ($uid != ANONYMOUS)
				{
					$user_detail = $user_details[$uid];
					$user_detail['is_self'] = ($uid == $user_id);
					if ($uid == $user_id || $is_write_owner)
					{
						$entries_modifyable[] = $entry['entry_id'];
					}
					$user_detail['username_full'] = get_username_string('full', $uid, $user_detail['username'], $user_detail['user_colour']);
				}
				else
				{
					$user_detail['is_self'] = false;
					if ($is_write_owner)
					{
						$entries_modifyable[] = $entry['entry_id'];
					}
					$user_detail['username'] = $user_detail['username_full'] = ($entry['entry_username'] != '' ? $entry['entry_username'] : $this->user->lang['GUEST']);
				}
				foreach ($user_detail as $key => $value)
				{
					$template_vars[strtoupper($key)] = $value;
				}
			}
			else
			{
				$template_vars['IS_SELF'] = false;
				$entries_modifyable[] = $entry['entry_id'];
			}
			$questions_to_assign = array();
			$is_first_question = true;
			foreach ($questions as $question_id => $question)
			{
				$template_vars_question = array();
				if (isset($entry['answers'][$question_id]))
				{
					$template_vars_question['VALUE'] = $entry['answers'][$question_id];
					$template_vars_question['IS_SET'] = true;
				}
				else
				{
					$template_vars_questions['IS_SET'] = false;
				}
				if ($is_first_question)
				{
					$is_first_question = false;
					$template_vars['first_answer_text'] = isset($entry['answers'][$question_id]) ? $entry['answers'][$question_id] : '';
				}
				$template_vars_question['S_INPUT_NAME'] = "answer_{$entry['entry_id']}_$question_id" . ($question['type'] == survey::$QUESTION_TYPES['MULTIPLE_CHOICE'] ? '[]' : '');
				$template_vars_question['TYPE_STRING'] = array_search($question['type'], survey::$QUESTION_TYPES);
				$template_vars_question['CAP_EXEEDED'] = $this->survey->cap_exceeded($question_id);
				$template_vars_question['SELECT_MULTIPLE_HEIGHT'] = min(4, sizeof($question['choices']));
				$choices_to_assign = array();
				if (isset($entry['answers'][$question_id]))
				{
					$exploded_answers = explode(",", $entry['answers'][$question_id]);
				}
				foreach ($question['choices'] as $choice)
				{
					$template_vars_choices = array();
					foreach ($choice as $key => $value)
					{
						$template_vars_choices[strtoupper($key)] = $value;
					}
					$template_vars_choices['SELECTED'] = (isset($entry['answers'][$question_id]) && in_array($choice['text'], $exploded_answers) ? ' selected="selected"' : '');
					$choices_to_assign[] = $template_vars_choices;
				}
				$template_vars_question['choices'] = $choices_to_assign;
				$questions_to_assign[] = $template_vars_question;
			}
			$template_vars['questions'] = $questions_to_assign;
			if ($entry['entry_id'] != self::ADDUSER_ENTRY_ID)
			{
				$entries_to_assign[] = $template_vars;
			}
			else
			{
				$adduser_entry_template_vars = $template_vars;
			}
		}
		$sort_order = SORT_ASC;
		switch ($this->survey->settings['show_order'])
		{
			case survey::$SHOW_ORDER_TYPES['ALPHABETICAL_USERNAME']:
				$sort_by = 'USERNAME';
			break;
			case survey::$SHOW_ORDER_TYPES['ALPHABETICAL_FIRST_ANSWER']:
				$sort_by = 'first_answer_text';
			break;
			case survey::$SHOW_ORDER_TYPES['ALPHABETICAL_FIRST_ANSWER_REVERSE']:
				$sort_by = 'first_answer_text';
				$sort_order = SORT_DESC;
			break;
			default:
				$sort_by = false;
			break;
		}
		if ($sort_by && !empty($this->survey->entries))
		{
			$only_sorting_row = array();
			foreach ($entries_to_assign as $key => $row)
			{
				$only_sorting_row[$key] = $row[$sort_by];
			}
			array_multisort($only_sorting_row, $sort_order, $entries_to_assign);
		}
		if (!empty($adduser_entry_template_vars))
		{
			$entries_to_assign[] = $adduser_entry_template_vars;
		}
		foreach ($entries_to_assign as $entry_to_assign)
		{
			if (isset($entry_to_assign['first_answer_text']))
			{
				unset($entry_to_assign['first_answer_text']);
			}
			$questions_to_assign = $entry_to_assign['questions'];
			unset($entry_to_assign['questions']);
			$this->template->assign_block_vars('entries', $entry_to_assign);
			foreach ($questions_to_assign as $question_to_assign)
			{
				$assign_choices = false;
				if (isset($question_to_assign['choices']))
				{
					$choices_to_assign = $question_to_assign['choices'];
					unset($question_to_assign['choices']);
					$assign_choices = true;
				}
				$this->template->assign_block_vars('entries.questions', $question_to_assign);
				if ($assign_choices)
				{
					foreach ($choices_to_assign as $choice_to_assign)
					{
						$this->template->assign_block_vars('entries.questions.choices', $choice_to_assign);
					}
				}
			}
		}

		if ($this->question_to_load !== false)
		{
			$template_vars = array();
			foreach ($this->survey->questions[$this->question_to_load] as $key => $value)
			{
				if ($key == 'cap')
				{
					$template_vars['S_SURVEY_LOADED_QUESTION_' . strtoupper($key)] = ($value != 0 ? $value : '');
				}
				else if ($key != 'choices')
				{
					$template_vars['S_SURVEY_LOADED_QUESTION_' . strtoupper($key)] = $value;
				}
			}
			$choices_to_load = array();
			foreach ($this->survey->questions[$this->question_to_load]['choices'] as $choice)
			{
				$choices_to_load[] = $choice['text'];
			}
			$template_vars['S_SURVEY_LOADED_QUESTION_CHOICES'] = implode(",", $choices_to_load);
			$this->template->assign_vars($template_vars);
		}

		$this->template->assign_vars(array(
			'S_HAS_SURVEY'						=> true,
			'S_SURVEY_IS_READ_OWNER'			=> $is_read_owner,
			'S_SURVEY_IS_WRITE_OWNER'			=> $is_write_owner,
			'S_SURVEY_IS_MODERATOR'				=> $this->survey->is_moderator(),
			'S_IS_SURVEY_MEMBER'				=> $is_member,
			'S_HAS_QUESTIONS'					=> empty($this->survey->questions) ? false : true,
			'S_HAS_ENTRIES'						=> empty($this->survey->entries) ? false : true,
			'S_SHOW_USERNAMES'					=> !$this->survey->is_anonymized() || $is_read_owner,
			'S_HIDE_ENTRIES'					=> $this->survey->hide_entries(),
			'S_HIDE_EVERYTHING'					=> $this->survey->hide_everything(),
			'S_CAN_ADD_ENTRY'					=> $can_add_new_entry,
			'S_CAN_MODIFY_OWN_ENTRY'			=> $this->survey->can_modify_entry($user_id),
			'S_SURVEY_ACTION'					=> $viewtopic_url,
			'S_SURVEY_ACTION_NAME'				=> $this->action_name,
			'U_FIND_USERNAME'					=> append_sid("{$this->phpbb_root_path}memberlist.{$this->phpEx}", 'mode=searchuser&amp;form=surveyform&amp;field=answer_adduser_username&amp;select_single=true'),
			'SURVEY_ERRORS'						=> (!empty($survey_errors)) ? implode('<br />', $survey_errors) : false,
			'S_ROOT_PATH'						=> $this->phpbb_root_path,
			'S_EXT_PATH'						=> $this->survey_path,
			'S_IS_CLOSED'						=> $is_closed,
			'U_CHANGE_OPEN'						=> $action_url . ($is_closed ? 'reopen' : 'close'),
			'S_DESC'							=> $this->user->lang('SURVEY_DESC', $this->user->format_date($this->survey->settings['start_time'])),
			'S_SURVEY_MODIFYABLE_ENTRIES'		=> implode(",", $entries_modifyable),
			'S_SURVEY_CAN_SEE_OR_ADD_ENTRIES'	=> $can_see_or_add_entries,
			'S_CAN_SEE_SUMS'					=> $can_see_sums,
			'S_CAN_SEE_AVERAGES'				=> $can_see_averages,
			'S_SOME_CAP_SET'					=> $some_cap_set,
			'S_SURVEY_LOADED_QUESTION'			=> ($this->question_to_load !== false ? true : false),
			'S_SURVEY_LOADED_QUESTION_ID'		=> $this->question_to_load,
		));
		add_form_key($this->form_key);
	}

	/**
	 * Process config change of survey
	 *
	 * @return array errors
	 */
	protected function process_config_change()
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		$new_settings = array(
			'caption'				=> '',
			'show_order'			=> 0,
			'allow_change_answer'	=> 0,
			'allow_multiple_answer'	=> 0,
			'visibility'			=> 0,
			'stop_time'				=> '',
		);
		if ($this->survey->is_moderator())
		{
			$new_settings['topic_poster_right'] = 0;
		}
		foreach ($new_settings as $setting => $default)
		{
			$new_settings[$setting] = $this->request->variable('survey_setting_'. $setting, $default, true);
		}
		if ($new_settings['caption'] == '')
		{
			return array($this->user->lang('SURVEY_INVALID_CAPTION'));
		}
		if (!in_array($new_settings['show_order'], survey::$SHOW_ORDER_TYPES))
		{
			return array($this->user->lang('SURVEY_INVALID_SHOW_ORDER_TYPE'));
		}
		$new_settings['allow_change_answer'] = ($new_settings['allow_change_answer'] ? 1 : 0);
		$new_settings['allow_multiple_answer'] = ($new_settings['allow_multiple_answer'] ? 1 : 0);
		if (!in_array($new_settings['visibility'], survey::$VISIBILITY_TYPES))
		{
			return array($this->user->lang('SURVEY_INVALID_VISIBILITY_TYPE'));
		}
		if ($new_settings['stop_time'] != '')
		{
			$orig_input = $new_settings['stop_time'];
			$new_settings['stop_time'] = $this->user->get_timestamp_from_format('Y-m-d H:i', $new_settings['stop_time']);
			if ($new_settings['stop_time'] === false || ($new_settings['stop_time']+60 < $this->survey->fixed_time() && $new_settings['stop_time'] != $this->survey->settings['stop_time']))
			{
				return array($this->user->lang('SURVEY_INVALID_STOPDATE', $orig_input));
			}
		}
		else
		{
			$new_settings['stop_time'] = null;
		}
		if ($this->survey->is_moderator())
		{
			if (!in_array($new_settings['topic_poster_right'], survey::$TOPIC_POSTER_RIGHTS))
			{
				return array($this->user->lang('SURVEY_INVALID_TOPIC_POSTER_RIGHT'));
			}
		}
		$this->survey->change_config($new_settings);
		return array();
	}

	/**
	 * Process close of survey
	 *
	 * @return array errors
	 */
	protected function process_close()
	{
		if (confirm_box(true))
		{
			$this->survey->close();
			redirect($this->base_url);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'					=> $this->topic_id,
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_CLOSE_CONFIRM'], $s_hidden_fields);
		}
		return array();
	}

	/**
	 * Process reopen of survey
	 *
	 * @return array errors
	 */
	protected function process_reopen()
	{
		if (confirm_box(true))
		{
			$this->survey->reopen();
			redirect($this->base_url);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'					=> $this->topic_id,
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_REOPEN_CONFIRM'], $s_hidden_fields);
		}
		return array();
	}

	/**
	 * Process deletion of own entry
	 *
	 * @return array errors
	 */
	protected function process_entry_deletion()
	{
		$entry_id = (int) $this->request->variable('entry_to_delete', '');
		if (!$this->survey->entry_exists($entry_id))
		{
			return array();
		}
		if (!$this->survey->can_modify_entry($this->user->data['user_id'], $this->survey->entries[$entry_id]['user_id']))
		{
			return array($this->user->lang('NO_AUTH_OPERATION'));
		}

		if (confirm_box(true))
		{
			$this->survey->delete_entry($entry_id);
			redirect($this->base_url);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'					=> $this->topic_id,
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
	 * @return array errors
	 */
	protected function process_entry_modification()
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
		$entry_user_id = $real_user_id = $this->user->data['user_id'];
		$errors = array();
		foreach ($entry_ids as $entry_id)
		{
			$changed = $filled_out = false;
			if ($entry_id == self::ADDUSER_ENTRY_ID)
			{
				$username = utf8_normalize_nfc(request_var('answer_adduser_username', '', true));
				if ($username == '')
				{
					continue;
				}
				$adduser_id = array();
				if (user_get_id_name($adduser_id, $username) == 'NO_USERS')
				{
					$errors[] = $this->user->lang('NO_USER');
					continue;
				}
				$entry_user_id = $adduser_id[0];
				if (!$this->survey->can_add_new_entry($real_user_id, $entry_user_id))
				{
					$errors[] = $this->user->lang('NO_AUTH_OPERATION');
					continue;
				}
			}
			else
			{
				$entry_id = (int) $entry_id;
				if ($entry_id == self::NEW_ENTRY_ID && !$this->survey->can_add_new_entry($real_user_id))
				{
					$errors[] = $this->user->lang('NO_AUTH_OPERATION');
					continue;
				}
				else if ($entry_id != self::NEW_ENTRY_ID && !$this->survey->entry_exists($entry_id))
				{
					continue;
				}
				else if ($entry_id != self::NEW_ENTRY_ID && !$this->survey->can_modify_entry($real_user_id, $this->survey->entries[$entry_id]['user_id']))
				{
					$errors[] = $this->user->lang('NO_AUTH_OPERATION');
					continue;
				}
			}
			$answers = array();
			$abort = false;
			foreach ($this->survey->questions as $question_id => $question)
			{
				$answers[$question_id] = $this->request->is_set_post("answer_{$entry_id}_$question_id") ? $this->request->variable("answer_{$entry_id}_$question_id", '', true) : '';
				if ($question['type'] == survey::$QUESTION_TYPES['DROP_DOWN_MENU'])
				{
					if (isset($question['choices'][$answers[$question_id]]))
					{
						$answers[$question_id] = $question['choices'][$answers[$question_id]]['text'];
					}
					else
					{
						$answers[$question_id] = '';
					}
				}
				else if ($question['type'] == survey::$QUESTION_TYPES['MULTIPLE_CHOICE'])
				{
					$answers_choice_array = array_unique($this->request->variable("answer_{$entry_id}_$question_id", array(0)));
					$answers[$question_id] = array();
					foreach ($answers_choice_array as $choice_id)
					{
						if (isset($question['choices'][$choice_id]))
						{
							$answers[$question_id][] = $question['choices'][$choice_id]['text'];
						}
					}
					$answers[$question_id] = implode(",", $answers[$question_id]);
				}
				$old_exists = $entry_id != self::ADDUSER_ENTRY_ID && $entry_id != self::NEW_ENTRY_ID && isset($this->survey->entries[$entry_id]['answers'][$question_id]);
				$old_value = ($old_exists ? $this->survey->entries[$entry_id]['answers'][$question_id] : 0);
				if ($answers[$question_id] != '')
				{
					if (!$this->survey->check_answer($answers[$question_id], $question_id))
					{
						$errors[] = $this->user->lang('SURVEY_INVALID_ANSWER');
						$abort = true;
						continue;
					}
					$filled_out = true;
					if ($this->survey->has_cap($question_id) && !$this->survey->is_write_owner($real_user_id))
					{
						$diff = $this->survey->modify_sum_entry($question_id, true, $answers[$question_id], $old_exists, $old_value);
						if ($diff != 0 && $this->survey->cap_exceeded($question_id, $diff))
						{
							$errors[] = $this->user->lang('SURVEY_CAP_EXEEDED', $this->survey->questions[$question_id]['label']);
							$abort = true;
							continue;
						}
					}
					if (!$old_exists || $old_value != $answers[$question_id])
					{
						$changed = true;
					}
				}
				else if ($old_exists && $old_value != '')
				{
					$changed = true;
				}
				print("\t\n");
			}
			if ($abort)
			{
				continue;
			}
			if ($filled_out)
			{
				if ($entry_id == self::ADDUSER_ENTRY_ID || $entry_id == self::NEW_ENTRY_ID)
				{
					$this->survey->add_entry($entry_user_id, $answers);
				}
				else if ($changed)
				{
					$this->survey->modify_entry($entry_id, $answers);
				}
			}
			else if ($entry_id != self::ADDUSER_ENTRY_ID && $entry_id != self::NEW_ENTRY_ID)
			{
				$this->survey->delete_entry($entry_id);
			}
		}
		return $errors;
	}

	/**
	 * Process addition or modification of question
	 *
	 * @return array errors
	 */
	protected function process_question_addition_or_modification()
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		$question_id = self::NEW_QUESTION_ID;
		if ($this->request->is_set_post('survey-submit-question-modify'))
		{
			$question_id = (int) $this->request->variable('question_to_modify', '');
			if (!$this->survey->question_exists($question_id))
			{
				return array();
			}
		}
		$question = array(
			'label'					=> '',
			'example_answer'		=> '',
			'type'					=> 0,
			'random_choice_order'	=> 0,
			'sum_type'				=> 0,
			'sum_by'				=> '',
			'average'				=> 0,
			'cap'					=> 0,
		);
		foreach ($question as $key => $value)
		{
			$question[$key] = $this->request->variable('question_'. $key, $question[$key], true);
		}
		$question = array_map('trim', $question);
		if ($question['label'] == '')
		{
			return array($this->user->lang('SURVEY_INVALID_QUESTION_NO_LABEL'));
		}
		if ($this->survey->get_question_id_from_label($question['label'], $question_id) != $question_id)
		{
			return array($this->user->lang('SURVEY_QUESTION_ALREADY_ADDED', $question['label']));
		}
		$question['random_choice_order'] = ($question['random_choice_order'] ? 1 : 0);
		$question['average'] = ($question['average'] ? 1 : 0);
		$question['cap'] = ($question['cap'] != '' ? $question['cap'] : 0);
		if (!in_array($question['type'], survey::$QUESTION_TYPES))
		{
			return array($this->user->lang('SURVEY_INVALID_QUESTION_TYPE'));
		}
		if (!in_array($question['sum_type'], survey::$QUESTION_SUM_TYPES))
		{
			return array($this->user->lang('SURVEY_INVALID_QUESTION_SUM_TYPE'));
		}
		if ($question['sum_type'] == survey::$QUESTION_SUM_TYPES['MATCHING_TEXT'] && $question['sum_by'] == '')
		{
			return array($this->user->lang('SURVEY_INVALID_QUESTION_SUM_BY'));
		}
		if ($question['sum_type'] != survey::$QUESTION_SUM_TYPES['MATCHING_TEXT'])
		{
			$question['sum_by'] = '';
		}
		if ($question['sum_type'] == survey::$QUESTION_SUM_TYPES['NO_SUM'])
		{
			$question['average'] = 0;
			$question['cap'] = 0;
		}
		$choices_input = $this->request->variable('question_choices', '', true);
		$choices = array();
		if ($question['type'] == survey::$QUESTION_TYPES['DROP_DOWN_MENU'] || $question['type'] == survey::$QUESTION_TYPES['MULTIPLE_CHOICE'])
		{
			if ($choices_input == '')
			{
				return array($this->user->lang('SURVEY_INVALID_QUESTION_CHOICES'));
			}
			$choices = array_unique(explode(",", $choices_input));
		}
		else
		{
			$question['random_choice_order'] = 0;
		}
		$choices = array_map('trim', $choices);
		if ($question_id == self::NEW_QUESTION_ID)
		{
			$this->survey->add_question($question, $choices);
		}
		else
		{
			$this->survey->modify_question($question_id, $question, $choices);
		}
		return array();
	}

	/**
	 * Process deletion of question
	 *
	 * @return array errors
	 */
	protected function process_question_deletion()
	{
		$question_id = (int) $this->request->variable('question_to_delete', '');
		if (!$this->survey->question_exists($question_id))
		{
			return array();
		}

		if (confirm_box(true))
		{
			$this->survey->delete_question($question_id);
			redirect($this->base_url);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'						=> $this->topic_id,
				'question_to_delete'	=> $question_id,
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang('SURVEY_DELETE_QUESTION_CONFIRM', $this->survey->questions[$question_id]['label']), $s_hidden_fields);
		}
		return array();
	}

	/**
	 * Process loading of a question for modification
	 *
	 * @return array errors
	 */
	protected function process_question_load_modify()
	{
		if (!check_form_key($this->form_key))
		{
			return array($this->user->lang('FORM_INVALID'));
		}
		$question_id = (int) $this->request->variable('survey_load_modify_question', '');
		if ($this->survey->question_exists($question_id))
		{
			$this->question_to_load = $question_id;
		}
		return array();
	}

	/**
	 * Process disable
	 *
	 * @return array errors
	 */
	protected function process_disable()
	{
		if (confirm_box(true))
		{
			$this->survey->disable();
			redirect($this->base_url);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'					=> $this->topic_id,
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_DISABLE_CONFIRM'], $s_hidden_fields);
		}
		return array();
	}

	/**
	 * Process delete
	 *
	 * @return array errors
	 */
	protected function process_delete()
	{
		if (confirm_box(true))
		{
			$this->survey->delete($this->topic_id);
			redirect($this->base_url);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				't'					=> $this->topic_id,
				$this->action_name	=> $this->request->variable($this->action_name, ''),
			));
			confirm_box(false, $this->user->lang['SURVEY_DELETE_ALL_CONFIRM'], $s_hidden_fields);
		}
		return array();
	}

	/**
	 * Processes all actions
	 *
	 * @return array errors
	 */
	protected function process_submit()
	{
		if (!$this->request->is_set($this->action_name))
		{
			return array();
		}
		$action = $this->request->variable($this->action_name, '');

		if (!$this->survey->enabled)
		{
			return array($this->user->lang('SURVEY_IS_DISABLED'));
		}

		$is_write_owner = $this->survey->is_write_owner($this->user->data['user_id']);

		if (!$is_write_owner && preg_match("/^(config_change|close|reopen|question_addition_or_modification|question_deletion|question_load_modify|delete|disable)$/", $action))
		{
			return array($this->user->lang('NO_AUTH_OPERATION'));
		}

		if ($this->survey->is_closed() && !$is_write_owner)
		{
			return array($this->user->lang('SURVEY_IS_CLOSED'));
		}

		if ($action == "config_change")
		{
			return $this->process_config_change();
		}

		if ($action == "reopen")
		{
			if (!$this->survey->is_closed())
			{
				return array($this->user->lang('SURVEY_IS_NOT_CLOSED'));
			}
			return $this->process_reopen();
		}

		if ($action == "close")
		{
			if ($this->survey->is_closed())
			{
				return array($this->user->lang('SURVEY_IS_CLOSED', $this->user->format_date($this->survey->settings['stop_time'])));
			}
			return $this->process_close();
		}

		if ($action == "entry_deletion")
		{
			return $this->process_entry_deletion();
		}

		if ($action == "entry_modification")
		{
			return $this->process_entry_modification();
		}

		if ($action == "question_addition_or_modification")
		{
			return $this->process_question_addition_or_modification();
		}

		if ($action == "question_deletion")
		{
			return $this->process_question_deletion();
		}

		if ($action == "question_load_modify")
		{
			return $this->process_question_load_modify();
		}

		if ($action == "disable")
		{
			return $this->process_disable();
		}

		if ($action == "delete")
		{
			return $this->process_delete();
		}

		return array();
	}
}
