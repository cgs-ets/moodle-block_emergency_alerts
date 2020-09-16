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
 * Responsive icon and text links list with visibilty based on user profile fields.
 *
 * @package   block_emergency_alerts
 * @copyright Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block class definition.
 *
 * @package    block_emergency_alerts
 * @copyright  Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_emergency_alerts extends block_base {

    /**
     * Core function used to initialize the block.
     */
    public function init() {
        $this->title = get_string('title', 'block_emergency_alerts');
    }

    /**
     * Core function used to identify if the block has a config page.
     */
    public function has_config() {
        return false;
    }

    /**
     * Controls whether multiple instances of the block are allowed on a page
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Controls whether the block is configurable
     *
     * @return bool
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'site'           => true,
            'course-view'    => false,
            'mod'            => false,
            'my'             => false,
        );
    }

    /**
     * Used to generate the content for the block.
     * @return object
     */
    public function get_content() {
        global $OUTPUT;

        // If content has already been generated, don't waste time generating it again.
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $data = [
            'instanceid' => $this->instance->id,
            'alerts' => array(),
        ];

        if (isset($this->config->alerteditor)) {
            $data['numalerts'] = count($this->config->alertmessage);
            foreach ($this->config->alertmessage as $i => $message) {
                if ($message == '') {
                    continue;
                }

                if (!$this->config->alertenabled[$i]) {
                    continue;
                }
                
                $type = isset($this->config->alerttype[$i]) ? $this->config->alerttype[$i] : '';
                $message = file_rewrite_pluginfile_urls($message, 'pluginfile.php', $this->context->id, 'block_emergency_alerts', 'messages', $i);

                $data['alerts'][] = [
                    'type' => $type,
                    'message' => $message,
                ];
            }
        }

        // Render links if any.
        if (!empty($data['alerts'])) {
            $this->content->text = $OUTPUT->render_from_template('block_emergency_alerts/content', $data);
        }

        return $this->content;
    }

    /**
     * Gets Javascript required for the widget functionality.
     */
    public function get_required_javascript() {
        parent::get_required_javascript();
        $this->page->requires->js_call_amd('block_emergency_alerts/control', 'init', [
            'instanceid' => $this->instance->id
        ]);
    }

}