<?php

/**
 *
 * @package survey
 * @copyright (c) 2015 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\migrations;

class legacy_data_from_phpbb30 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\gold', '\kilianr\survey\migrations\convert_from_phpbb30');
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('f_survey_design', false)),
			array('permission.add', array('f_survey_takeforothers', false)),
			array('permission.add', array('f_survey_viewhiddenresults', false)),
			array('config.add', array('survey_version', 0)),
		);
	}
}
