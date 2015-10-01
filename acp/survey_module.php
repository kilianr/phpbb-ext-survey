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
				'default_hide'			=> 0,
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
			$new_settings['default_hide'] = ($new_settings['default_hide'] ? 1 : 0);
			if (!in_array($new_settings['visibility'], survey::$VISIBILITY_TYPES))
			{
				trigger_error('SURVEY_INVALID_VISIBILITY_TYPE');
			}
			if (!in_array($new_settings['topic_poster_right'], survey::$TOPIC_POSTER_RIGHT_TYPES))
			{
				trigger_error('SURVEY_INVALID_TOPIC_POSTER_RIGHT_TYPE');
			}
			foreach ($new_settings as $setting => $value)
			{
				$config->set("kilianr_survey_default_$setting", $value);
			}
			trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		foreach (array('show_order', 'visibility', 'topic_poster_right') as $block_setting)
		{
			survey::assign_block_vars_for_selection($block_setting, $template, $user, $config, 'kilianr_survey_default_');
		}

		$template->assign_vars(array(
			'U_ACTION'								=> $this->u_action,
			'SURVEY_DEFAULT_REVERSE_ORDER'			=> $config['kilianr_survey_default_reverse_order'],
			'SURVEY_DEFAULT_ALLOW_CHANGE_ANSWER'	=> $config['kilianr_survey_default_allow_change_answer'],
			'SURVEY_DEFAULT_ALLOW_MULTIPLE_ANSWER'	=> $config['kilianr_survey_default_allow_multiple_answer'],
			'SURVEY_DEFAULT_DEFAULT_HIDE'			=> $config['kilianr_survey_default_default_hide'],
		));
	}
}
