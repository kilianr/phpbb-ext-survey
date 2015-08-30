<?php
/**
*
* survey [German]
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
	'SURVEY'							=> 'Fragebogen',
	'SURVEY_ADD'						=> 'Fragebogen',
	'SURVEY_ADD_DESC'					=> 'Diesem Thema einen Fragebogen hinzufügen',
	'SURVEY_ADD_QUESTIONS'				=> 'Fragen hinzufügen',
	'SURVEY_ADD_QUESTIONS_EXPLAIN'		=> 'Hiermit fügst du neue Fragen in den Bogen ein.',
	'SURVEY_ADD_REACTIVATE'				=> 'Fragebogen reaktivieren',
	'SURVEY_ADD_REACTIVATE_EXPLAIN'		=> 'Es existieren noch Daten eines zuvor deaktivierten Fragebogens. Wenn du diesen reaktivierst, sind die alten Antworten und Fragen wieder verfügbar.',
	'SURVEY_ADD_USERS'					=> 'Benutzer hinzufügen',
	'SURVEY_ALLOW_CHANGE_ANSWER'		=> 'Ändern der Antworten erlauben',
	'SURVEY_ALLOW_MULTIPLE_ANSWER'		=> 'Mehrfachantworten erlauben',
	'SURVEY_CAP'						=> 'Grenze',
	'SURVEY_CAPTION'					=> 'Überschrift',
	'SURVEY_CHOICES'					=> 'Auswahlmöglichkeiten',
	'SURVEY_CLOSE'						=> 'Fragebogen schließen',
	'SURVEY_CLOSE_CONFIRM'				=> 'Bist du sicher, dass du den Fragebogen schließen möchtest?',
	'SURVEY_CLOSED'						=> 'Der Fragebogen wurde geschlossen',
	'SURVEY_DELETE_ALL'					=> 'Alle Daten löschen',
	'SURVEY_DELETE_ALL_CONFIRM'			=> 'Willst du den Fragebogen wirklich vollständig löschen? Die Gespeicherten Daten (Fragen, Antworten) gehen verloren und können nicht wiederhergestellt werden.',
	'SURVEY_DELETE_ALL_EXPLAIN'			=> 'Alle Daten dieses Fragebogens werden aus der Datenbank gelöscht.',
	'SURVEY_DELETE_ANSWER'				=> 'Antwort löschen',
	'SURVEY_DELETE_EXPLAIN'				=> 'Hier kannst den kompletten Fragebogen deaktivieren oder löschen',
	'SURVEY_DELETE_QUESTION'			=> 'Frage löschen',
	'SURVEY_DELETE_QUESTION_CONFIRM'	=> 'Möchtest du wirklich die Frage %s löschen?',
	'SURVEY_DELETE_WHOLE'				=> 'Gesamten Fragebogen löschen',
	'SURVEY_DESC'						=> 'Dieses Thema enthält einen Fragebogen, der dazu verwendet werden kann einen Umfrage unter verschiedenen Usern durchzuführen.',
	'SURVEY_DISABLE'					=> 'Nur deaktivieren',
	'SURVEY_DISABLE_CONFIRM'			=> 'Willst du den Fragebogen wirklich deaktivieren? Die Gespeicherten Daten (Fragen, Antworten) bleiben in der Datenbank gespeichert und der Fragebogen kann jederzeit wieder reaktiviert werden.',
	'SURVEY_DISABLE_EXPLAIN'			=> 'Der Fragebogen wird im Thema nicht mehr angezeigt, die gespeicherten Daten (Fragen, Antworten) bleiben jedoch in der Datenbank gespeichert.',
	'SURVEY_ENTRY_DELETION_CONFIRM'		=> 'Möchtest du deine Teilnahme an diesem Fragebogen wirklich beenden?',
	'SURVEY_HIDE'						=> 'Fragebogen verstecken',
	'SURVEY_HIDE_RESULTS'				=> 'Antworten und Ergebnisse verstecken',
	'SURVEY_HIDE_RESULTS_DESC_USER'		=> 'Die Antworten und Ergebnisse des Fragebogens sind versteckt, daher kannst du nur deine eigenen Einträge sehen.',
	'SURVEY_HIDE_RESULTS_DESC_OWNER'	=> 'Die Antworten und Ergebnisse des Fragebogens sind versteckt, als Topicstarter oder aufgrund deiner Rechte kannst du dennoch alle Einträge sehen.',
	'SURVEY_INVALID_QUESTION'			=> 'Frage ungültig.',
	'SURVEY_IS_CLOSED'					=> 'Der Fragebogen ist geschlossen.',
	'SURVEY_IS_DISABLED'				=> 'Der Fragebogen ist deaktiviert.',
	'SURVEY_IS_NOT_CLOSED'				=> 'Der Fragebogen ist nicht geschlossen.',
	'SURVEY_LABEL'						=> 'Name',
	'SURVEY_NO_ENTRIES'					=> 'Es wurden noch keine Antworten eingetragen.',
	'SURVEY_NO_QUESTIONS'				=> 'Es wurden noch keine Fragen hinzugefügt.',
	'SURVEY_OVERVIEW'					=> 'Fragebogen-Übersicht',
	'SURVEY_QUESTION_ALREADY_ADDED'		=> 'Die Frage %s wurde diesem Fragebogen bereits hinzugefügt',
	'SURVEY_REOPEN'						=> 'Fragebogen wieder eröffnen',
	'SURVEY_REOPEN_CONFIRM'				=> 'Bist du sicher, dass du den Fragebogen wiedereröffnen möchtest?',
	'SURVEY_RUN'						=> 'Ausführen',
	'SURVEY_SETTINGS'					=> 'Einstellungen',
	'SURVEY_SETTINGS_EXPLAIN'			=> 'Hier kannst du die grundlegenden Einstellungen des Fragebogens einstellen.',
	'SURVEY_SHOW'						=> 'Fragebogen anzeigen',
	'SURVEY_SHOW_ORDER'					=> 'Reihenfolge',
	'SURVEY_STOP_TIME'					=> 'Datum, bis zu dem der Fragebogen offen ist',
	'SURVEY_SUM'						=> 'Summe',
	'SURVEY_SUM_BY'						=> 'Summieren bei',
	'SURVEY_SUM_TYPE'					=> 'Summen-Typ',
	'SURVEY_TYPE'						=> 'Typ',
	'SURVEY_USER_EXISTS'				=> 'Der Benutzer %s ist bereits Mitglied des Fragebogens.',
	'SURVEY_USERS_EXIST'				=> 'Alle Benutzer der gewählten Gruppen sind bereits Mitglied des Fragebogens.',
	'SURVEY_VIEWTOPIC_EXPLAIN'			=> 'Dieses Thema enthält bereits einen aktiven Fragebogen. Um den gesamten Fragebogen zu deaktivieren oder zu löschen, verwende den Reiter <em>Einstellungen</em> in der Themenansicht',
));
