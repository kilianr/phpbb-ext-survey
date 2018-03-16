<?php

/**
 *
 * @package survey
 * @copyright (c) 2018 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\migrations;

class fix_permissions_4_delete_tmp extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kilianr\survey\migrations\fix_permissions_3_readd_orig');
	}

	public function update_data()
	{
		return array(
			array('permission.remove', array('f_survey_createtmp', false,)),
			array('permission.remove', array('f_survey_answertmp', false)),
		);
	}
}
