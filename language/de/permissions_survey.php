<?php
/**
* permissions_survey [German]
*
* @package language
* @copyright (c) 2015 kilianr
* @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Define categories and permission types
$lang = array_merge($lang, array(
	'ACL_F_SURVEY_CREATE'	=> 'Kann Fragebogen erstellen',
	'ACL_F_SURVEY_ANSWER'	=> 'Kann Fragebogen beantworten',
	'ACL_M_SURVEY'			=> 'Kann Fragebogen verwalten',
));
