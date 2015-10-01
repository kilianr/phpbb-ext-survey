# phpBB 3.1 Extension - Survey
This Extension adds the possibility of performing surveys with multiple people to your topics.

This Extension is intended to be fully backwards compatible to the existing phpBB 3.0.x Mod "tabulated survey at topic head" from the asinshesq (see https://www.phpbb.com/customise/db/mod/tabulated_survey_at_topic_head_2/) for upgrades, but may include additional features.

Current features:
* Full survey functionality
    * Topic poster can create a survey and add questions
    * Users can answer the questions
* Configure survey
    * Configure ordering of answers (by username, answer-date oder text in first answer)
    * Allow users to change their answers
    * Allow multiple answers
    * Configure visibilty of information
    * Close/Reopen survey anytime
    * Configure automatic closing date
* Questions
    * Different question types (text fields, number, drop-down menus, multiple-choice menus, dates, time)
    * Count / Sum up answers based on different criteria (count answers matching a text, sum up numbers in answers)
    * For drop-down and multiple-choice menus, all options are counted
    * Average the sums
    * Configure a cap that will prevent users to answer, when it is reached
    * Option Order choices randomly
* Topic-Poster / Moderator can:
    * Configure the survey
    * Add, edit and remove answers of other users
    * Add, edit and remove questions
    * Disable or delete the whole survey
* Forum-Admin can configure default survey settings in ACP
* Languages
    * English
    * German

Planned features:

* See [TODO](TODO)

## Installation

Clone into ext/kilianr/survey:

    git clone https://github.com/kilianr/phpbb-ext-survey ext/kilianr/survey

Go to "ACP" > "Customise" > "Extensions" and enable the "Survey" extension.

## Configuration

Two new forum permissions (content category) will be introduced:
* f_survey_create: Can create surveys upon topic creation or when editing the first post. This permission is initially copied from f_poll.
* f_survey_answer: Can answer surveys. This permission is initially copied from f_reply.

One new forum Moderator permission (topic category) will be introduced:
* m_survey: Can manage surveys: Edit settings, manage questions and edit all answers, also of other users. This permission is initially copied from m_edit.

## Development

If you find a bug, please report it on https://github.com/kilianr/phpbb-ext-survey

## Automated Testing

We use automated unit tests including functional tests to prevent regressions. Check out our travis build below:

master: [![Build Status](https://travis-ci.org/kilianr/phpbb-ext-survey.png?branch=master)](http://travis-ci.org/kilianr/phpbb-ext-survey)

## License

[AGPLv3](license.txt)
