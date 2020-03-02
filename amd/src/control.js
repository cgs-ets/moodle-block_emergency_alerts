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
 * Provides the block_emergency_alerts/control module
 *
 * @package   block_emergency_alerts
 * @category    output
 * @copyright 2019 Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module block_emergency_alerts/control
 */
define(['jquery', 'core/log', 'core/pubsub'], function($, Log, PubSub) {
    'use strict';

    /**
     * Initializes the block controls.
     */
    function init(instanceid) {
        Log.debug('block_emergency_alerts/control: initializing instance ' + instanceid);

        var region = $('[data-region="block_emergency_alerts-instance-' + instanceid +'"]').first();

        if (!region.length) {
            Log.debug('block_emergency_alerts/control: wrapping region not found!');
            return;
        }

        var control = new EmergencyalertControl(region);
        control.main();
    }

    /**
     * Controls a single my_day_timetable block instance contents.
     *
     * @constructor
     * @param {jQuery} region
     */
    function EmergencyalertControl(region) {
        var self = this;
        self.region = region;
    }

    /**
     * Run the controller.
     *
     */
   EmergencyalertControl.prototype.main = function () {
        var self = this;

        var body = $('body');
        var block = self.region.closest('.block.block_emergency_alerts');

        if ( ! body.hasClass('editing') && self.region.data('numalerts') ) {
            block.addClass('active');
            body.addClass('has-emergency-alert');
            block.detach();
            body.prepend(block);

            var page = $('#page');
            var navleft = $('#nav-drawer');
            var navtop = $('.navbar'); 

            var height = block.height();
            page.css('margin-top', height+navtop.height());
            var css = {
                'margin-top' : height,
                'position' : 'absolute',
            };
            navleft.css(css);
            navtop.css(css);
        }

        //Subscribe to nav drawer event
        PubSub.subscribe('nav-drawer-toggle-end', function(el){
            if (body.hasClass('has-emergency-alert')) {
                Log.debug('scrolling to top');
                window.scrollTo(0, 0);
            }
        });
    };

    return {
        init: init
    };
});