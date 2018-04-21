<?php
/**
*
* survey [French]
*
* @package language
* @copyright (c) 2018 pvu, kilianr
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
	'SURVEY'							=> 'Questionnaire',
	'SURVEY_ADD'						=> 'Questionnaire',
	'SURVEY_ADD_DESC'					=> 'Ajouter un questionnaire à ce sujet',
	'SURVEY_ADD_REACTIVATE'				=> 'Réactiver le questionnaire',
	'SURVEY_ADD_REACTIVATE_EXPLAIN'		=> 'Les données d\'un questionnaire désactivé sont disponibles. Si vous le réactivez, ses questions et réponses seront à nouveau disponibles.',
	'SURVEY_ADD_USERS'					=> 'Inviter des utilisateurs',
	'SURVEY_ALLOW_CHANGE_ANSWER'		=> 'Autoriser les utilisateurs à modfifier leurs réponses',
	'SURVEY_ALLOW_MULTIPLE_ANSWER'		=> 'Autoriser les utilisateurs à répondre plusieurs fois',
	'SURVEY_AVERAGE'					=> 'Moyenne',
	'SURVEY_AVERAGE_DESC'				=> 'Affiche la moyenne : somme divisée par les réponses (dépend du type de somme en pourcentage ou non)',
	'SURVEY_CAP'						=> 'Maximum',
	'SURVEY_CAP_DESC'					=> 'En cas de somme, les utilisateurs ne peuvent pas donner de réponse qui ferait que la somme dépasse le maximum. Laisser vide pour ne pas avoir de maximum.',
	'SURVEY_CAP_EXEEDED'				=> 'Le maximum de la question %s a été dépassé (en comptant votre réponse). Aussi votre réponses n\'est-elle pas enregistrée.',
	'SURVEY_CAPTION'					=> 'Titre',
	'SURVEY_CHOICES'					=> 'Choix',
	'SURVEY_CHOICES_DESC'				=> 'Si un choix sur liste ou un choix multiple a été choisi, spécifier les choix ici, séparés par des virgules.',
	'SURVEY_CLOSE'						=> 'Fermer le questionnaire',
	'SURVEY_CLOSE_CONFIRM'				=> 'Êtes-vous sûr de vouloir fermer le questionnaire ?',
	'SURVEY_CLOSED'						=> 'Le questionnaire a été fermé.',
	'SURVEY_DATEFORMAT'					=> 'Y-m-d H:i',
	'SURVEY_DEFAULT_HIDE'				=> 'Cacher par défaut le questionnaire',
	'SURVEY_DELETE_ALL'					=> 'Supprimer toutes les données',
	'SURVEY_DELETE_ALL_CONFIRM'			=> 'Voulez-vous vraiment supprimer ce questionnaire ? Toutes les données seront définitivement perdues.',
	'SURVEY_DELETE_ALL_EXPLAIN'			=> 'Toutes les données liées à ce questionnaire vont être supprimées.',
	'SURVEY_DELETE_ANSWER'				=> 'Supprimer la réponse',
	'SURVEY_DELETE_EXPLAIN'				=> 'Ici vous pouvez désactiver ou supprimer le questionnaire',
	'SURVEY_DELETE_QUESTION'			=> 'Suppimer les questions',
	'SURVEY_DELETE_QUESTION_CONFIRM'	=> 'Voulez-vous vraiment supprimer la question %s ?',
	'SURVEY_DELETE_WHOLE'				=> 'Supprimer tout le questionnaire',
	'SURVEY_DESC'						=> 'Depuis le %s, ce sujet contient un questionnaire pouvant être utilisé pour consulter les utilisateurs.',
	'SURVEY_DESC_STOP'					=> 'Il est possible de répondre au questionnaire jusqu\'au %s.',
	'SURVEY_DISABLE'					=> 'Désactiver seulement',
	'SURVEY_DISABLE_CONFIRM'			=> 'Voulez-vous vraiment désactiver le questionnaire ? Les données (questions et réponses) seront conservées dans la base et le questionnaire pourra être réactivé à tout moment',
	'SURVEY_DISABLE_EXPLAIN'			=> 'Le questionnaire ne sera plus affiché dans le sujet mais données (questions et réponses) seront conservées dans la base.',
	'SURVEY_ENTRY_DELETION_CONFIRM'		=> 'Voulez-vous vraiment annuler votre participation à ce questionnaire ?',
	'SURVEY_EXAMPLE_ANSWER'				=> 'Exemple de réponse',
	'SURVEY_HIDE'						=> 'Cacher le questionnaire',
	'SURVEY_HIDDEN'						=> 'caché',
	'SURVEY_INVALID_ANSWER'				=> 'Réponse invalide.',
	'SURVEY_INVALID_CAPTION'			=> 'Titre invalide.',
	'SURVEY_INVALID_QUESTION_CHOICES'	=> 'Question invalide : pas de choix définis.',
	'SURVEY_INVALID_QUESTION_NO_LABEL'	=> 'Question invalide : pas de libellé défini.',
	'SURVEY_INVALID_QUESTION_SUM_BY'	=> 'Question invalide : le texte à compter n\'est pas défini.',
	'SURVEY_INVALID_QUESTION_SUM_TYPE'	=> 'Question invalide : type de somme inconnu.',
	'SURVEY_INVALID_QUESTION_TYPE'		=> 'Question invalide : type inconnu.',
	'SURVEY_INVALID_SHOW_ORDER_TYPE'	=> 'Type d\'ordre invalide.',
	'SURVEY_INVALID_STOPDATE'			=> 'Date de fin %s invalide. Entrer une date au format AAAA-MM-JJ hh:mm, comme 2042-07-01 13:37. La date ne peut être dans le passé.',
	'SURVEY_INVALID_TOPIC_POSTER_RIGHT_TYPE'	=> 'Droit invalide pour le créateur du sujet.',
	'SURVEY_INVALID_VISIBILITY_TYPE'	=> 'Type de visibilité invalide.',
	'SURVEY_IS_CLOSED'					=> 'Le questionnaire est fermé depuis le %s.',
	'SURVEY_IS_CLOSED_DESC_OWNER'		=> 'Le questionnaire est fermé depuis le %s mais, en tant qu\'auteur du sujet ou grâce à vos permissions, vous pouvez toujours l\'éditer.',
	'SURVEY_IS_DISABLED'				=> 'Le questionnaire est désactivé.',
	'SURVEY_IS_NOT_CLOSED'				=> 'Le questionnaire n\'est pas fermé.',
	'SURVEY_LABEL'						=> 'Libellé',
	'SURVEY_MANAGE_QUESTIONS'			=> 'Gérer les questions',
	'SURVEY_MANAGE_QUESTIONS_DESC'		=> 'Ici, vous pouvez ajouter des questions ou modifier les questions existantes.',
	'SURVEY_NO_ENTRIES'					=> 'Aucune réponse donnée pour le moment.',
	'SURVEY_NO_QUESTIONS'				=> 'Aucune question ajoutée pour le moment.',
	'SURVEY_OVERVIEW'					=> 'Résumé du questionnaire',
	'SURVEY_QUESTION_ADD_AS_NEW'		=> 'Ajouter en tant que nouvelle question',
	'SURVEY_QUESTION_ALREADY_ADDED'		=> 'La question %s a déjà été ajoutée à ce questionnaire',
	'SURVEY_QUESTION_LOAD'				=> 'Charger la question',
	'SURVEY_QUESTION_MODIFY'			=> 'Modifier la question',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['NO_SUM']				=> 'ne pas faire la somme',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['NUMBER_OF_RESPONSES']	=> 'compter les réponses',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['SUM_OF_NUMBERS']		=> 'faire le total des nombres des réponses',
	'SURVEY_QUESTION_SUM_TYPE_DESC_' . survey::$QUESTION_SUM_TYPES['MATCHING_TEXT']			=> 'compter les réponses qui contiennent un texte (à spécifier ci-dessous)',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['NORMAL_TEXT_BOX']	=> 'zone de texte normale',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['LARGE_TEXT_BOX']	=> 'zone de texte large',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['NUMBER']			=> 'nombre',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['CHECKBOX']			=> 'case à cocher',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DROP_DOWN_MENU']	=> 'menu à choix sur liste',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['MULTIPLE_CHOICE']	=> 'menu à choix multiple',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATE']				=> 'date',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['TIME']				=> 'heure',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATETIME']			=> 'date & heure avec timezone',
	'SURVEY_QUESTION_TYPE_DESC_' . survey::$QUESTION_TYPES['DATETIME_LOCAL']	=> 'date & heure sans timezone',
	'SURVEY_RANDOM_CHOICE_ORDER'		=> 'Ordre aléatoire des choix',
	'SURVEY_RANDOM_CHOICE_ORDER_DESC'	=> 'Si défini, les choix seront affichés aléatoirement pour chaque utilisateur.',
	'SURVEY_REOPEN'						=> 'Réouvrir le questionnaire',
	'SURVEY_REOPEN_CONFIRM'				=> 'Voulez-vous vraiment réouvrir le questionnaire ?',
	'SURVEY_REVERSE_ORDER'				=> 'Ordre inverse',
	'SURVEY_RUN'						=> 'Exécuter',
	'SURVEY_SETTINGS'					=> 'Éditer les paramètres',
	'SURVEY_SETTINGS_EXPLAIN'			=> 'Éditez ici les paramètres de base du questionnaire.',
	'SURVEY_SHOW'						=> 'Afficher le questionnaire',
	'SURVEY_SHOW_ORDER'					=> 'Type d\'ordre',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['ALPHABETICAL_USERNAME']		=> 'Par ordre de nom d\'utilisateur',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['RESPONSE_TIME']				=> 'Par ordre de réponse des utilisateurs',
	'SURVEY_SHOW_ORDER_DESC_' . survey::$SHOW_ORDER_TYPES['ALPHABETICAL_FIRST_ANSWER']	=> 'Par ordre du texte de la première réponse',
	'SURVEY_STOP_TIME'					=> 'Date de fin (incluse) du questionnaire (format : YYYY-MM-DD hh:mm)',
	'SURVEY_SUM'						=> 'Somme',
	'SURVEY_SUM_BY'						=> 'Texte à compter dans les réponses',
	'SURVEY_SUM_BY_DESC'				=> 'Compter les réponses qui contiennent ce texte, si le bon type de somme a été choisi.',
	'SURVEY_SUM_TYPE'					=> 'Type de somme',
	'SURVEY_TOPIC_POSTER_RIGHT_DESC'	=> 'Droits de l\'auteur du sujet',
	'SURVEY_TOPIC_POSTER_RIGHT_DESC_' . survey::$TOPIC_POSTER_RIGHT_TYPES['NONE']					=> 'Aucun droit spécial.',
	'SURVEY_TOPIC_POSTER_RIGHT_DESC_' . survey::$TOPIC_POSTER_RIGHT_TYPES['CAN_SEE_EVERYTHING']		=> 'Peut tout voir.',
	'SURVEY_TOPIC_POSTER_RIGHT_DESC_' . survey::$TOPIC_POSTER_RIGHT_TYPES['CAN_MANAGE']				=> 'Peut gérer les paramètres, les questions et tout voir.',
	'SURVEY_TOPIC_POSTER_RIGHT_DESC_' . survey::$TOPIC_POSTER_RIGHT_TYPES['CAN_EDIT_OTHER_USERS']	=> 'Peut gérer les paramètres, les questions et réponses (y compris des autres utilisateurs) et peut tout voir.',
	'SURVEY_TOTAL_ENTRIES'				=> 'Total : %s réponses',
	'SURVEY_TYPE'						=> 'Type',
	'SURVEY_USER_EXISTS'				=> 'L\'utilisateur %s a déjà participé à ce questionnaire.',
	'SURVEY_USERS_EXIST'				=> 'Les utilisateurs choisis ont déjà participé à ce questionnaire.',
	'SURVEY_VIEWTOPIC_EXPLAIN'			=> 'Ce sujet contient déjà un questionnaire actif. Pour le désactiver ou le supprimer, utiliser l\'onglet  <em>Paramètres</em> dans l\'affichage du sujet',
	'SURVEY_VISIBILITY_DESC'			=> 'Visibilité',
	'SURVEY_VISIBILITY_DESC_' . survey::$VISIBILITY_TYPES['SHOW_EVERYTHING']	=> 'Afficher tout',
	'SURVEY_VISIBILITY_DESC_' . survey::$VISIBILITY_TYPES['ANONYMIZE']			=> 'Anonymiser',
	'SURVEY_VISIBILITY_DESC_' . survey::$VISIBILITY_TYPES['HIDE_ENTRIES']		=> 'Cacher les réponses (les sommes seront affichées)',
	'SURVEY_VISIBILITY_DESC_' . survey::$VISIBILITY_TYPES['HIDE_EVERYTHING']	=> 'Cacher tout',
	'SURVEY_VISIBILITY_HIDE_DESC_OWNER'	=> 'Les réponses à ce questionnaire sont cachées mais, en tant qu\'auteur du sujet ou grâce à vos permissions, vous pouvez toujours les voir.',
	'SURVEY_VISIBILITY_HIDE_DESC_USER'	=> 'Comme les réponses à ce questionnaire sont cachées, vous ne pouvez voir que vos propres réponses.',
));
