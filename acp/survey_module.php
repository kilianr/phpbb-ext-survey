<?php

/**
 *
 * @package survey
 * @copyright (c) 2015 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\acp;

use kilianr\survey\functions\survey;

class survey_module
{
	public function main($id, $mode)
	{
		global $config, $user, $template, $request;

		$user->add_lang_ext('kilianr/survey', array('survey', 'info_acp_survey'));
		$this->tpl_name = 'acp_survey';
		$this->page_title = $user->lang('ACP_SURVEY');
		add_form_key('acp_survey');
		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('acp_survey'))
			{
				trigger_error('FORM_INVALID');
			}
			$new_settings = array(
				'show_order'			=> 0,
				'reverse_order'			=> 0,
				'allow_change_answer'	=> 0,
				'allow_multiple_answer'	=> 0,
				'visibility'			=> 0,
				'topic_poster_right'	=> 0,
			);
			foreach ($new_settings as $setting => $default)
			{
				$new_settings[$setting] = $request->variable("kilianr_survey_default_$setting", $default);
			}
			if (!in_array($new_settings['show_order'], survey::$SHOW_ORDER_TYPES))
			{
				trigger_error('SURVEY_INVALID_SHOW_ORDER_TYPE');
			}
			$new_settings['reverse_order'] = ($new_settings['reverse_order'] ? 1 : 0);
			$new_settings['allow_change_answer'] = ($new_settings['allow_change_answer'] ? 1 : 0);
			$new_settings['allow_multiple_answer'] = ($new_settings['allow_multiple_answer'] ? 1 : 0);
			if (!in_array($new_settings['visibility'], survey::$VISIBILITY_TYPES))
			{
				trigger_error('SURVEY_INVALID_VISIBILITY_TYPE');
			}
			if (!in_array($new_settings['topic_poster_right'], survey::$TOPIC_POSTER_RIGHTS))
			{
				trigger_error('SURVEY_INVALID_TOPIC_POSTER_RIGHT');
			}
			foreach ($new_settings as $setting => $value)
			{
				$config->set("kilianr_survey_default_$setting", $value);
			}
			trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		// Output show_order
		foreach (survey::$SHOW_ORDER_TYPES as $type)
		{
			$template_vars = array(
				'NUM'		=> $type,
				'SELECTED'	=> ($config['kilianr_survey_default_show_order'] == $type) ? true : false,
				'DESC'		=> $user->lang('SURVEY_SHOW_ORDER_DESC_' . $type),
			);
			$template->assign_block_vars('show_order', $template_vars);
		}

		// Output visibility types
		foreach (survey::$VISIBILITY_TYPES as $type)
		{
			$template_vars = array(
				'NUM'		=> $type,
				'SELECTED'	=> ($config['kilianr_survey_default_visibility'] == $type) ? true : false,
				'DESC'		=> $user->lang('SURVEY_VISIBILITY_DESC_' . $type),
			);
			$template->assign_block_vars('visibility', $template_vars);
		}

		// Output topic poster rights
		foreach (survey::$TOPIC_POSTER_RIGHTS as $right)
		{
			$template_vars = array(
				'NUM'		=> $right,
				'SELECTED'	=> ($config['kilianr_survey_default_topic_poster_right'] == $right) ? true : false,
				'DESC'		=> $user->lang('SURVEY_TOPIC_POSTER_RIGHT_DESC_' . $right),
			);
			$template->assign_block_vars('topic_poster_right', $template_vars);
		}

		$template->assign_vars(array(
			'U_ACTION'								=> $this->u_action,
			'SURVEY_DEFAULT_REVERSE_ORDER'			=> $config['kilianr_survey_default_reverse_order'],
			'SURVEY_DEFAULT_ALLOW_CHANGE_ANSWER'	=> $config['kilianr_survey_default_allow_change_answer'],
			'SURVEY_DEFAULT_ALLOW_MULTIPLE_ANSWER'	=> $config['kilianr_survey_default_allow_multiple_answer'],
		));
	}
}
