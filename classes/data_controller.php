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

use core_customfield\api;

/**
 * Base class for custom fields data controllers
 *
 * This class is a wrapper around the persistent data class that allows to define
 * how the element behaves in the instance edit forms.
 *
 * Contrôle le formulaire d'édition des données d'une instance de champ personnalisé.
 * Enregistre un JSON au format {'start':0,'end':86400}.
 * À l'affichage, la valeur est formatée en : Jeudi 1er janvier au vendredi 2 janvier.
 *
 * @package   customfield_daterange
 * @copyright 2026 Université Rennes 2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_controller extends \core_customfield\data_controller {
    /**
     * Return the name of the field in the db table {customfield_data} where the data is stored
     *
     * Must be one of the following:
     *   intvalue - can store integer values, this field is indexed
     *   decvalue - can store decimal values
     *   shortcharvalue - can store character values up to 255 characters long, this field is indexed
     *   charvalue - can store character values up to 1333 characters long, this field is not indexed but
     *     full text search is faster than on field 'value'
     *   value - can store character values of unlimited length ("text" field in the db)
     *
     * @return string
     */
    public function datafield(): string {
        return 'shortcharvalue';
    }

    /**
     * Add fields for editing data of a date field on a context.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        $field = $this->get_field();
        $name = $this->get_form_element_name();

        // Get the current calendar in use - see MDL-18375.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();

        $config = $field->get('configdata');

        // Always set the form element to "optional", even when it's required. Otherwise it defaults to the
        // current date and is easy to miss.
        $attributes = ['optional' => false];

        if (!empty($config['mindate'])) {
            $attributes['startyear'] = $calendartype->timestamp_to_date_array($config['mindate'])['year'];
        }

        if (!empty($config['maxdate'])) {
            $attributes['stopyear'] = $calendartype->timestamp_to_date_array($config['maxdate'])['year'];
        }

        foreach (['start', 'end'] as $lapse) {
            $elementname = sprintf('%s[%s]', $name, $lapse);
            $elementlabel = get_string(sprintf('%s_date', $lapse), 'customfield_daterange');

            $mform->addElement('date_selector', $elementname, $elementlabel, $attributes);
            $mform->setType($elementname, PARAM_INT);
            // $mform->setDefault($elementname, time());
            if ($field->get_configdata_property('required')) {
                $mform->addRule($elementname, null, 'required', null, 'client');
            }
        }
    }

    /**
     * Validates data for this field.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function instance_form_validation(array $data, array $files): array {
        $errors = parent::instance_form_validation($data, $files);

        $elementname = $this->get_form_element_name();

        $mindate = $this->get_field()->get_configdata_property('mindate');
        $maxdate = $this->get_field()->get_configdata_property('maxdate');

        if (empty($mindate) === false || empty($maxdate) === false) {
            foreach (['start', 'end'] as $lapse) {
                $errorkey = sprintf('%s[%s]', $name, $lapse);

                // Compare the date with min/max values.
                $machineformat = '%Y-%m-%d';
                $humanformat = get_string('strftimedatefullshort');
                $value = userdate($data[$elementname][$lapse], $machineformat, 99, false, false);

                if ($mindate && userdate($mindate, $machineformat, 99, false, false) > $value) {
                    $errors[$errorkey] = get_string('errormindate', 'customfield_daterange', userdate($mindate, $humanformat));
                }

                if ($maxdate && userdate($maxdate, $machineformat, 99, false, false) < $value) {
                    $errors[$errorkey] = get_string('errormaxdate', 'customfield_daterange', userdate($maxdate, $humanformat));
                }
            }
        }

        if ($data[$elementname]['start'] > $data[$elementname]['end']) {
            $errorkey = sprintf('%s[start]', $elementname);
            $errors[$errorkey] = get_string('the_start_date_must_be_prior_to_the_end_date', 'customfield_daterange');
        }

        return $errors;
    }

    /**
     * Saves the data coming from form
     *
     * @param \stdClass $datanew data coming from the form
     */
    public function instance_form_save(\stdClass $datanew) {
        $elementname = $this->get_form_element_name();
        if (property_exists($datanew, $elementname) === false) {
            return;
        }

        $data = [];
        foreach (['start', 'end'] as $lapse) {
            $data[$lapse] = intval($datanew->{$elementname}[$lapse]);
        }

        $json = json_encode($data);
        $intvalue = $data['start'];

        $this->data->set('intvalue', $intvalue);
        $this->data->set($this->datafield(), $json);
        $this->data->set('value', $json);
        $this->save();
    }

    /**
     * Prepares the custom field data related to the object to pass to mform->set_data() and adds them to it
     *
     * This function must be called before calling $form->set_data($object);
     *
     * @param \stdClass $instance the entity that has custom fields, if 'id' attribute is present the custom
     *    fields for this entity will be added, otherwise the default values will be added.
     */
    public function instance_form_before_set_data(\stdClass $instance) {
        $value = $this->get($this->datafield());
        if ($value === null) {
            $value = $this->get_default_value();
        }

        $data = json_decode($value);
        if (isset($data->main, $data->additional) === false) {
            $data = json_decode($this->get_default_value());
        }

        $instance->{$this->get_form_element_name()} = (array) $data;
    }

    /**
     * Returns the default value as it would be stored in the database (not in human-readable format).
     *
     * @return mixed
     */
    public function get_default_value() {
        return json_encode($this->get_field()->get_configdata_property('defaultvalue'));
    }

    /**
     * Returns the value as it is stored in the database or default value if data record is not present
     *
     * @return mixed
     */
    public function get_value() {
        $value = $this->get($this->datafield());
        if ($value === null) {
            return $this->get_default_value();
        }

        $data = json_decode($value);
        if (isset($data->start, $data->end) === false) {
            return $this->get_default_value();
        }

        return json_encode(['start' => $data->start, 'end' => $data->end]);
    }

    /**
     * Returns value in a human-readable format
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        $value = json_decode($this->get_value(), $associative = true);

        $format = get_string('single_value_format', 'customfield_daterange');
        if ($value['start'] === $value['end']) {
            return userdate($value['start'], $format);
        }

        return get_string('multiple_value_format', 'customfield_daterange', [
            'start' => userdate($value['start'], $format),
            'end' => userdate($value['end'], $format),
        ]);
    }
}
