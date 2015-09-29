<?php
/**
 *
 * @package survey
 * @copyright (c) 2015 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\functions;

/**
 * The core functions of the survey extension
 *
 */
class survey
{
	static public $SHOW_ORDER_TYPES = array(
		'ALPHABETICAL_USERNAME'		=> 0,
		'RESPONSE_TIME'				=> 1,
		'ALPHABETICAL_FIRST_ANSWER'	=> 2,
	);

	static public $QUESTION_TYPES = array(
		'NORMAL_TEXT_BOX'	=> 0,
		'LARGE_TEXT_BOX'	=> 1,
		'NUMBER'			=> 2,
		'CHECKBOX'			=> 3,
		'DROP_DOWN_MENU'	=> 4,
		'MULTIPLE_CHOICE'	=> 5,
		'DATE'				=> 6,
		'TIME'				=> 7,
		'DATETIME'			=> 8,
		'DATETIME_LOCAL'	=> 9,
	);

	static public $QUESTION_SUM_TYPES = array(
		'NO_SUM'				=> 0,
		'NUMBER_OF_RESPONSES'	=> 1,
		'SUM_OF_NUMBERS'		=> 2,
		'MATCHING_TEXT'			=> 3,
	);

	static public $VISIBILITY_TYPES = array(
		'SHOW_EVERYTHING'	=> 0,
		'ANONYMIZE'			=> 1,
		'HIDE_ENTRIES'		=> 2,
		'HIDE_EVERYTHING'	=> 3,
	);

	static public $TOPIC_POSTER_RIGHTS = array(
		'NONE'					=> 0,
		'CAN_SEE_EVERYTHING'	=> 1,
		'CAN_MANAGE'			=> 2,
		'CAN_EDIT_OTHER_USERS'	=> 3,
	);

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var array */
	protected $tables;

	/** @var int */
	protected $time_called;

	/** @var int */
	protected $topic_id;

	/** @var int */
	protected $forum_id;

	/** @var int */
	protected $topic_poster;

	/** @var int */
	protected $topic_status;

	/** @var int */
	protected $forum_status;

	/** @var int */
	public $enabled = 0;

	/** @var array */
	public $settings;

	/** @var array */
	public $questions;

