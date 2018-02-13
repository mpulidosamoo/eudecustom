<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Add page to admin menu.
 *
 * @package    local_comillasppi
 * @copyright  2018 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
global $CFG;
if ($hassiteconfig) {
    $settings = new admin_settingpage('local_comillasppi', get_string('pluginname', 'local_comillasppi'));

    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading('local_comillasppi_title',
            new lang_string('comillasppi_warning', 'local_comillasppi'), ''));

    $settings->add(new admin_setting_configtextarea('local_comillasppi_text',
                new lang_string('comillasppi_warning', 'local_comillasppi'),
                new lang_string('comillasppi_warning_desc', 'local_comillasppi'),
                ''));

    $settings->add(new admin_setting_configtext('local_comillasppi_url',
                new lang_string('comillasppi_url', 'local_comillasppi'),
                new lang_string('comillasppi_url_desc', 'local_comillasppi'),
                '', PARAM_TEXT, null));
}