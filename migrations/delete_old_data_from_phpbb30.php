<?php

/**
 *
 * @package survey
 * @copyright (c) 2015 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\migrations;

class delete_old_data_from_phpbb30 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kilianr\survey\migrations\convert_from_phpbb30', '\kilianr\survey\migrations\legacy_data_from_phpbb30');
	}

	public function update_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'survey',
				$this->table_prefix . 'survey_answers',
			),
			'drop_columns' => array(
				TOPICS_TABLE => array('topic_survey',),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('permission.remove', array('f_survey_design', false)),
			array('permission.remove', array('f_survey_takeforothers', false)),
			array('permission.remove', array('f_survey_viewhiddenresults', false)),
			array('config.remove', array('survey_version')),
		);
	}
}
