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
 * Form for editing HTML block instances.
 *
 * @package   multi_cohorts
 * @copyright K.V. Moret <k.moret@agriholland.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

 class block_multi_cohorts extends block_base {

     public function init() {
         $this->title = get_string('pluginname', 'block_multi_cohorts');
     }

     function get_content() {
       global $USER, $DB, $COURSE, $OUTPUT;

       if (!isloggedin() or isguestuser()) {
         return '';      // Prevent display on front page/course index/course/pages/ when not logged in
       }

       $this->content =  new stdClass;

       $this->title = get_string('pluginname', 'block_multi_cohorts');

       //var_dump( (array) $my_user); // SHOULD RETURN EVERYTHING

       //$this->content->text .= "<div class=\"classname\"> <div> $my_user->firstname $my_user->lastname</div>";
       //$this->content->text .= '</div>';

       global $COURSE;

       $url = new moodle_url('/blocks/multi_cohorts/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
       $this->content->footer = html_writer::link($url, get_string('addpage', 'block_multi_cohorts'));

       return $this->content;
    }



}
