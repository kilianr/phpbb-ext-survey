<?php

/**
 *
 * @package survey
 * @copyright (c) 2015 kilianr
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License v3
 *
 */

namespace kilianr\survey\migrations;

use kilianr\survey\functions\survey;

class convert_from_phpBB30 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kilianr\survey\migrations\version_1_0');
	}

	public function update_data()
	{
		return array(array('custom', array(array(&$this, 'convert_old_survey_data'))));
	}

	public function convert_old_survey_data()
	{
		global $auth, $user;
		if (!isset($this->config['survey_version']))
		{
			return;
		}
		if (!function_exists('user_get_id_name'))
		{
			include("{$this->phpbb_root_path}includes/functions_user.{$this->php_ext}");
		}
		$sql = 'SELECT topic_id FROM ' . TOPICS_TABLE . ' WHERE topic_survey = 1';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_id = $row['topic_id'];
			$sql = "SELECT * FROM {$this->table_prefix}survey WHERE topic_id = '$topic_id'";
			$result2 = $this->db->sql_query($sql);
			$old_settings = $this->db->sql_fetchrow($result2);
			$this->db->sql_freeresult($result2);
			if ($old_settings)
			{
				$survey = new survey($this->db, $this->config, $user, $auth, $this->table_prefix . 'surveys', $this->table_prefix . 'surveys_questions', $this->table_prefix . 'surveys_q_choices', $this->table_prefix . 'surveys_entries', $this->table_prefix . 'surveys_answers');
				$survey->enable($topic_id);
				$survey->initialize($topic_id);
				$survey->load_survey($topic_id);

				// Convert the settings
				$settings = array(
					'caption'				=> $old_settings['survey_caption'] ? $old_settings['survey_caption'] : 'Survey',
					'show_order'			=> min(max((int) $old_settings['show_order'], 0), 2),
					'reverse_order'			=> $old_settings['show_order'] == 3 ? 1 : 0,
					'allow_change_answer'	=> $old_settings['allow_change_answers'] == 1 ? 1 : 0,
					'allow_multiple_answer'	=> $old_settings['allow_change_answers'] == 2 ? 1 : 0,
					'visibility'			=> $old_settings['hide_survey_results'] ? 3 : ($old_settings['hide_names_of_respondents'] ? 1 : 0),
					'start_time'			=> $old_settings['survey_start'],
					'stop_time'				=> $old_settings['survey_length'] == 0 ? null : ($old_settings['survey_start'] + $old_settings['survey_length']),
				);
				$survey->change_config($settings);

				// Convert the questions
				$questions_skip = array();
				$questions_label = array_map('trim', explode('|', $old_settings['questions']));
				$questions_type = explode('|', $old_settings['question_types']);
				$questions_choices = explode('|', htmlspecialchars_decode($old_settings['question_selections']));
				$questions_sum_type = explode('|', $old_settings['question_sums']);
				$questions_sum_by = array_map('trim', explode('|', $old_settings['question_selected_text']));
				$questions_cap = explode('|', $old_settings['question_response_caps']);
				$num_questions = min(sizeof($questions_label), sizeof($questions_type), sizeof($questions_choices), sizeof($questions_sum_type), sizeof($questions_sum_by), sizeof($questions_cap));
				for ($i = 0; $i < $num_questions; $i++)
				{
					$questions_skip[$i] = false;
					if ($questions_label[$i] == '' || $survey->get_question_id_from_label($questions_label[$i], -1) != -1)
					{
						$questions_skip[$i] = true;
						continue;
					}
					$new_type = min(max((int) $questions_type[$i], 0), 5);
					$new_type = ($new_type == 2 ? 0 : $new_type);
					$new_type = ($new_type == 3 ? 4 : $new_type);
					$question = array(
						'label'					=> $questions_label[$i],
						'example_answer'		=> '',
						'type'					=> $new_type,
						'random_choice_order'	=> 0,
						'sum_type'				=> min(max(((int) $questions_sum_type[$i] == 4 ? 2 : (int) $questions_sum_type[$i]), 0), 3),
						'sum_by'				=> $questions_sum_type[$i] == 3 ? $questions_sum_by[$i] : '',
						'average'				=> $questions_sum_type[$i] == 4 ? 1 : 0,
						'cap'					=> (int) $questions_cap[$i],
					);
					$choices = array();
					if ($new_type == 4 || $new_type == 5)
					{
						if ($questions_choices[$i] == '')
						{
							$questions_skip[$i] = true;
							continue;
						}
						foreach (array_map('trim', array_unique(explode(";", $questions_choices[$i]))) as $choice)
						{
							if ($choice == '')
							{
								continue;
							}
							$choices[] = htmlspecialchars(str_replace(',', '', $choice));
						}
					}
					$questions_type[$i] = $new_type;
					$survey->add_question($question, $choices);
					$questions_id[$i] = $survey->get_question_id_from_label($questions_label[$i], -1);
				}

				// Convert the answers
				$sql = "SELECT user_id, answers FROM {$this->table_prefix}survey_answers WHERE survey_id = {$old_settings['survey_id']} ORDER BY response_order";
				$result2 = $this->db->sql_query($sql);
				while ($row2 = $this->db->sql_fetchrow($result2))
				{
					$username = array();
					if (user_get_id_name($row2['user_id'], $username) == 'NO_USERS')
					{
						continue;
					}
					$raw_answers = explode('|', $row2['answers']);
					$answers = array();
					$i = -1;
					foreach ($raw_answers as $answer)
					{
						++$i;
						if ($i >= $num_questions || $questions_skip[$i])
						{
							continue;
						}
						if ($questions_type[$i] == 5)
						{
							$answer = implode(',', array_map('trim', explode('&&', str_replace(',', '', $answer))));
						}
						if ($questions_type[$i] == 4)
						{
							$answer = str_replace(',', '', trim($answer));
						}
						if (!$survey->check_answer($answer, $questions_id[$i]))
						{
							continue;
						}
						$answers[$questions_id[$i]] = $answer;
					}
					$survey->add_entry($row2['user_id'], $answers);
				}
				$this->db->sql_freeresult($result2);
				unset($survey);
			}
		}
		$this->db->sql_freeresult($result);
	}
}