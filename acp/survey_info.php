<?php

/**
 *
 * @package survey
 * @copyright (c) 2015 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\acp;

class survey_info
{
	function module()
	{
		return array(
			'filename'	=> '\kilianr\survey\acp\survey_module',
			'title'		=> 'ACP_SURVEY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'settings'	=> array('title' => 'ACP_SURVEY_SETTINGS', 'auth' => 'ext_kilianr/survey && acl_a_board', 'cat' => array('ACP_SURVEY')),
			),
		);
	}
}