	/** @var array */
	public $entries;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\config\config $config
	 * @param \phpbb\user $user
	 * @param \phpbb\auth\auth $auth
	 * @param string $surveys_table
	 * @param string $questions_table
	 * @param string $question_choices_table
	 * @param string $entries_table
	 * @param string $answers_table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\auth\auth $auth, $surveys_table, $questions_table, $question_choices_table, $entries_table, $answers_table)
	{
		$this->settings = array(
			's_id'					=> 0,
			'caption'				=> 0,
			'show_order'			=> 0,
			'reverse_order'			=> 0,
			'allow_change_answer'	=> 0,
			'allow_multiple_answer'	=> 0,
			'visibility'			=> 0,
			'start_time'			=> 0,
			'stop_time'				=> null,
			'topic_poster_right'	=> 0,
		);

		$this->questions = array();
		$this->entries = array();

		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->tables = array(
			'surveys'			=> $surveys_table,
			'questions'			=> $questions_table,
			'question_choices'	=> $question_choices_table,
			'entries'			=> $entries_table,
			'answers'			=> $answers_table,
		);
		$this->time_called = false;
	}

	/**
	 * Set some environment variables
	 *
	 * @param int $forum_id
	 * @param int $topic_poster
	 * @param int $topic_status
	 * @param int $forum_status
	 * @return bool success
	 */
	public function set_env($forum_id, $topic_poster, $topic_status, $forum_status)
	{
		$this->forum_id = $forum_id;
		$this->topic_poster = $topic_poster;
		$this->topic_status = $topic_status;
		$this->forum_status = $forum_status;
	}

	/**
	 * Loads all available data on a survey from the database. Returns false if the topic does not exist.
	 *
	 * @param int $topic_id
	 * @param bool $use_survey_id
	 * @return bool success
	 */
	public function load_survey($topic_id, $use_survey_id = false)
	{
		$db = $this->db;

		$this->topic_id = $topic_id;
		$this->questions = array();
		$this->entries = array();

		if (!$use_survey_id)
		{
			$sql = 'SELECT survey_enabled FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $topic_id;
			$result = $db->sql_query($sql);
			if (!$row = $db->sql_fetchrow($result))
			{
				// topic doesn't exist
				return false;
			}
			$this->enabled = $row['survey_enabled'];
			$db->sql_freeresult($result);
		}

		// load settings
		$sql = 'SELECT ';
		$first = key($this->settings);
		foreach ($this->settings as $setting => $entry)
		{
			if ($setting !== $first)
			{
				$sql .= ', ';
			}
			$sql .= $setting;
		}
		$sql .= " FROM {$this->tables['surveys']} WHERE " . ($use_survey_id ? 's_id' : 'topic_id') . " = $topic_id";
		$result = $db->sql_query($sql);
		if (!$row = $db->sql_fetchrow($result))
		{
			// survey doesn't exist
			return false;
		}
		foreach ($this->settings as $setting => $entry)
		{
			$this->settings[$setting] = $row[$setting];
		}
		$db->sql_freeresult($result);

		// load questions
		$sql = "SELECT q_id, label, example_answer, type, random_choice_order, sum_value, sum_type, sum_by, average, cap FROM {$this->tables['questions']} WHERE s_id = {$this->settings['s_id']}";
		$result = $db->sql_query($sql);
		$this->questions = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$this->questions[$row['q_id']] = $row;
			// load question choices
			$sql = "SELECT c_id, text, sum FROM {$this->tables['question_choices']} WHERE q_id = {$row['q_id']}";
			$result2 = $db->sql_query($sql);
			$this->questions[$row['q_id']]['choices'] = array();
			while ($row2 = $db->sql_fetchrow($result2))
			{
				$this->questions[$row['q_id']]['choices'][$row2['c_id']] = $row2;
			}
			$db->sql_freeresult($result2);
		}
		$db->sql_freeresult($result);

		// load entries
		$sql = "SELECT entry_id, user_id, entry_username FROM {$this->tables['entries']} WHERE s_id = {$this->settings['s_id']}";
		$result = $db->sql_query($sql);
		$this->entries = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$this->entries[$row['entry_id']] = $row;
			// load answers
			$sql = "SELECT q_id, answer FROM {$this->tables['answers']} WHERE entry_id = {$row['entry_id']}";
			$result2 = $db->sql_query($sql);
			$this->entries[$row['entry_id']]['answers'] = array();
			while ($row2 = $db->sql_fetchrow($result2))
			{
				$this->entries[$row['entry_id']]['answers'][$row2['q_id']] = $row2['answer'];
			}
			$db->sql_freeresult($result2);
		}
		$db->sql_freeresult($result);

		return true;
	}

	/**
	 * Checks if the survey can be accessed at all
	 * Called BEFORE load_survey()!
	 *
	 * @param int $forum_id
	 * @return boolean
	 */
	public function can_create_survey($forum_id)
	{
		return $this->auth->acl_get('f_survey_create', $forum_id) || $this->is_moderator();
	}

	/**
	 * Checks if the user is a survey moderator
	 *
	 * @return boolean
	 */
	public function is_moderator()
	{
		return $this->auth->acl_get('m_edit', $this->forum_id);
	}

	/**
	 * Checks if the user can manage questions, edit the settings and edit the entries of other users
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function can_edit_other_users($user_id)
	{
		return !$this->is_locked() && ($this->is_moderator() || ($this->topic_poster == $user_id && $this->settings['topic_poster_right'] == self::$TOPIC_POSTER_RIGHTS['CAN_EDIT_OTHER_USERS']));
	}

	/**
	 * Checks if the user can manage questions and the edit the settings of the survey
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function can_manage($user_id)
	{
		return !$this->is_locked() && ($this->is_moderator() || ($this->topic_poster == $user_id && ($this->settings['topic_poster_right'] == self::$TOPIC_POSTER_RIGHTS['CAN_EDIT_OTHER_USERS'] || $this->settings['topic_poster_right'] == self::$TOPIC_POSTER_RIGHTS['CAN_MANAGE'])));
	}

	/**
	 * Checks if the user can see all the information of the survey
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function can_see_everything($user_id)
	{
		return $this->is_moderator() || ($this->topic_poster == $user_id && ($this->settings['topic_poster_right'] == self::$TOPIC_POSTER_RIGHTS['CAN_EDIT_OTHER_USERS'] || $this->settings['topic_poster_right'] == self::$TOPIC_POSTER_RIGHTS['CAN_MANAGE'] || $this->settings['topic_poster_right'] == self::$TOPIC_POSTER_RIGHTS['CAN_SEE_EVERYTHING']));
	}

	/**
	 * Checks if the survey is locked via forum or topic lock
	 *
	 * @return boolean
	 */
	public function is_locked()
	{
		return ($this->forum_status == ITEM_LOCKED || $this->topic_status == ITEM_LOCKED) && !$this->is_moderator();
	}

	/**
	 * Checks if the user is already participating
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function is_participating($user_id)
	{
		foreach ($this->entries as $entry)
		{
			if ($entry['user_id'] == $user_id)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks if the user can add a new entry of $entry_user_id
	 *
	 * @param int $real_user_id
	 * @param int $entry_user_id
	 * @return boolean
	 */
	public function can_add_new_entry($real_user_id, $entry_user_id = null)
	{
		if ($entry_user_id === null)
		{
			$entry_user_id = $real_user_id;
		}
		if ($this->is_locked())
		{
			return false;
		}
		if (!$this->user->data['is_registered'])
		{
			return false;
		}
		if (!$this->settings['allow_multiple_answer'] && $this->is_participating($entry_user_id))
		{
			return false;
		}
		if ($this->can_edit_other_users($real_user_id))
		{
			return true;
		}
		if ($real_user_id != $entry_user_id)
		{
			return false;
		}
		if ($this->can_manage($real_user_id))
		{
			return true;
		}
		if ($this->is_closed())
		{
			return false;
		}
		return $this->auth->acl_get('f_survey_answer', $this->forum_id);
	}

	/**
	 * Checks if the user can edit an entry of $entry_user_id
	 *
	 * @param int $real_user_id
	 * @param int $entry_user_id
	 * @return boolean
	 */
	public function can_modify_entry($real_user_id, $entry_user_id = null)
	{
		if ($entry_user_id === null)
		{
			$entry_user_id = $real_user_id;
		}
		if ($this->is_locked())
		{
			return false;
		}
		if (!$this->user->data['is_registered'])
		{
			return false;
		}
		if (!$this->is_participating($entry_user_id))
		{
			return false;
		}
		if ($this->can_edit_other_users($real_user_id))
		{
			return true;
		}
		if ($real_user_id != $entry_user_id)
		{
			return false;
		}
		if ($this->can_manage($real_user_id))
		{
			return true;
		}
		if ($this->is_closed())
		{
			return false;
		}
		if (!$this->settings['allow_change_answer'])
		{
			return false;
		}
		return true;
	}

	/**
	 * Checks if the entry exists
	 *
	 * @param int $entry_id
	 * @return boolean
	 */
	public function entry_exists($entry_id)
	{
		if ($entry_id != '' && isset($this->entries[$entry_id]))
		{
			return true;
		}
		return false;
	}

	/**
	 * Checks if the question exists
	 *
	 * @param int $question_id
	 * @return boolean
	 */
	public function question_exists($question_id)
	{
		if ($question_id != '' && isset($this->questions[$question_id]))
		{
			return true;
		}
		return false;
	}

	/**
	 * Return the question with given label
	 *
	 * @param string $question_label
	 * @param int $default_value
	 * @return int
	 */
	public function get_question_id_from_label($question_label, $default_value)
	{
		foreach ($this->questions as $question_id => $question)
		{
			if ($question['label'] == $question_label)
			{
				return $question_id;
			}
		}
		return $default_value;
	}

	/**
	 * Change config
	 * The $new_settings can contain caption, show_order, reverse_order, allow_change_answer, allow_multiple_answer, visibility, stop_time and topic_poster_right
	 * It MUST NOT contain s_id, topic_id, and start_time
	 *
	 * @param array $new_settings
	 */
	public function change_config($new_settings)
	{
		$sql = "UPDATE {$this->tables['surveys']} SET " . $this->db->sql_build_array('UPDATE', $new_settings) . " WHERE s_id = {$this->settings['s_id']}";
		$this->db->sql_query($sql);
		$this->settings = array_merge($this->settings, $new_settings);
	}

	/**
	 * Add a question
	 *
	 * @param array $question
	 * @param array $choices
	 */
	public function add_question($question, $choices)
	{
		$question['s_id'] = $this->settings['s_id'];
		$question['sum_value'] = 0;
		$sql = "INSERT INTO {$this->tables['questions']} " . $this->db->sql_build_array('INSERT', $question);
		$this->db->sql_query($sql);
		$question_id = $this->db->sql_nextid();
		$question['choices'] = array();
		$this->questions[$question_id] = $question;
		foreach ($choices as $choice)
		{
			$insert_choice = array(
				'q_id'	=> $question_id,
				'text'	=> $choice,
				'sum'	=> 0,
			);
			$sql = "INSERT INTO {$this->tables['question_choices']} " . $this->db->sql_build_array('INSERT', $insert_choice);
			$this->db->sql_query($sql);
			$choice_id = $this->db->sql_nextid();
			unset($insert_choice['q_id']);
			$insert_choice['c_id'] = $choice_id;
			$this->questions[$question_id]['choices'][$choice_id] = $insert_choice;
		}
	}

	/**
	 * Delete question
	 * The question must exist
	 *
	 * @param int $question_id
	 */
	public function delete_question($question_id)
	{
		$sql = "DELETE FROM {$this->tables['answers']} WHERE q_id = $question_id";
		$this->db->sql_query($sql);
		foreach ($this->entries as $entry_id => $entry)
		{
			if (isset($this->entries[$entry_id]['answers'][$question_id]))
			{
				unset($this->entries[$entry_id]['answers'][$question_id]);
			}
		}
		$sql = "DELETE FROM {$this->tables['question_choices']} WHERE q_id = $question_id";
		$this->db->sql_query($sql);
		$sql = "DELETE FROM {$this->tables['questions']} WHERE q_id = $question_id";
		$this->db->sql_query($sql);
		unset($this->questions[$question_id]);
		foreach ($this->entries as $entry_id => $entry)
		{
			$is_filled = false;
			foreach ($entry['answers'] as $answer)
			{
				if ($answer != '')
				{
					$is_filled = true;
				}
			}
			if (!$is_filled)
			{
				$this->delete_entry($entry_id);
			}
		}
	}

	/**
	 * Modify question
	 * The question must exist
	 * The $question MUST NOT contain question_id, s_id and sum_value
	 * The $choices array contains only the texts
	 *
	 * @param int $question_id
	 * @param array $question
	 * @param array $choices
	 */
	public function modify_question($question_id, $question, $choices)
	{
		$this->questions[$question_id] = $question;
		// Here, we start some sort of a hack, to get the check_answer call working...
		$this->questions[$question_id]['choices'] = array();
		foreach ($choices as $choice)
		{
			$this->questions[$question_id]['choices'][] = array('text' => $choice);
		}
		foreach ($this->entries as $entry_id => $entry)
		{
			if (isset($entry['answers'][$question_id]) && !$this->check_answer($entry['answers'][$question_id], $question_id))
			{
				$this->delete_answer($question_id, $entry_id);
			}
		}
		unset($this->questions[$question_id]['choices']);
		// End of hack
		$this->compute_sum($question_id);
		$this->questions[$question_id]['sum_value'] = $this->questions[$question_id]['sum_value'];
		$sql = "UPDATE {$this->tables['questions']} SET " . $this->db->sql_build_array('UPDATE', $this->questions[$question_id]) . " WHERE q_id = $question_id";
		$this->db->sql_query($sql);
		$this->questions[$question_id]['q_id'] = $question_id;
		$sql = "DELETE FROM {$this->tables['question_choices']} WHERE q_id = $question_id";
		$this->db->sql_query($sql);
		$this->questions[$question_id]['choices'] = array();
		foreach ($choices as $choice)
		{
			$count = 0;
			foreach ($this->entries as $entry)
			{
				$count += (isset($entry['answers'][$question_id]) ? $this->get_sum_diff_for_answer($question_id, $entry['answers'][$question_id], $choice) : 0);
			}
			$insert_choice = array(
				'q_id'	=> $question_id,
				'text'	=> $choice,
				'sum'	=> $count,
			);
			$sql = "INSERT INTO {$this->tables['question_choices']} " . $this->db->sql_build_array('INSERT', $insert_choice);
			$this->db->sql_query($sql);
			$choice_id = $this->db->sql_nextid();
			unset($insert_choice['q_id']);
			$insert_choice['c_id'] = $choice_id;
			$this->questions[$question_id]['choices'][$choice_id] = $insert_choice;
		}
	}

	/**
	 * Add entry
	 * The user with $user_id must exist
	 *
	 * @param int $entry_id
	 * @param array $answers
	 */
	public function add_entry($user_id, $answers)
	{
		$entry = array(
			's_id'		=> $this->settings['s_id'],
			'user_id'	=> $user_id,
		);
		$sql = "INSERT INTO {$this->tables['entries']} " . $this->db->sql_build_array('INSERT', $entry);
		$this->db->sql_query($sql);
		$entry_id = $this->db->sql_nextid();
		$entry['entry_id'] = $entry_id;
		$entry['entry_username'] = '';
		$this->entries[$entry_id] = $entry;
		foreach ($answers as $question_id => $answer)
		{
			$this->add_answer($question_id, $entry_id, $answer);
		}
	}

	/**
	 * Delete entry
	 * The entry must exist
	 *
	 * @param int $entry_id
	 */
	public function delete_entry($entry_id)
	{
		$sql = "DELETE FROM {$this->tables['answers']} WHERE entry_id = $entry_id";
		$this->db->sql_query($sql);
		$sql = "DELETE FROM {$this->tables['entries']} WHERE entry_id = $entry_id";
		$this->db->sql_query($sql);
		foreach ($this->questions as $question_id => $question)
		{
			if (isset($this->entries[$entry_id]['answers'][$question_id]))
			{
				$this->modify_sum_entry($question_id, true, false, 0, true, $this->entries[$entry_id]['answers'][$question_id], true);
			}
		}
		unset($this->entries[$entry_id]);
	}

	/**
	 * Modify entry
	 * The entry with $entry_id must exist
	 *
	 * @param int $entry_id
	 * @param array $answers
	 */
	public function modify_entry($entry_id, $answers)
	{
		foreach ($answers as $question_id => $answer)
		{
			if (isset($this->entries[$entry_id]['answers'][$question_id]))
			{
				$sql = "UPDATE {$this->tables['answers']} SET " . $this->db->sql_build_array('UPDATE', array('answer' => $answer)) . " WHERE q_id = $question_id AND entry_id = $entry_id";
				$this->db->sql_query($sql);
				$this->modify_sum_entry($question_id, true, true, $answer, true, $this->entries[$entry_id]['answers'][$question_id], true);
				$this->entries[$entry_id]['answers'][$question_id] = $answer;
			}
			else
			{
				$this->add_answer($question_id, $entry_id, $answer);
			}
		}
	}

	/**
	 * Add answer
	 * The question with $question_id and the entry with $entry_id must exist
	 *
	 * @param int $question_id
	 * @param int $entry_id
	 * @param string $answer
	 */
	protected function add_answer($question_id, $entry_id, $answer)
	{
		$insert_answer = array(
			'q_id'		=> $question_id,
			'entry_id'	=> $entry_id,
			'answer'	=> $answer,
		);
		$sql = "INSERT INTO {$this->tables['answers']} " . $this->db->sql_build_array('INSERT', $insert_answer);
		$this->db->sql_query($sql);
		$this->entries[$entry_id]['answers'][$question_id] = $answer;
		$this->modify_sum_entry($question_id, true, true, $answer, false, 0, true);
	}

	/**
	 * Delete answer
	 * The question with $question_id and the entry with $entry_id must exist
	 * Sums will NOT be handeled here!
	 *
	 * @param int $question_id
	 * @param int $entry_id
	 */
	protected function delete_answer($question_id, $entry_id)
	{
		$sql = "DELETE FROM {$this->tables['answers']} WHERE q_id = $question_id AND entry_id = $entry_id";
		$this->db->sql_query($sql);
		unset($this->entries[$entry_id]['answers'][$question_id]);
		if (empty($this->entries[$entry_id]['answers']))
		{
			$sql = "DELETE FROM {$this->tables['entries']} WHERE entry_id = $entry_id";
			$this->db->sql_query($sql);
			unset($this->entries[$entry_id]);
		}
	}

	/**
	 * Check if the answer is conform with the type of the question
	 *
	 * @param string $answer
	 * @param int $question_id
	 * @return bool
	 */
	public function check_answer($answer, $question_id)
	{
		if (strlen($answer) > 3000)
		{
			return false;
		}
		$question_type = $this->questions[$question_id]['type'];
		switch ($question_type)
		{
			case self::$QUESTION_TYPES['NUMBER']:
				return preg_match('/^-?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?$/', $answer) ? true : false;
			break;
			case self::$QUESTION_TYPES['CHECKBOX']:
				return preg_match('/^[01]$/', $answer) ? true : false;
			break;
			case self::$QUESTION_TYPES['DROP_DOWN_MENU']:
				return $this->check_choice($answer, $question_id, false);
			break;
			case self::$QUESTION_TYPES['MULTIPLE_CHOICE']:
				return $this->check_choice($answer, $question_id, true);
			break;
			case self::$QUESTION_TYPES['DATE']:
				return $this->check_full_date($answer);
			break;
			case self::$QUESTION_TYPES['TIME']:
				return $this->check_partial_time($answer);
			break;
			case self::$QUESTION_TYPES['DATETIME']:
				$matches = array();
				if (preg_match('/^(.+)T(.+)(Z|[+-][0-9]{2}:[0-9]{2})$/', $answer, $matches) === 1)
				{
					return $this->check_full_date($matches[1]) && $this->check_partial_time($matches[2]) && $this->check_time_offset($matches[3]);
				}
				return false;
			break;
			case self::$QUESTION_TYPES['DATETIME_LOCAL']:
				$matches = array();
				if (preg_match('/^(.+)T(.+)$/', $answer, $matches) === 1)
				{
					return $this->check_full_date($matches[1]) && $this->check_partial_time($matches[2]);
				}
				return false;
			break;
		}
		return true;
	}

	/**
	 * Check if the answer is a valid choice for the question
	 *
	 * @param string $answer
	 * @param int $question_id
	 * @param bool $is_multiple_choice
	 * @return bool
	 */
	protected function check_choice($answer, $question_id, $is_multiple_choice)
	{
		$answer = utf8_case_fold_nfc($answer);
		$valid_choices = array();
		$given_choices = array($answer);
		foreach ($this->questions[$question_id]['choices'] as $choice)
		{
			$valid_choices[] = utf8_case_fold_nfc($choice['text']);
		}
		if ($is_multiple_choice)
		{
			$given_choices = explode(",", $answer);
		}
		return empty(array_diff($given_choices, $valid_choices));
	}

	/**
	 * Check if the answer is a valid HTML5 date dataype,
	 * meaning that it is conform to full-date as defined in RFC 3339
	 * but the year can have more than 4 digits
	 *
	 * @param string $answer
	 * @return bool
	 */
	protected function check_full_date($answer)
	{
		$matches = array();
		if (preg_match('/^([0-9]{4,})-([0-9]{2})-([0-9]{2})$/', $answer, $matches) === 1)
		{
			return checkdate($matches[2], $matches[3], $matches[1]);
		}
		return false;
	}

	/**
	 * Check if the answer is a valid HTML5 time datatype,
	 * meaning that it is conform to partial-time as defined in RFC 3339
	 *
	 * @param string $answer
	 * @return bool
	 */
	protected function check_partial_time($answer)
	{
		$matches = array();
		if (preg_match('/^([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]+)?$/', $answer, $matches) === 1)
		{
			if ($matches[1] <= 24 && $matches[2] <= 59 && $matches[3] <= 59)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if the answer is conform to time-offset as defined in RFC 3339
	 *
	 * @param string $answer
	 * @return bool
	 */
	protected function check_time_offset($answer)
	{
		if ($answer == "Z")
		{
			return true;
		}
		// test for time-numoffset
		$matches = array();
		if (preg_match('/^[+-]([0-9]{2}):([0-9]{2})$/', $answer, $matches) === 1)
		{
			if ($matches[1] <= 24 && $matches[2] <= 59)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Re-compute the sum of a question and then call update_sum
	 *
	 * @param int $question_id
	 */
	protected function compute_sum($question_id, $do_sql_update = false)
	{
		$type = $this->questions[$question_id]['sum_type'];
		$sum = 0;
		if ($type == self::$QUESTION_SUM_TYPES['NO_SUM'])
		{
			$this->update_sum($question_id, $sum, $do_sql_update);
			return;
		}
		foreach ($this->entries as $entry_id => $entry)
		{
			if (isset($entry['answers'][$question_id]))
			{
				$sum += $this->modify_sum_entry($question_id, false, true, $entry['answers'][$question_id]);
			}
		}
		$this->update_sum($question_id, $sum, $do_sql_update);
	}

	/**
	 * Determine the diff for the sum of a question by changing the answer of a given entry
	 *
	 * @param int $question_id
	 * @param bool $do_choice_sum
	 * @param bool $new_exists
	 * @param mixed $new_value
	 * @param bool $old_exists
	 * @param mixed $old_value
	 * @param bool $do_update
	 * @return int
	 */
	public function modify_sum_entry($question_id, $do_choice_sum, $new_exists, $new_value, $old_exists = false, $old_value = 0, $do_update = false)
	{
		$type = $this->questions[$question_id]['sum_type'];
		$diff = 0;
		if ($type == self::$QUESTION_SUM_TYPES['NUMBER_OF_RESPONSES'] && $new_exists != $old_exists)
		{
			$diff = ($new_exists ? 1 : -1);
		}
		else if ($type == self::$QUESTION_SUM_TYPES['SUM_OF_NUMBERS'])
		{
			if ($this->questions[$question_id]['type'] == self::$QUESTION_TYPES['MULTIPLE_CHOICE'])
			{
				$exploded_values = explode(",", $new_value);
				foreach ($exploded_values as $value)
				{
					$diff += is_numeric($value) ? (float) $value : 0;
				}
				$exploded_values = explode(",", $old_value);
				foreach ($exploded_values as $value)
				{
					$diff -= is_numeric($value) ? (float) $value : 0;
				}
			}
			else
			{
				if (!is_numeric($new_value))
				{
					$new_value = 0;
				}
				if (!is_numeric($old_value))
				{
					$old_value = 0;
				}
				$diff = (float) $new_value - (float) $old_value;
			}
		}
		else if ($type == self::$QUESTION_SUM_TYPES['MATCHING_TEXT'])
		{
			if ($new_exists)
			{
				$diff = $this->get_sum_diff_for_answer($question_id, $new_value, $this->questions[$question_id]['sum_by']);
			}
			if ($old_exists)
			{
				$diff -= $this->get_sum_diff_for_answer($question_id, $old_value, $this->questions[$question_id]['sum_by']);
			}
		}
		if ($do_choice_sum && ($this->questions[$question_id]['type'] == self::$QUESTION_TYPES['DROP_DOWN_MENU'] || $this->questions[$question_id]['type'] == self::$QUESTION_TYPES['MULTIPLE_CHOICE']))
		{
			$cdiff = array();
			$choice_ids = array_keys($this->questions[$question_id]['choices']);
			foreach ($choice_ids as $choice_id)
			{
				$cdiff[$choice_id] = 0;
			}
			if ($new_exists)
			{
				$this->get_choice_sum_diff_for_answer($question_id, $new_value, 1, $cdiff);
			}
			if ($old_exists)
			{
				$this->get_choice_sum_diff_for_answer($question_id, $old_value, -1, $cdiff);
			}
			foreach ($choice_ids as $choice_id)
			{
				if ($cdiff[$choice_id] != 0)
				{
					$this->questions[$question_id]['choices'][$choice_id]['sum'] += $cdiff[$choice_id];
					$sql = "UPDATE {$this->tables['question_choices']} SET " . $this->db->sql_build_array('UPDATE', array('sum' => $this->questions[$question_id]['choices'][$choice_id]['sum'])) . " WHERE c_id = $choice_id";
					$this->db->sql_query($sql);
				}
			}
		}
		if ($do_update)
		{
			$sum = $this->questions[$question_id]['sum_value'] + $diff;
			$this->update_sum($question_id, $sum, true);
		}
		return $diff;
	}

	/**
	 * Get the diff for the sum of a question of a specific text
	 *
	 * @param int $question_id
	 * @param string $value
	 * @param string $sum_by
	 */
	protected function get_sum_diff_for_answer($question_id, $value, $sum_by)
	{
		$diff = 0;
		$sum_by = utf8_case_fold_nfc($sum_by);
		if ($this->questions[$question_id]['type'] == self::$QUESTION_TYPES['MULTIPLE_CHOICE'])
		{
			$exploded_answers = explode(",", utf8_case_fold_nfc($value));
			$diff = (in_array($sum_by, $exploded_answers) ? 1 : 0);
		}
		else
		{
			$diff = ($sum_by == utf8_case_fold_nfc($value) ? 1 : 0);
		}
		return $diff;
	}

	/**
	 * Get the diff for the sum of a question for all choices of a specific text
	 * $sign is either 1 or -1
	 *
	 * @param int $question_id
	 * @param string $sign
	 * @param string $value
	 * @param array $cdiff
	 */
	protected function get_choice_sum_diff_for_answer($question_id, $value, $sign, &$cdiff)
	{
		$exploded_answers = '';
		if ($this->questions[$question_id]['type'] == self::$QUESTION_TYPES['MULTIPLE_CHOICE'])
		{
			$exploded_answers = explode(",", utf8_case_fold_nfc($value));
		}
		foreach ($this->questions[$question_id]['choices'] as $choice_id => $choice)
		{
			if ($this->questions[$question_id]['type'] == self::$QUESTION_TYPES['MULTIPLE_CHOICE'])
			{
				$cdiff[$choice_id] += (in_array(utf8_case_fold_nfc($choice['text']), $exploded_answers) ? $sign : 0);
			}
			else
			{
				$cdiff[$choice_id] += (utf8_case_fold_nfc($choice['text']) == utf8_case_fold_nfc($value) ? $sign : 0);
			}
		}
	}

	/**
	 * Update the sum value of question with $question_id.
	 * Update only the sql value, if $do_sql_update is set.
	 *
	 * @param int $question_id
	 * @param mixed $sum
	 * @param bool $do_sql_update
	 */
	protected function update_sum($question_id, $sum, $do_sql_update)
	{
		$this->questions[$question_id]['sum_value'] = $sum;
		if ($do_sql_update)
		{
			$sql = "UPDATE {$this->tables['questions']} SET " . $this->db->sql_build_array('UPDATE', array('sum_value' => $sum)) . " WHERE q_id = $question_id";
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Return the number of entries in the survey
	 *
	 * @return int
	 */
	public function get_entry_count()
	{
		return sizeof($this->entries);
	}

	/**
	 * Returns the sum of the $question.
	 *
	 * @param int $question_id
	 * @return string
	 */
	public function get_sum_string($question_id)
	{
		if ($this->questions[$question_id]['sum_type'] == self::$QUESTION_SUM_TYPES['NO_SUM'])
		{
			return '';
		}
		$sum = $this->questions[$question_id]['sum_value'];
		if ($sum == round($sum))
		{
			return strval((int) $sum);
		}
		return strval($sum);
	}

	/**
	 * Returns the average sum of the $question.
	 * The number of entries needs to be provided.
	 *
	 * @param int $question_id
	 * @param int $count
	 * @return string
	 */
	public function get_average_string($question_id, $count)
	{
		if (!$this->questions[$question_id]['average'])
		{
			return '';
		}
		if ($count == 0)
		{
			return '0';
		}
		$type = $this->questions[$question_id]['sum_type'];
		$sum = $this->questions[$question_id]['sum_value'];
		$diff = 0;
		if ($type == self::$QUESTION_SUM_TYPES['MATCHING_TEXT'])
		{
			return round(($sum * 100) / $count, 2) . '%';
		}
		else if ($type == self::$QUESTION_SUM_TYPES['SUM_OF_NUMBERS'])
		{
			return round($sum / $count, 2) . '';
		}
		return '';
	}

	/**
	 * Initialize the survey
	 *
	 * @param int $tid
	 */
	public function initialize($tid)
	{
		$this->user->add_lang_ext('kilianr/survey', 'survey');
		$inserts = array(
			'topic_id'				=> $tid,
			'caption'				=> $this->user->lang['SURVEY'],
			'show_order'			=> $this->config['kilianr_survey_default_show_order'],
			'reverse_order'			=> $this->config['kilianr_survey_default_reverse_order'],
			'allow_change_answer'	=> $this->config['kilianr_survey_default_allow_change_answer'],
			'allow_multiple_answer'	=> $this->config['kilianr_survey_default_allow_multiple_answer'],
			'visibility'			=> $this->config['kilianr_survey_default_visibility'],
			'start_time'			=> $this->fixed_time(),
			'topic_poster_right'	=> $this->config['kilianr_survey_default_topic_poster_right'],
		);
		$sql = 'INSERT INTO ' . $this->tables['surveys'] . ' ' . $this->db->sql_build_array('INSERT', $inserts);
		$this->db->sql_query($sql);
	}

	/**
	 * Checks if the survey is anonymized
	 *
	 * @return bool
	 */
	public function is_anonymized()
	{
		return $this->settings['visibility'] == self::$VISIBILITY_TYPES['ANONYMIZE'] || $this->settings['visibility'] == self::$VISIBILITY_TYPES['HIDE_ENTRIES'] || $this->settings['visibility'] == self::$VISIBILITY_TYPES['HIDE_EVERYTHING'];
	}

	/**
	 * Checks if the entries of the survey are hidden
	 *
	 * @return bool
	 */
	public function hide_entries()
	{
		return $this->settings['visibility'] == self::$VISIBILITY_TYPES['HIDE_ENTRIES'] || $this->settings['visibility'] == self::$VISIBILITY_TYPES['HIDE_EVERYTHING'];
	}

	/**
	 * Checks if the everything (entries and sums) of the survey are hidden
	 *
	 * @return bool
	 */
	public function hide_everything()
	{
		return $this->settings['visibility'] == self::$VISIBILITY_TYPES['HIDE_EVERYTHING'];
	}

	/**
	 * Checks if the question has a cap set
	 *
	 * @param int $question_id
	 * @param float $diff
	 * @return bool
	 */
	public function has_cap($question_id)
	{
		return ($this->questions[$question_id]['cap'] > 0 ? true : false);
	}

	/**
	 * Checks if the cap of question with $question_id is exceeded
	 * If $diff is set, it is beeing taking into consideration as change of the sum_value
	 *
	 * @param int $question_id
	 * @param float $diff
	 * @return bool
	 */
	public function cap_exceeded($question_id, $diff = 0)
	{
		return (($this->has_cap($question_id) && $this->questions[$question_id]['cap'] < $this->questions[$question_id]['sum_value'] + $diff) ? true: false);
	}

	/**
	 * Checks if the cap of question with $question_id has been reached
	 *
	 * @param int $question_id
	 * @return bool
	 */
	public function cap_reached($question_id)
	{
		return (($this->has_cap($question_id) && $this->questions[$question_id]['cap'] <= $this->questions[$question_id]['sum_value']) ? true: false);
	}

	/**
	 * Checks if the survey on given topic is enabled
	 *
	 * @param int $topic_id
	 * @return boolean
	 */
	public function is_enabled($topic_id)
	{
		$sql = 'SELECT survey_enabled FROM ' . TOPICS_TABLE . " WHERE topic_id = $topic_id";
		$result = $this->db->sql_query($sql);
		if (!$row = $this->db->sql_fetchrow($result))
		{
			// topic doesn't exist
			return false;
		}
		$this->db->sql_freeresult($result);
		return $row['survey_enabled'] == true;
	}

	/**
	 * Enable the survey
	 */
	public function enable()
	{
		$this->enabled = true;
		$sql = 'UPDATE ' . TOPICS_TABLE . " SET survey_enabled = 1 WHERE topic_id = {$this->topic_id}";
		$this->db->sql_query($sql);
	}

	/**
	 * Disable the survey
	 */
	public function disable()
	{
		$this->enabled = false;
		$sql = 'UPDATE ' . TOPICS_TABLE . " SET survey_enabled = 0 WHERE topic_id = {$this->topic_id}";
		$this->db->sql_query($sql);
	}

	/**
	 * Close the survey
	 */
	public function close()
	{
		$this->settings['stop_time'] = $this->fixed_time();
		$sql = "UPDATE {$this->tables['surveys']} SET " . $this->db->sql_build_array('UPDATE', array('stop_time' => $this->settings['stop_time'])) . " WHERE s_id = {$this->settings['s_id']}";
		$this->db->sql_query($sql);
	}

	/**
	 * Reopen the survey
	 */
	public function reopen()
	{
		$this->settings['stop_time'] = null;
		$sql = "UPDATE {$this->tables['surveys']} SET stop_time = null WHERE s_id = {$this->settings['s_id']}";
		$this->db->sql_query($sql);
	}

	/**
	 * Returns time(), but always the same value over one run of the extension
	 *
	 * @return int
	 */
	public function fixed_time()
	{
		if ($this->time_called === false)
		{
			$this->time_called = time();
		}
		return $this->time_called;
	}

	/**
	 * Checks if the survey is is closed
	 *
	 * @return boolean
	 */
	public function is_closed()
	{
		if ($this->settings['stop_time'] !== null && $this->fixed_time() >= $this->settings['stop_time'])
		{
			return true;
		}
		return false;
	}

	/**
	 * Efficiently delete one or multiple surveys directly in DB.
	 *
	 * @param array $topic_ids
	 * @param bool $update_topics
	 */
	public function delete($topic_ids, $from_topic_delete = false)
	{
		if (!is_array($topic_ids))
		{
			$topic_ids = array($topic_ids);
		}

		foreach ($topic_ids as $tid)
		{
			$sql = "SELECT s_id FROM {$this->tables['surveys']} WHERE topic_id = $tid";
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sid = $row['s_id'];
				$sql = "SELECT q_id FROM {$this->tables['questions']} WHERE s_id = $sid";
				$result2 = $this->db->sql_query($sql);
				while ($row2 = $this->db->sql_fetchrow($result2))
				{
					$qid = $row2['q_id'];
					$sql = "DELETE FROM {$this->tables['question_choices']} WHERE q_id = $qid";
					$this->db->sql_query($sql);
					$sql = "DELETE FROM {$this->tables['answers']} WHERE q_id = $qid";
					$this->db->sql_query($sql);
				}
				$this->db->sql_freeresult($result2);
				$sql = "DELETE FROM {$this->tables['questions']} WHERE s_id = $sid";
				$this->db->sql_query($sql);
				$sql = "DELETE FROM {$this->tables['entries']} WHERE s_id = $sid";
				$this->db->sql_query($sql);
			}
			$this->db->sql_freeresult($result);
			$sql = "DELETE FROM {$this->tables['surveys']} WHERE topic_id = $tid";
			$this->db->sql_query($sql);

			if (!$from_topic_delete)
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . " SET survey_enabled = 0 WHERE topic_id = $tid";
				$this->db->sql_query($sql);
			}
		}
		if (!$from_topic_delete)
		{
			$this->enabled = 0;
			unset($this->settings);
			unset($this->questions);
			unset($this->entries);
		}
	}

	/**
	 * Handle the deletion of one or multiple users
	 *
	 * @param int $mode
	 * @param array $user_ids
	 * @param bool $retain_username
	 */
	public function delete_user($mode, $user_ids, $retain_username)
	{
		if ($mode == 'retain')
		{
			$usernames = array();
			if ($retain_username !== false)
			{
				$sql = 'SELECT user_id, username FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$usernames[(int) $row['user_id']] = $row['username'];
				}
				$this->db->sql_freeresult($result);
			}
			foreach ($user_ids as $uid)
			{
				$update_array = array('user_id' => ANONYMOUS);
				if ($retain_username !== false)
				{
					$update_array['entry_username'] = $usernames[$uid];
				}
				$sql = "UPDATE {$this->tables['entries']} SET " . $this->db->sql_build_array('UPDATE', $update_array) . " WHERE user_id = $uid";
				$this->db->sql_query($sql);
			}
		}
		else if ($mode == 'remove')
		{
			$surveys = array();
			$sql = "SELECT s_id FROM {$this->tables['entries']} WHERE " . $this->db->sql_in_set('user_id', $user_ids);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$surveys[] = $row['s_id'];
			}
			$this->db->sql_freeresult($result);
			foreach (array_unique($surveys) as $survey_id)
			{
				$this->load_survey($survey_id, true);
				foreach ($this->entries as $entry_id => $entry)
				{
					if (in_array($entry['user_id'], $user_ids))
					{
						$this->delete_entry($entry_id);
					}
				}
			}
		}
	}
}
