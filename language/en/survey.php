<?php
/**
*
* survey [English]
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
	'SURVEY'							=> 'Survey',
	'SURVEY_ADD'						=> 'Survey',
	'SURVEY_ADD_DESC'					=> 'Attach a survey to this topic',
	'SURVEY_ADD_REACTIVATE'				=> 'reactivate survey',
	'SURVEY_ADD_REACTIVATE_EXPLAIN'		=> 'Data of a previously disabled survey is available. If you reactivate it, the previously added questions and answers will be available again.',
	'SURVEY_ADD_USERS'					=> 'Invite users',
	'SURVEY_ALLOW_CHANGE_ANSWER'		=> 'Allow users to modify their answers',
	'SURVEY_ALLOW_MULTIPLE_ANSWER'		=> 'Allow users to answer multiple times',
	'SURVEY_AVERAGE'					=> 'Average',
	'SURVEY_AVERAGE_DESC'				=> 'Show the average of sum divided by answers (depending on sum-type in percent or not)',
	'SURVEY_CAP'						=> 'Cap',
	'SURVEY_CAP_DESC'					=> 'If summing up, users can no longer respond if their answer would cause the sum to exceed the cap. Leave blank for no cap.',
	'SURVEY_CAP_EXEEDED'				=> 'The cap of question %s has been exceeded (including your answer). Thus, your answer was not saved.',
	'SURVEY_CAPTION'					=> 'Caption',
	'SURVEY_CHOICES'					=> 'Choices',
	'SURVEY_CHOICES_DESC'				=> 'If drop downmenu or multiple choice menu are chosen, specify the choices here separated by commas.',
	'SURVEY_CLOSE'						=> 'Close survey',
	'SURVEY_CLOSE_CONFIRM'				=> 'Are you sure you want to close the survey?',
	'SURVEY_CLOSED'						=> 'The survey has been closed.',
	'SURVEY_DATEFORMAT'					=> 'Y-m-d H:i',
	'SURVEY_DELETE_ALL'					=> 'Delete all data',
	'SURVEY_DELETE_ALL_CONFIRM'			=> 'Do you really want to delete this survey? All stored data (questions, answers) will be lost and cannot be restored.',
	'SURVEY_DELETE_ALL_EXPLAIN'			=> 'All data related to this survey will be deleted.',
	'SURVEY_DELETE_ANSWER'				=> 'Delete answer',
	'SURVEY_DELETE_EXPLAIN'				=> 'Here you can disable or delete the whole survey',
	'SURVEY_DELETE_QUESTION'			=> 'Delete questions',
	'SURVEY_DELETE_QUESTION_CONFIRM'	=> 'Do you really want to delete the question %s?',
	'SURVEY_DELETE_WHOLE'				=> 'Delete whole survey',
	'SURVEY_DESC'						=> 'Since %s, this topic has a survey attached that can be used to perform an iquiry with multiple users.',
	'SURVEY_DESC_STOP'					=> 'The survey can be answered until %s.',
	'SURVEY_DISABLE'					=> 'Disable only',
	'SURVEY_DISABLE_CONFIRM'			=> 'Do you really want to disable the survey? The data (questions, answers) will be kept in the database and the survey can be reactivated anytime',
	'SURVEY_DISABLE_EXPLAIN'			=> 'The survey will not be displayed in the topic anymore, but the data (questions, answers) will be kept in the database.',
	'SURVEY_ENTRY_DELETION_CONFIRM'		=> 'Do you really want to cancel your membership in this survey?',
	'SURVEY_HIDE'						=> 'Hide survey',
	'SURVEY_HIDE_DESC'					=> 'Hide answers and results',
	'SURVEY_HIDE_DESC_' . survey::$HIDE_TYPES['NO_HIDE']			=> 'Hide nothing',
	'SURVEY_HIDE_DESC_' . survey::$HIDE_TYPES['ANONYMIZE']			=> 'Anonymize',
	'SURVEY_HIDE_DESC_' . survey::$HIDE_TYPES['HIDE_ENTRIES']		=> 'Hide answers (sums will be displayed)',
	'SURVEY_HIDE_DESC_' . survey::$HIDE_TYPES['HIDE_EVERYTHING']	=> 'Hide everything',
	'SURVEY_HIDE_RESULTS_DESC_OWNER'	=> 'The answers of this survey are hidden, as topic-starter or due to your permissions you can still see all entries.',
	'SURVEY_HIDE_RESULTS_DESC_USER'		=> 'Because the answers of this survey are hidden, you can only see your own entries.',
	'SURVEY_HIDDEN'						=> 'hidden',
	'SURVEY_INVALID_ANSWER'				=> 'Invalid answer.',
	'SURVEY_INVALID_CAPTION'			=> 'Invalid caption.',
	'SURVEY_INVALID_HIDE_TYPE'			=> 'Invalid visibilty type.',
	'SURVEY_INVALID_QUESTION_CHOICES'	=> 'Invalid question: No choices set.',
	'SURVEY_INVALID_QUESTION_NO_LABEL'	=> 'Invalid question: No label set.',
	'SURVEY_INVALID_QUESTION_SUM_BY'	=> 'Invalid question: Text for counting is missing.',
	'SURVEY_INVALID_QUESTION_SUM_TYPE'	=> 'Invalid question: Unknown sum type.',
	'SURVEY_INVALID_QUESTION_TYPE'		=> 'Invalid question: Unknown type.',
	'SURVEY_INVALID_SHOW_ORDER_TYPE'	=> 'Invalid ordering type.',
	'SURVEY_INVALID_STOPDATE'			=> 'Invalid stop date %s. Please enter date in YYYY-MM-DD hh:mm format, e.g. 2042-07-01 13:37. The date cannot be in the past.',
	'SURVEY_IS_CLOSED'					=> 'The survey is closed since %s.',
	'SURVEY_IS_CLOSED_DESC_OWNER'		=> 'The survey is closed since %s, as topic-starter or due to your permissions you can still edit.',
	'SURVEY_IS_DISABLED'				=> 'The survey is disabled.',
	'SURVEY_IS_NOT_CLOSED'				=> 'The survey is not closed.',
	'SURVEY_LABEL'						=> 'Label',
	'SURVEY_MANAGE_QUESTIONS'			=> 'Manage questions',
	'SURVEY_MANAGE_QUESTIONS_DESC'		=> 'Here you can add new questions or modify existing ones.',
	'SURVEY_NO_ENTRIES'					=> 'No answers have been filled in yet.',
	'SURVEY_NO_QUESTIONS'				=> 'No questions have been added yet.',
	'SURVEY_OVERVIEW'					=> 'Survey-overview',
	'SURVEY_QUESTION_ADD_AS_NEW'		=> 'Add as new question',
	'SURVEY_QUESTION_ALREADY_ADDED'		=> 'The question %s has already been added to this survey',
	'SURVEY_QUESTION_LOAD'				=> 'Load question',
	'SURVEY_QUESTION_MODIFY'			=> 'Modify question',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['NO_SUM']				=> 'don\'t sum up',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['NUMBER_OF_RESPONSES']	=> 'count the responses',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['SUM_OF_NUMBERS']		=> 'sum the total of the numbers in the responses',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['MATCHING_TEXT']			=> 'count the responses matching a text (specify text below)',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['NORMAL_TEXT_BOX']	=> 'normal text box',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['LARGE_TEXT_BOX']	=> 'large text box',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['NUMBER']			=> 'number',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['CHECKBOX']			=> 'checkbox',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DROP_DOWN_MENU']	=> 'drop down menu',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['MULTIPLE_CHOICE']	=> 'multiple choice menu',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATE']				=> 'date',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['TIME']				=> 'time',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATETIME']			=> 'date & time with timezone',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATETIME_LOCAL']	=> 'date & time without timezone',
	'SURVEY_REOPEN'						=> 'Reopen survey',
	'SURVEY_REOPEN_CONFIRM'				=> 'Are you sure you want to reopen the survey?',
	'SURVEY_RUN'						=> 'Execute',
	'SURVEY_SETTINGS'					=> 'Edit Settings',
	'SURVEY_SETTINGS_EXPLAIN'			=> 'Here you can edit the basic settings of the survey.',
	'SURVEY_SHOW'						=> 'Show survey',
	'SURVEY_SHOW_ORDER'					=> 'Ordering',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['ALPHABETICAL_USERNAME']				=> 'alphabetical by username',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['RESPONSE_TIME']						=> 'order of response of the users',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['ALPHABETICAL_FIRST_ANSWER']			=> 'alphabetical by text of first answer',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['ALPHABETICAL_FIRST_ANSWER_REVERSE']	=> 'reverse alphabetical by text of first answer',
	'SURVEY_STOP_TIME'					=> 'Date until which survey can be answered (format: YYYY-MM-DD hh:mm)',
	'SURVEY_SUM'						=> 'Sum',
	'SURVEY_SUM_BY'						=> 'Text in answers to be counted',
	'SURVEY_SUM_BY_DESC'				=> 'Count the responses matching this text, if the sum-type is chosen accordingly.',
	'SURVEY_SUM_TYPE'					=> 'Sum-Type',
	'SURVEY_TYPE'						=> 'Type',
	'SURVEY_USER_EXISTS'				=> 'The user %s is already a member of this survey.',
	'SURVEY_USERS_EXIST'				=> 'The selected users are already members of this survey.',
	'SURVEY_VIEWTOPIC_EXPLAIN'			=> 'This topic already contains an active survey. To deactivate or to delete the whole survey, use the <em>settings</em> tab in the topic view',
));
