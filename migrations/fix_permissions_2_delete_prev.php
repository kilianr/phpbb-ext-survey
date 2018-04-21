<?php

/**
 *
 * @package survey
 * @copyright (c) 2018 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\migrations;

class fix_permissions_2_delete_prev extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kilianr\survey\migrations\fix_permissions_1_copy_tmp');
	}

	public function update_data()
	{
		return array(
			array('permission.remove', array('f_survey_create', true)),
			array('permission.remove', array('f_survey_answer', true)),
		);
	}
}
