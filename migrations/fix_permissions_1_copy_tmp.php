<?php

/**
 *
 * @package survey
 * @copyright (c) 2018 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\migrations;

class fix_permissions_1_copy_tmp extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kilianr\survey\migrations\version_1_0_0');
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('f_survey_createtmp', false, 'f_survey_create')),
			array('permission.add', array('f_survey_answertmp', false, 'f_survey_answer')),
		);
	}
}
