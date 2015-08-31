<?php
/**
*
* survey [English]
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

$lang = array_merge($lang, array(
	'SURVEY'							=> 'Survey',
	'SURVEY_ADD'						=> 'Survey',
	'SURVEY_ADD_DESC'					=> 'Attach a survey to this topic',
	'SURVEY_ADD_QUESTIONS'				=> 'Propose new questions',
	'SURVEY_ADD_QUESTIONS_EXPLAIN'		=> 'Here you can propose new questions.',
	'SURVEY_ADD_REACTIVATE'				=> 'reactivate survey',
	'SURVEY_ADD_REACTIVATE_EXPLAIN'		=> 'Data of a previously disabled survey is available. If you reactivate it, the previously added questions and answers will be available again.',
	'SURVEY_ADD_USERS'					=> 'Invite users',
	'SURVEY_ALLOW_CHANGE_ANSWER'		=> 'Allow users to modify their answers',
	'SURVEY_ALLOW_MULTIPLE_ANSWER'		=> 'Allow users to answer multiple times',
	'SURVEY_CAP'						=> 'Cap',
	'SURVEY_CAPTION'					=> 'Caption',
	'SURVEY_CHOICES'					=> 'Choices',
	'SURVEY_CLOSE'						=> 'Close survey',
	'SURVEY_CLOSE_CONFIRM'				=> 'Are you sure you want to close the survey?',
	'SURVEY_CLOSED'						=> 'The survey has been closed.',
	'SURVEY_DELETE_ALL'					=> 'Delete all data',
	'SURVEY_DELETE_ALL_CONFIRM'			=> 'Do you really want to delete this survey? All stored data (questions, answers) will be lost and cannot be restored.',
	'SURVEY_DELETE_ALL_EXPLAIN'			=> 'All data related to this survey will be deleted.',
	'SURVEY_DELETE_ANSWER'				=> 'Delete answer',
	'SURVEY_DELETE_EXPLAIN'				=> 'Here you can disable or delete the whole survey',
	'SURVEY_DELETE_QUESTION'			=> 'Delete questions',
	'SURVEY_DELETE_QUESTION_CONFIRM'	=> 'Do you really want to delete the question %s?',
	'SURVEY_DELETE_WHOLE'				=> 'Delete whole survey',
	'SURVEY_DESC'						=> 'This topic has a survey attached that can be used to perform an iquiry with multiple users.',
	'SURVEY_DISABLE'					=> 'Disable only',
	'SURVEY_DISABLE_CONFIRM'			=> 'Do you really want to disable the survey? The data (questions, answers) will be kept in the database and the survey can be reactivated anytime',
	'SURVEY_DISABLE_EXPLAIN'			=> 'The survey will not be displayed in the topic anymore, but the data (questions, answers) will be kept in the database.',
	'SURVEY_ENTRY_DELETION_CONFIRM'		=> 'Do you really want to cancel your membership in this survey?',
	'SURVEY_HIDE'						=> 'Hide survey',
	'SURVEY_HIDE_RESULTS'				=> 'Hide answers and results',
	'SURVEY_HIDE_RESULTS_DESC_OWNER'	=> 'The answers and results of this survey are hidden, as topic-starter or due to your permissions you can still see all entries.',
	'SURVEY_HIDE_RESULTS_DESC_USER'		=> 'Because the answers and results of this survey are hidden, you can only see your own entries.',
	'SURVEY_INVALID_QUESTION'			=> 'Invalid question.',
	'SURVEY_IS_CLOSED'					=> 'The survey is closed.',
	'SURVEY_IS_CLOSED_DESC_OWNER'		=> 'The survey is closed, as topic-starter or due to your permissions you can still edit.',
	'SURVEY_IS_DISABLED'				=> 'The survey is disabled.',
	'SURVEY_IS_NOT_CLOSED'				=> 'The survey is not closed.',
	'SURVEY_LABEL'						=> 'Label',
	'SURVEY_NO_ENTRIES'					=> 'No answers have been filled in yet.',
	'SURVEY_NO_QUESTIONS'				=> 'No questions have been added yet.',
	'SURVEY_OVERVIEW'					=> 'Survey-overview',
	'SURVEY_QUESTION_ALREADY_ADDED'		=> 'The question %s has already been added to this survey',
	'SURVEY_REOPEN'						=> 'Reopen survey',
	'SURVEY_REOPEN_CONFIRM'				=> 'Are you sure you want to reopen the survey?',
	'SURVEY_RUN'						=> 'Execute',
	'SURVEY_SETTINGS'					=> 'Edit Settings',
	'SURVEY_SETTINGS_EXPLAIN'			=> 'Here you can edit the basic settings of the survey.',
	'SURVEY_SHOW'						=> 'Show survey',
	'SURVEY_SHOW_ORDER'					=> 'Ordering',
	'SURVEY_STOP_TIME'					=> 'Date until which survey can be answered',
	'SURVEY_SUM'						=> 'Sum',
	'SURVEY_SUM_BY'						=> 'Sum by',
	'SURVEY_SUM_TYPE'					=> 'Sum-Typ',
	'SURVEY_TYPE'						=> 'Typ',
	'SURVEY_USER_EXISTS'				=> 'The user %s is already a member of this survey.',
	'SURVEY_USERS_EXIST'				=> 'The selected users are already members of this survey.',
	'SURVEY_VIEWTOPIC_EXPLAIN'			=> 'This topic already contains an active survey. To deactivate or to delete the whole survey, use the <em>settings</em> tab in the topic view',
));
