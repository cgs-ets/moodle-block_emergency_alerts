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
 * Custom Site Links block configuration form definition
 *
 * @package   block_emergency_alerts
 * @copyright Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');

define('DEFAULT_NUMBER_ALERTS', 1);

/**
 * Edit form class
 *
 * @package   block_emergency_alerts
 * @copyright 2019 Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_emergency_alerts_edit_form extends block_edit_form {

    /**
     * Form definition
     *
     * @param \moodleform $mform
     * @return void
     */
    protected function specific_definition($mform) {

        /***********************
        * ICON LINKS
        ************************/
        $mform->addElement('header', 'configheader', get_string('header', 'block_emergency_alerts'));

        $repeatarray = array();
        $type = 'hidden';
        $name = 'config_alertid';
        $value = get_string('arrayno', 'block_emergency_alerts');
        $repeatarray[] = &$mform->createElement($type, $name, $value);

        $type = 'hidden';
        $name = 'config_alertmessage';
        $value = '';
        $repeatarray[] = &$mform->createElement($type, $name, $value);

        $type = 'advcheckbox';
        $name = 'config_alertenabled';
        $label = get_string('enabled', 'block_emergency_alerts');
        $desc = get_string('enableddesc', 'block_emergency_alerts');
        $options = array();
        $values = array(0, 1);
        $repeatarray[] = &$mform->createElement($type, $name, $label, $desc, $options, $values);

        $type = 'editor';
        $name = 'config_alerteditor';
        $label = get_string('message', 'block_emergency_alerts');
        $editoroptions = array(
            'maxfiles' => 10,
            'maxbytes' => 5000000,
            'trusttext'=> true,
            'subdirs' => 0
        );
        $attributes = 'rows="4"';
        $repeatarray[] = &$mform->createElement($type, $name, $label, $attributes, $editoroptions);

        $type = 'select';
        $name = 'config_alerttype';
        $label = get_string('type', 'block_emergency_alerts');
        $options = array(
            "primary" => "Blue - Information", 
            "success" => "Green - Success", 
            "warning" => "Yellow - Warning", 
            "danger" => "Red - Danger",
            "secondary" => "Grey", 
        );
        $repeatarray[] = &$mform->createElement($type, $name, $label, $options);

        $type = 'advcheckbox';
        $name = 'config_alertdelete';
        $label = get_string('delete');
        $desc = get_string('deletedesc', 'block_emergency_alerts');
        $options = array();
        $values = array(0, 1);
        $repeatarray[] = &$mform->createElement($type, $name, $label, $desc, $options, $values);

        $type = 'html';
        $value = '<br/><hr><br/>';
        $repeatarray[] = &$mform->createElement($type, $value); // Spacer.

        $repeatcount = DEFAULT_NUMBER_ALERTS;
        if ( isset($this->block->config->alertid) ) {
            $count = count($this->block->config->alertid);
            if ( $count > 0 ) {
                $repeatcount = $count;
            }
        }

        $repeatoptions = array();

        $repeatoptions['config_alertid']['type']       = PARAM_INT;
        $repeatoptions['config_alertenabled']['type']  = PARAM_INT;
        $repeatoptions['config_alertmessage']['type']  = PARAM_RAW;
        $repeatoptions['config_alerteditor']['type']   = PARAM_RAW;
        $repeatoptions['config_alerttype']['type']     = PARAM_TEXT;
        $repeatoptions['config_alertdelete']['type']   = PARAM_INT;

        $repeatoptions['config_alerteditor']['rule']  = array(get_string('required'), 'required', null, 'server');

        $repeatoptions['config_alertenabled']['disabledif'] = array('config_alertdelete', 'checked');
        $repeatoptions['config_alerteditor']['disabledif'] = array('config_alertdelete', 'checked');
        $repeatoptions['config_alerttype']['disabledif'] = array('config_alertdelete', 'checked');

        $this->repeat_elements($repeatarray, $repeatcount, $repeatoptions, 'alert_repeats', 'alert_add_fields',
            1, get_string('addnewalert', 'block_emergency_alerts'), true);

    }

    /**
     * Return submitted data.
     *
     * @return object submitted data.
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data) {
            // Remove deleted alerts before saving data.
            if ( !empty($data->config_alertdelete) ) {
                foreach ($data->config_alertdelete as $i => $del) {
                    if ($del) {
                        $this->delete_array_element($data->config_alertid, $i);
                        $this->delete_array_element($data->config_alertenabled, $i);
                        $this->delete_array_element($data->config_alerteditor, $i);
                        $this->delete_array_element($data->config_alertmessage, $i);
                        $this->delete_array_element($data->config_alerttype, $i);
                    }
                }
                // Dont need delete array anymore.
                $data->config_alertdelete = array();

                // Reindex arrays.
                $data->config_alertid = array_values($data->config_alertid);
                $data->config_alertenabled = array_values($data->config_alertenabled);
                $data->config_alerteditor = array_values($data->config_alerteditor);
                $data->config_alerttype = array_values($data->config_alerttype);
            }

            // Save message files to a permanent file area.
            if ( !empty($data->config_alerteditor) ) {
                foreach ($data->config_alerteditor as $i => $editor) {
                    $data->config_alertmessage[$i] = file_save_draft_area_files(
                        $editor['itemid'], 
                        $this->block->context->id, 
                        'block_emergency_alerts', 
                        'messages', 
                        $i,
                        array('maxfiles' => 10, 'maxbytes' => 5000000, 'trusttext'=> true, 'subdirs' => 0),
                        $editor['text']
                    );
                    $data->config_alerteditor[$i]['text'] = $data->config_alertmessage[$i];
                }
            }
        }

        return $data;
    }

    /**
     * Set form data.
     *
     * @param array $defaults
     * @return void
     */
    public function set_data($defaults) {
        global $USER;

        if (isset($this->block->config->alerteditor)) {
            foreach ($this->block->config->alerteditor as $i => $editor) {
                $itemid = ''; // Empty string force creates a new area and copy existing files into.

                // Fetch the draft file areas. On initial load this is empty and new draft areas are created.
                // On subsequent loads the draft areas are retreived.
                if (isset($_REQUEST['config_alerteditor'][$i])) {
                    $itemid = $_REQUEST['config_alerteditor'][$i]['itemid'];
                }

                // Copy all the files from the 'real' area, into the draft areas.
                $message = file_prepare_draft_area($itemid, $this->block->context->id, 'block_emergency_alerts',
                    'messages', $i, array('maxfiles' => 10, 'maxbytes' => 5000000, 'trusttext'=> true, 'subdirs' => 0), $editor['text']);

                $this->block->config->alerteditor[$i]['itemid'] = $itemid;
                $this->block->config->alerteditor[$i]['text'] = $message;

            }
        }

        // Set form data.
        parent::set_data($defaults);
    }

    /**
     * Remove fields not required if delete link is selected.
     *
     * @return void
     */
    public function definition_after_data() {
        if (!isset($this->_form->_submitValues['config_alertdelete'])) {
            return;
        }
        foreach ($this->_form->_submitValues['config_alertdelete'] as $i => $del) {
            // Remove the rules for the deleted link so that error is not triggered.
            if ($del) {
                unset($this->_form->_rules["config_alertenabled[${i}]"]);
                unset($this->_form->_rules["config_alertmessage[${i}]"]);
                unset($this->_form->_rules["config_alerteditor[${i}]"]);
                unset($this->_form->_rules["config_alerttype[${i}]"]);
            }
        }
    }

    /**
     * Helper to delete array element
     *
     * @param array $array
     * @param mixed $index
     * @return void
     */
    private function delete_array_element(&$array, $index) {
        // Unset element and shuffle everything down.
        if (isset($array[$index])) {
            unset($array[$index]);
        }
        if (empty($array)) {
            $array = array();
        }
    }

}