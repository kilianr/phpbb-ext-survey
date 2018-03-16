<?php

/**
 *
 * @package survey
 * @copyright (c) 2018 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\migrations;

class fix_permissions_3_readd_orig extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kilianr\survey\migrations\fix_permissions_2_delete_prev');
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('f_survey_create', false, 'f_survey_createtmp')),
			array('permission.add', array('f_survey_answer', false, 'f_survey_answertmp')),
			array('permission.add', array('m_survey', false)),
		);
	}
}
