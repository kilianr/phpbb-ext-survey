<?php
/**
* permissions_survey [French]
*
* @package language
* @copyright (c) 2018 pvu, kilianr
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
	'ACL_F_SURVEY_CREATE'	=> 'Peut créer des questionnaires',
	'ACL_F_SURVEY_ANSWER'	=> 'Peut répondre aux questionnaires',
	'ACL_M_SURVEY'			=> 'Peut gérer les questionnaires',
));
