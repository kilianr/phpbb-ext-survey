<?php
/**
* permissions_survey [English]
*
* @package language
* @copyright (c) 2015 kilianr
* @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Define categories and permission types
$lang = array_merge($lang, array(
	'ACL_F_CREATE_SURVEY'	=> 'Can create surveys',
	'ACL_F_ANSWER_SURVEY'	=> 'Can answer surveys',
));
