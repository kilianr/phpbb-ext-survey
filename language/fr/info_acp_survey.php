<?php
/**
*
* info_acp_survey [French]
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

$lang = array_merge($lang, array(
	'ACP_SURVEY'					=> 'Questionnaire',
	'ACP_SURVEY_DEFAULT_SETTINGS'	=> 'Valeurs par défaut',
	'ACP_SURVEY_SETTINGS'			=> 'Paramètres',
	'ACP_SURVEY_SETTINGS_EXPLAIN'	=> 'Définissez ici les paramètres par défaut pour les nouveaux questionnaires.',
));
