<?php
/**
*
* survey [German]
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
	'SURVEY_AVERAGE'					=> 'Durchschnitt',
	'SURVEY_AVERAGE_DESC'				=> 'Zeige den Durchschnitt Summe geteilt durch Antworten (je nach Summen-Typ in Prozent oder nicht)',
	'SURVEY_CAP'						=> 'Grenze',
	'SURVEY_CAP_DESC'					=> 'Falls summiert wird, können User nicht mehr antworten, sobald die Summe die Grenze überschreiten würde. Lass das Feld leer, um keine Grenze zu setzen.',
	'SURVEY_CAP_EXEEDED'				=> 'Die Grenze der Frage %s wurde (inklusive deiner Antwort) überschritten. Daher wurde deine Antwort nicht gespeichert.',
	'SURVEY_CAPTION'					=> 'Überschrift',
	'SURVEY_CHOICES'					=> 'Auswahlmöglichkeiten',
	'SURVEY_CHOICES_DESC'				=> 'Gibt hier die Auswahlmöglichkeiten mit Kommata getrennt an, falls Ausklapp-Menü oder Multiple-Choice-Menü gewählt ist.',
	'SURVEY_CLOSE'						=> 'Fragebogen schließen',
	'SURVEY_CLOSE_CONFIRM'				=> 'Bist du sicher, dass du den Fragebogen schließen möchtest?',
	'SURVEY_CLOSED'						=> 'Der Fragebogen wurde geschlossen',
	'SURVEY_DATEFORMAT'					=> 'Y-m-d H:i',
	'SURVEY_DELETE_ALL'					=> 'Alle Daten löschen',
	'SURVEY_DELETE_ALL_CONFIRM'			=> 'Willst du den Fragebogen wirklich vollständig löschen? Die Gespeicherten Daten (Fragen, Antworten) gehen verloren und können nicht wiederhergestellt werden.',
	'SURVEY_DELETE_ALL_EXPLAIN'			=> 'Alle Daten dieses Fragebogens werden aus der Datenbank gelöscht.',
	'SURVEY_DELETE_ANSWER'				=> 'Antwort löschen',
	'SURVEY_DELETE_EXPLAIN'				=> 'Hier kannst den kompletten Fragebogen deaktivieren oder löschen',
	'SURVEY_DELETE_QUESTION'			=> 'Frage löschen',
	'SURVEY_DELETE_QUESTION_CONFIRM'	=> 'Möchtest du wirklich die Frage %s löschen?',
	'SURVEY_DELETE_WHOLE'				=> 'Gesamten Fragebogen löschen',
	'SURVEY_DESC'						=> 'Seit %s enthält dieses Thema einen Fragebogen, der dazu verwendet werden kann einen Umfrage unter verschiedenen Usern durchzuführen.',
	'SURVEY_DESC_STOP'					=> 'Der Fragebogen kann bis %s ausgefüllt werden.',
	'SURVEY_DISABLE'					=> 'Nur deaktivieren',
	'SURVEY_DISABLE_CONFIRM'			=> 'Willst du den Fragebogen wirklich deaktivieren? Die Gespeicherten Daten (Fragen, Antworten) bleiben in der Datenbank gespeichert und der Fragebogen kann jederzeit wieder reaktiviert werden.',
	'SURVEY_DISABLE_EXPLAIN'			=> 'Der Fragebogen wird im Thema nicht mehr angezeigt, die gespeicherten Daten (Fragen, Antworten) bleiben jedoch in der Datenbank gespeichert.',
	'SURVEY_ENTRY_DELETION_CONFIRM'		=> 'Möchtest du deine Teilnahme an diesem Fragebogen wirklich beenden?',
	'SURVEY_HIDE'						=> 'Fragebogen verstecken',
	'SURVEY_HIDE_DESC'					=> 'Informationen verstecken',
	'SURVEY_HIDE_DESC_' . survey::$HIDE_TYPES['NO_HIDE']			=> 'Nichts verstecken',
	'SURVEY_HIDE_DESC_' . survey::$HIDE_TYPES['ANONYMIZE']			=> 'Anonymisieren',
	'SURVEY_HIDE_DESC_' . survey::$HIDE_TYPES['HIDE_ENTRIES']		=> 'Antworten verstecken (Summen werden angezeigt)',
	'SURVEY_HIDE_DESC_' . survey::$HIDE_TYPES['HIDE_EVERYTHING']	=> 'Alles verstecken',
	'SURVEY_HIDE_RESULTS_DESC_OWNER'	=> 'Die Antworten des Fragebogens sind versteckt, als Topicstarter oder aufgrund deiner Rechte kannst du dennoch alle Einträge sehen.',
	'SURVEY_HIDE_RESULTS_DESC_USER'		=> 'Die Antworten des Fragebogens sind versteckt, daher kannst du nur deine eigenen Einträge sehen.',
	'SURVEY_HIDDEN'						=> 'versteckt',
	'SURVEY_INVALID_QUESTION'			=> 'Frage ungültig.',
	'SURVEY_INVALID_STOPDATE'			=> 'Ungültiges Stop-Datum %s. Bitte gib das Datum im Format YYYY-MM-DD ss:mm ein, z.B. 2042-07-01 13:37. Das Datum darf nicht in der Vergangenheit liegen.',
	'SURVEY_IS_CLOSED'					=> 'Der Fragebogen ist seit %s geschlossen.',
	'SURVEY_IS_CLOSED_DESC_OWNER'		=> 'Der Fragebogen ist seit %s geschlossen, als Topicstarter oder aufgrund deiner Rechte kannst du dennoch weiterhin bearbeiten.',
	'SURVEY_IS_DISABLED'				=> 'Der Fragebogen ist deaktiviert.',
	'SURVEY_IS_NOT_CLOSED'				=> 'Der Fragebogen ist nicht geschlossen.',
	'SURVEY_LABEL'						=> 'Name',
	'SURVEY_NO_ENTRIES'					=> 'Es wurden noch keine Antworten eingetragen.',
	'SURVEY_NO_QUESTIONS'				=> 'Es wurden noch keine Fragen hinzugefügt.',
	'SURVEY_OVERVIEW'					=> 'Fragebogen-Übersicht',
	'SURVEY_QUESTION_ALREADY_ADDED'		=> 'Die Frage %s wurde diesem Fragebogen bereits hinzugefügt',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['NO_SUM']				=> 'Nichts zusammenzählen',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['NUMBER_OF_RESPONSES']	=> 'Zähle die Antworten',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['SUM_OF_NUMBERS']		=> 'Summiere die Zahlen in den Antworten',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['MATCHING_TEXT']			=> 'Zähle die Antworten gleich einem Text (gibt den Text unten an)',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['NORMAL_TEXT_BOX']	=> 'Normale Text-Box',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['LARGE_TEXT_BOX']	=> 'Große Text-Box',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['NUMBER']			=> 'Zahl',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['CHECKBOX']			=> 'Checkbox',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DROP_DOWN_MENU']	=> 'Ausklapp-Menü',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['MULTIPLE_CHOICE']	=> 'Multiple-Choice Menü',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATE']				=> 'Datum',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['TIME']				=> 'Uhrzeit',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATETIME']			=> 'Datum & Uhrzeit mit Zeitzone',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATETIME_LOCAL']	=> 'Datum & Uhrzeit ohne Zeitzone',
	'SURVEY_REOPEN'						=> 'Fragebogen wieder eröffnen',
	'SURVEY_REOPEN_CONFIRM'				=> 'Bist du sicher, dass du den Fragebogen wiedereröffnen möchtest?',
	'SURVEY_RUN'						=> 'Ausführen',
	'SURVEY_SETTINGS'					=> 'Einstellungen',
	'SURVEY_SETTINGS_EXPLAIN'			=> 'Hier kannst du die grundlegenden Einstellungen des Fragebogens einstellen.',
	'SURVEY_SHOW'						=> 'Fragebogen anzeigen',
	'SURVEY_SHOW_ORDER'					=> 'Sortierung der Antworten',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['ALPHABETICAL_USERNAME']				=> 'alphabetisch nach dem Benutzernamen',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['RESPONSE_TIME']						=> 'Reihenfolge, in der die Benutzer geantwortet haben',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['ALPHABETICAL_FIRST_ANSWER']			=> 'alphabetisch nach dem Text der ersten Antwort',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['ALPHABETICAL_FIRST_ANSWER_REVERSE']	=> 'umgekehrt alphabetisch nach dem Text der ersten Antwort',
	'SURVEY_STOP_TIME'					=> 'Datum, bis zu dem der Fragebogen offen ist (Format: YYYY-MM-TT ss:mm)',
	'SURVEY_SUM'						=> 'Summe',
	'SURVEY_SUM_BY'						=> 'Zu zählender Antwort-Text',
	'SURVEY_SUM_BY_DESC'				=> 'Zähle die Antworten gleich diesem Text, falls als Summen-Typ die entsprechende Option gewählt ist.',
	'SURVEY_SUM_TYPE'					=> 'Summen-Typ',
	'SURVEY_TYPE'						=> 'Typ',
	'SURVEY_USER_EXISTS'				=> 'Der Benutzer %s ist bereits Mitglied des Fragebogens.',
	'SURVEY_USERS_EXIST'				=> 'Alle Benutzer der gewählten Gruppen sind bereits Mitglied des Fragebogens.',
	'SURVEY_VIEWTOPIC_EXPLAIN'			=> 'Dieses Thema enthält bereits einen aktiven Fragebogen. Um den gesamten Fragebogen zu deaktivieren oder zu löschen, verwende den Reiter <em>Einstellungen</em> in der Themenansicht',
));
