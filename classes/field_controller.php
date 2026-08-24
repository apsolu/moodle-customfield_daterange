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

namespace customfield_daterange;

/**
 * Class field
 *
 * @package   customfield_daterange
 * @copyright 2026 Université Rennes 2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_controller extends \core_customfield\field_controller {
    /**
     * Add fields for editing a date field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $config = $this->get('configdata');

        // Add elements.
        $mform->addElement('header', 'header_specificsettings', get_string('default_field_settings', 'customfield_daterange'));
        $mform->setExpanded('header_specificsettings', true);

        $label = get_string('mindate', 'customfield_daterange');
        $mform->addElement('date_selector', 'configdata[mindate]', $label, ['optional' => true]);

        $label = get_string('maxdate', 'customfield_daterange');
        $mform->addElement('date_selector', 'configdata[maxdate]', $label, ['optional' => true]);
    }

    /**
     * Validate the data from the config form.
     *
     * @param array $data
     * @param array $files
     * @return array associative array of error messages
     */
    public function config_form_validation(array $data, $files = []): array {
        $errors = [];

        // Make sure the start year is not greater than the end year.
        if (
            empty($data['configdata']['mindate']) === false &&
            empty($data['configdata']['maxdate']) === false &&
            $data['configdata']['mindate'] > $data['configdata']['maxdate']
        ) {
            $errors['configdata[mindate]'] = get_string('mindateaftermax', 'customfield_daterange');
        }

        return $errors;
    }

    /**
     * Convert given value into appropriate timestamp
     *
     * @param string $value
     * @return int
     */
    /*
    public function parse_value(string $value) {
        $timestamp = strtotime($value);

        // If we have a valid, positive timestamp then return it.
        return $timestamp > 0 ? $timestamp : 0;
    }
*/
}
