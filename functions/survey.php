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
	public static $SHOW_ORDER_TYPES = array(
		'ALPHABETICAL_USERNAME'				=> 0,
		'RESPONSE_TIME'						=> 1,
		'ALPHABETICAL_FIRST_ANSWER'			=> 2,
		'ALPHABETICAL_FIRST_ANSWER_REVERSE'	=> 3,
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

	var $topic_id;
	var $forum_id;
	var $topic_poster;
	var $survey_enabled = 0;
	var $settings;
	var $survey_questions;
	var $survey_entries;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\auth\auth $auth, $surveys_table, $questions_table, $question_choices_table, $entries_table, $answers_table)
	{
		$this->settings = array(
			's_id'					=> 0,
			'caption'				=> 0,
			'show_order'			=> 0,
			'allow_change_answer'	=> 0,
			'allow_multiple_answer'	=> 0,
			'hide_results'			=> 0,
			'start_time'			=> 0,
			'stop_time'				=> null,
		);

		$this->survey_questions = array();
		$this->survey_entries = array();

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
	}

	/**
	 * Loads all available data on a survey from the database. Returns false if the topic does not exist.
	 *
	 * @param int $topic_id
	 * @param int $forum_id
	 * @param int $topic_poster
	 * @return bool success
	 */
	public function load_survey($topic_id, $forum_id, $topic_poster)
	{
		$db = $this->db;

		$this->topic_id = $topic_id;
		$this->forum_id = $forum_id;
		$this->topic_poster = $topic_poster;
		$this->survey_questions = array();
		$this->survey_entries = array();

		$sql = 'SELECT survey_enabled FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		if (!$row = $db->sql_fetchrow($result))
		{
			// topic doesn't exist
			return false;
		}
		$this->survey_enabled = $row['survey_enabled'];

		// load survey settings
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
		$sql .= ' FROM ' . $this->tables['surveys'] . '
				WHERE topic_id = ' . $topic_id;
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

		// load questions for this survey
		$sql = 'SELECT q_id, label, type, sum_value, sum_type, sum_by, cap
				FROM ' . $this->tables['questions'] . '
				WHERE s_id=' . $this->settings['s_id'] . '
				ORDER BY q_id ASC';
		$result = $db->sql_query($sql);
		$this->survey_questions = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$this->survey_questions[$row['q_id']] = $row;
			// load question choices
			$sql = 'SELECT c_id, text, sum
					FROM ' . $this->tables['question_choices'] . '
					WHERE q_id=' . $row['q_id'] . '
					ORDER BY c_id ASC';
			$result2 = $db->sql_query($sql);
			$this->survey_questions[$row['q_id']]['choices'] = array();
			while ($row2 = $db->sql_fetchrow($result2))
			{
				$this->survey_questions[$row['q_id']]['choices'][$row2['c_id']] = $row2;
			}
		}

		// load entries
		$sql = 'SELECT entry_id, user_id
				FROM ' . $this->tables['entries'] . '
				WHERE s_id=' . $this->settings['s_id'] . '
				ORDER BY entry_id';
		$result = $db->sql_query($sql);
		$this->survey_entries = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$this->survey_entries[$row['entry_id']] = $row;
			// load answers
			$sql = 'SELECT q_id, answer
					FROM ' . $this->tables['answers'] . '
					WHERE entry_id=' . $row['entry_id'];
			$result2 = $db->sql_query($sql);
			$this->survey_entries[$row['entry_id']]['answers'] = array();
			while ($row2 = $db->sql_fetchrow($result2))
			{
				$this->survey_entries[$row['entry_id']]['answers'][$row2['q_id']] = $row2['answer'];
			}
		}

		return true;
	}

	/**
	 * Checks if the survey can be accessed at all
	 * Called BEFORE load_survey()!
	 *
	 * @param int $forum_id
	 * @return boolean
	 */
	public function can_access($forum_id)
	{
		return $this->auth->acl_get('f_survey', $forum_id) || $this->auth->acl_get('m_edit', $forum_id);
	}

	/**
	 * Checks if the user is owner of the survey
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function is_owner($user_id)
	{
		return $this->topic_poster == $user_id || $this->auth->acl_gets('f_edit', 'm_edit', $this->forum_id);
	}

	/**
	 * Checks if the user is already participating
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function is_participating($user_id)
	{
		foreach ($this->survey_entries as $entry)
		{
			if ($entry['user_id'] == $user_id)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks if the user can participate in the survey
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function has_right_to_participate($user_id)
	{
		return !$this->is_closed() && ($this->auth->acl_get('f_reply', $this->forum_id) || $this->is_participating($user_id));
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
		//TODO: $user->data['is_registered'], !$locked
		if (!$this->settings['allow_multiple_answer'] && $this->is_participating($entry_user_id))
		{
			return false;
		}
		if ($this->is_owner($real_user_id))
		{
			return true;
		}
		if ($this->is_closed())
		{
			return false;
		}
		if (!$this->auth->acl_get('f_reply', $this->forum_id))
		{
			return false;
		}
		if ($real_user_id != $entry_user_id)
		{
			return false;
		}
		return true;
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
		//TODO: $user->data['is_registered'], !$locked
		if (!$this->is_participating($entry_user_id))
		{
			return false;
		}
		if ($this->is_owner($real_user_id))
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
		if ($real_user_id != $entry_user_id)
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
		if ($entry_id != '' && isset($this->survey_entries[$entry_id]))
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
		if ($question_id != '' && isset($this->survey_questions[$question_id]))
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
		foreach ($this->survey_questions as $question_id => $question)
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
	 * The $new_settings can contain caption, show_order, allow_change_answer, allow_multiple_answer, hide_results and stop_time
	 * It MUST NOT contain s_id, topic_id, and start_time
	 *
	 * @param array $new_settings
	 */
	public function change_config($new_settings)
	{
		$sql = 'UPDATE ' . $this->tables['surveys'] . ' SET ' . $this->db->sql_build_array('UPDATE', $new_settings) . ' WHERE s_id = ' . $this->settings['s_id'];
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
		$sql = 'INSERT INTO ' . $this->tables['questions'] . ' ' . $this->db->sql_build_array('INSERT', $question);
		$this->db->sql_query($sql);
		$question_id = $this->db->sql_nextid();
		$this->survey_questions[$question_id] = $question;
		foreach ($choices as $choice)
		{
			$insert_choice = array(
				'q_id'	=> $question_id,
				'text'	=> $choice,
				'sum'	=> 0,
			);
			$sql = 'INSERT INTO ' . $this->tables['question_choices'] . ' ' . $this->db->sql_build_array('INSERT', $insert_choice);
			$this->db->sql_query($sql);
			$choice_id = $this->db->sql_nextid();
			unset($insert_choice['q_id']);
			$insert_choice['c_id'] = $choice_id;
			$this->survey_questions[$question_id]['choices'][$choice_id] = $insert_choice;
		}
		//TODO: Sums
	}

	/**
	 * Delete question
	 * The question must exist
	 *
	 * @param int $question_id
	 */
	public function delete_question($question_id)
	{
		$sql = 'DELETE FROM ' . $this->tables['answers'] . ' WHERE q_id=' . $question_id;
		$this->db->sql_query($sql);
		foreach ($this->survey_entries as $entry_id => $entry)
		{
			if (isset($this->survey_entries[$entry_id]['answers'][$question_id]))
			{
				unset($this->survey_entries[$entry_id]['answers'][$question_id]);
			}
		}
		$sql = 'DELETE FROM ' . $this->tables['question_choices'] . ' WHERE q_id=' . $question_id;
		$this->db->sql_query($sql);
		$sql = 'DELETE FROM ' . $this->tables['questions'] . ' WHERE q_id=' . $question_id;
		$this->db->sql_query($sql);
		unset($this->survey_questions[$question_id]);
		foreach ($this->survey_entries as $entry_id => $entry)
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
		//TODO: Sums
	}

	/**
	 * Modify question
	 * The question must exist
	 * The $question MUST NOT contain question_id, s_id and sum_value
	 * The $choices array contains only the texts
	 *
	 * @param array $question
	 * @param array $choices
	 */
	public function modify_question($question_id, $question, $choices)
	{
		$sql = 'UPDATE ' . $this->tables['questions'] . ' SET ' . $this->db->sql_build_array('UPDATE', $question) . ' WHERE q_id = ' .$question_id;
		$this->db->sql_query($sql);
		$sql = 'DELETE FROM ' . $this->tables['question_choices'] . ' WHERE q_id=' . $qid;
		$this->survey_questions[$question_id]['choices'] = array();
		foreach ($choices as $choice)
		{
			$insert_choice = array(
				'q_id'	=> $question_id,
				'text'	=> $choice,
				'sum'	=> 0,
			);
			$sql = 'INSERT INTO ' . $this->tables['question_choices'] . ' ' . $this->db->sql_build_array('INSERT', $insert_choice);
			$this->db->sql_query($sql);
			$choice_id = $this->db->sql_nextid();
			unset($insert_choice['q_id']);
			$insert_choice['c_id'] = $choice_id;
			$this->survey_questions[$question_id]['choices'][$choice_id] = $insert_choice;
			//TODO: Sums
		}
		//TODO: If type is choices, iterate through answers of this question and delete thoise with now inexistent choice
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
		$sql = 'INSERT INTO ' . $this->tables['entries'] . ' ' . $this->db->sql_build_array('INSERT', $entry);
		$this->db->sql_query($sql);
		$entry_id = $this->db->sql_nextid();
		$entry['entry_id'] = $entry_id;
		$this->survey_entries[$entry_id] = $entry;
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
		$sql = 'DELETE FROM ' . $this->tables['answers'] . ' WHERE entry_id=' . $entry_id;
		$this->db->sql_query($sql);
		$sql = 'DELETE FROM ' . $this->tables['entries'] . ' WHERE entry_id=' . $entry_id;
		$this->db->sql_query($sql);
		unset($this->survey_entries[$entry_id]);
		//TODO: Sums
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
			//TODO: Choices, Type checks
			//TODO: Sums
			if (isset($this->survey_entries[$entry_id]['answers'][$question_id]))
			{
				$sql = 'UPDATE ' . $this->tables['answers'] . ' SET ' . $this->db->sql_build_array('UPDATE', array('answer' => $answer)) . ' WHERE q_id=' . $question_id . ' AND entry_id=' . $entry_id;
				$this->db->sql_query($sql);
				$this->survey_entries[$entry_id]['answers'][$question_id] = $answer;
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
	public function add_answer($question_id, $entry_id, $answer)
	{
		//TODO: Choices, Type checks
		//TODO: Sums
		$insert_answer = array(
			'q_id'		=> $question_id,
			'entry_id'	=> $entry_id,
			'answer'	=> $answer,
		);
		$sql = 'INSERT INTO ' . $this->tables['answers'] . ' ' . $this->db->sql_build_array('INSERT', $insert_answer);
		$this->db->sql_query($sql);
		$this->survey_entries[$entry_id]['answers'][$question_id] = $answer;
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
			'allow_change_answer'	=> $this->config['kilianr_survey_default_allow_change_answer'],
			'allow_multiple_answer'	=> $this->config['kilianr_survey_default_allow_multiple_answer'],
			'hide_results'			=> $this->config['kilianr_survey_default_hide_results'],
			'start_time'			=> time(),
		);
		$sql = 'INSERT INTO ' . $this->tables['surveys'] . ' ' . $this->db->sql_build_array('INSERT', $inserts);
		$this->db->sql_query($sql);
	}

	/**
	 * Checks if the survey on given topic is enabled
	 *
	 * @param int $topic_id
	 * @return boolean
	 */
	public function is_enabled($topic_id)
	{
		$sql = 'SELECT survey_enabled FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $topic_id;
		$result = $this->db->sql_query($sql);
		if (!$row = $this->db->sql_fetchrow($result))
		{
			// topic doesn't exist
			return false;
		}
		return $row['survey_enabled'] == true;
	}

	/**
	 * Enable the survey
	 */
	public function enable()
	{
		$this->survey_enabled = true;
		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET survey_enabled = 1 WHERE topic_id=' . $this->topic_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Disable the survey
	 */
	public function disable()
	{
		$this->survey_enabled = false;
		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET survey_enabled = 0 WHERE topic_id=' . $this->topic_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Close the survey
	 */
	public function close()
	{
		$this->settings['stop_time'] = time();
		$sql = 'UPDATE ' . $this->tables['surveys'] . ' SET ' . $this->db->sql_build_array('UPDATE', array('stop_time' => $this->settings['stop_time'])) . ' WHERE s_id=' . $this->settings['s_id'];
		$this->db->sql_query($sql);
	}

	/**
	 * Reopen the survey
	 */
	public function reopen()
	{
		$this->settings['stop_time'] = null;
		$sql = 'UPDATE ' . $this->tables['surveys'] . ' SET stop_time = null WHERE s_id=' . $this->settings['s_id'];
		$this->db->sql_query($sql);
	}

	/**
	 * Checks if the survey is is closed
	 *
	 * @return boolean
	 */
	public function is_closed()
	{
		if ($this->settings['stop_time'] !== null && time() >= $this->settings['stop_time'])
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
			$sql = 'SELECT s_id FROM ' . $this->tables['surveys'] . ' WHERE topic_id=' . $tid;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sid = $row['s_id'];
				$sql = 'SELECT q_id FROM ' . $this->tables['questions'] . ' WHERE s_id=' . $sid;
				$result2 = $this->db->sql_query($sql);
				while ($row2 = $this->db->sql_fetchrow($result2))
				{
					$qid = $row2['q_id'];
					$sql = 'DELETE FROM ' . $this->tables['question_choices'] . ' WHERE q_id=' . $qid;
					$this->db->sql_query($sql);
					$sql = 'DELETE FROM ' . $this->tables['answers'] . ' WHERE q_id=' . $qid;
					$this->db->sql_query($sql);
				}
				$sql = 'DELETE FROM ' . $this->tables['questions'] . ' WHERE s_id=' . $sid;
				$this->db->sql_query($sql);
				$sql = 'DELETE FROM ' . $this->tables['entries'] . ' WHERE s_id=' . $sid;
				$this->db->sql_query($sql);
			}
			$sql = 'DELETE FROM ' . $this->tables['surveys'] . ' WHERE topic_id=' . $tid;
			$this->db->sql_query($sql);

			if (!$from_topic_delete)
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . ' SET survey_enabled = 0 WHERE topic_id=' . $tid;
				$this->db->sql_query($sql);
			}
		}
		if (!$from_topic_delete)
		{
			$this->survey_enabled = 0;
			unset($this->settings);
			unset($this->survey_questions);
			unset($this->survey_entries);
		}
	}
}
