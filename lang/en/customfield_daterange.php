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
 * Customfields date plugin
 *
 * @package   customfield_daterange
 * @copyright 2026 Université Rennes 2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['default_field_settings'] = 'Réglages champ « Plage de dates »';
$string['end_date'] = 'Date de fin';
$string['errormaxdate'] = 'Veuillez saisir une date antérieure à {$a}.';
$string['errormindate'] = 'Veuillez saisir une date postérieure ou égale à {$a}.';
$string['maxdate'] = 'Valeur maximale';
$string['mindate'] = 'Valeur minimale';
$string['mindateaftermax'] = 'La valeur minimale ne peut pas être plus grande que la valeur maximale.';
$string['multiple_value_format'] = '{$a->start} au {$a->end}';
$string['pluginname'] = 'Plage de dates';
$string['privacy:metadata'] = 'Le plugin champ « Plage de dates » n’enregistre aucune donnée personnelle.';
$string['single_value_format'] = '%a %d %b';
$string['start_date'] = 'Date de début';
$string['the_start_date_must_be_prior_to_the_end_date'] = 'La date de début doit être antérieure à la date de fin.';
