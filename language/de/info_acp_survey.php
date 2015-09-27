<?php
/**
*
* info_acp_survey [German]
*
* @package language
* @copyright (c) 2015 kilianr
* @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
*/

use kilianr\survey\functions\survey;

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

$lang = array_merge($lang, array(
	'ACP_SURVEY'					=> 'Fragebogen',
	'ACP_SURVEY_DEFAULT_SETTINGS'	=> 'Standard-Werte',
	'ACP_SURVEY_SETTINGS'			=> 'Einstellungen',
	'ACP_SURVEY_SETTINGS_EXPLAIN'	=> 'Hier können die Standard-Einstellungen für neue Fragebögen festgelegt werden.',
));
