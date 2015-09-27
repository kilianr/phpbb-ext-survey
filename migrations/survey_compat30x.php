<?php

/**
 *
 * @package survey
 * @copyright (c) 2015 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\migrations;

use kilianr\survey\functions\survey;

class survey_compat30x extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array();
	}

	public function effectively_installed()
	{
		return isset($this->config['kilianr_survey_default_show_order']);
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'surveys' => array(
					'COLUMNS'		=> array(
						's_id'					=> array('UINT', null, 'auto_increment'),
						'topic_id'				=> array('UINT', 0),
						'caption'				=> array('VCHAR_UNI', ''),
						'show_order'			=> array('UINT:1', survey::$SHOW_ORDER_TYPES['ALPHABETICAL_USERNAME']),
						'reverse_order'			=> array('BOOL', 0),
						'allow_change_answer'	=> array('BOOL', 1),
						'allow_multiple_answer'	=> array('BOOL', 0),
						'visibility'			=> array('UINT:1', survey::$VISIBILITY_TYPES['SHOW_EVERYTHING']),
						'start_time'			=> array('TIMESTAMP', 0),
						'stop_time'				=> array('TIMESTAMP', null),
						'topic_poster_right'	=> array('UINT:1', survey::$TOPIC_POSTER_RIGHTS['WRITE_OWNER']),
					),
					'PRIMARY_KEY'	=> 's_id',
					'KEYS'			=> array(
						's_id' 					=> array('INDEX', 's_id'),
						'topic_id' 				=> array('INDEX', 'topic_id'),
					),
				),
				$this->table_prefix . 'survey_questions' => array(
					'COLUMNS'		=> array(
						'q_id' 					=> array('UINT', null, 'auto_increment'),
						's_id'					=> array('UINT', 0),
						'label'					=> array('VCHAR_UNI', ''),
						'example_answer'		=> array('VCHAR_UNI', ''),
						'type'					=> array('UINT:1', 0),
						'random_choice_order'	=> array('BOOL', 0),
						'sum_value'				=> array('DECIMAL:11', 0),
						'sum_type'				=> array('UINT:1', 0),
						'sum_by'				=> array('VCHAR_UNI', ''),
						'average'				=> array('BOOL', 0),
						'cap'					=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'q_id',
					'KEYS'			=> array(
						'q_id' 					=> array('INDEX', 'q_id'),
						's_id' 					=> array('INDEX', 's_id'),
					),
				),
				$this->table_prefix . 'survey_q_choices' => array(
					'COLUMNS'		=> array(
						'c_id' 					=> array('UINT', null, 'auto_increment'),
						'q_id'					=> array('UINT', 0),
						'text'					=> array('VCHAR_UNI', ''),
						'sum'					=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'c_id',
					'UNIQUE'		=> array('q_id', 'text'),
					'KEYS'			=> array(
						'c_id' 		=> array('INDEX', 'c_id'),
						'q_id' 		=> array('INDEX', 'q_id'),
					),
				),
				$this->table_prefix . 'survey_entries' => array(
					'COLUMNS'		=> array(
						'entry_id'				=> array('UINT', null, 'auto_increment'),
						's_id'					=> array('UINT', 0),
						'user_id'				=> array('UINT', 0),
						'entry_username'		=> array('VCHAR_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'entry_id',
					'KEYS'			=> array(
						'entry_id'				=> array('INDEX', 'entry_id'),
						's_id'					=> array('INDEX', 's_id'),
						'user_id'				=> array('INDEX', 'user_id'),
					),
				),
				$this->table_prefix . 'survey_answers' => array(
					'COLUMNS'		=> array(
						'q_id' 					=> array('UINT', 0),
						'entry_id'				=> array('UINT', 0),
						'answer'				=> array('TEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> array('q_id', 'entry_id'),
					'KEYS'			=> array(
						'q_id' 					=> array('INDEX', 'q_id'),
						'entry_id' 				=> array('INDEX', 'entry_id'),
						'ta_id'					=> array('INDEX', array('q_id', 'entry_id')),
					),
				),
			),
			'add_columns' => array(
				TOPICS_TABLE		=> array(
					'survey_enabled'			=> array('BOOL', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'surveys',
				$this->table_prefix . 'survey_questions',
				$this->table_prefix . 'survey_q_choices',
				$this->table_prefix . 'survey_entries',
				$this->table_prefix . 'survey_answers',
			),
			'drop_columns' => array(
				TOPICS_TABLE => array('survey_enabled',),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('f_survey_create', false, 'f_poll')),
			array('permission.add', array('f_survey_answer', false, 'f_reply')),
			array('config.add', array('kilianr_survey_default_show_order', survey::$SHOW_ORDER_TYPES['ALPHABETICAL_USERNAME'])),
			array('config.add', array('kilianr_survey_default_reverse_order', false)),
			array('config.add', array('kilianr_survey_default_allow_change_answer', true)),
			array('config.add', array('kilianr_survey_default_allow_multiple_answer', false)),
			array('config.add', array('kilianr_survey_default_visibility', survey::$VISIBILITY_TYPES['SHOW_EVERYTHING'])),
			array('config.add', array('kilianr_survey_default_topic_poster_right', survey::$TOPIC_POSTER_RIGHTS['WRITE_OWNER'])),
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'ACP_SURVEY')),
			array('module.add', array('acp', 'ACP_SURVEY', array(
					'module_basename'	=> '\kilianr\survey\acp\survey_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
