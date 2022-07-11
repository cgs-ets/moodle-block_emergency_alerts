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

        self.drawBanner();

        var body = $('body');
        
        //Subscribe to nav drawer event
        PubSub.subscribe('nav-drawer-toggle-end', function(el){
            if (body.hasClass('has-emergency-alert')) {
                window.scrollTo(0, 0);
            }
        });

        // Watch resize to adjust width
        $(window).on('resize', function(){
            self.drawBanner();
        });
    };

    EmergencyalertControl.prototype.drawBanner = function () {
        var self = this;

        var body = $('body');
        var block = self.region.closest('.block.block_emergency_alerts');

        if ( ! body.hasClass('editing') && self.region.data('numalerts') ) {
            block.addClass('active');
            body.addClass('has-emergency-alert');
            block.detach();
            body.prepend(block);

            var height = block.height();

            var navtop = $('.navbar'); 
            var css = {
                'margin-top' : height,
                'position' : 'absolute',
            };
            navtop.css(css);

            var navleft = $('#nav-drawer');
            var css = {
                'margin-top' : height,
                'position' : 'absolute',
                'height' : 'auto',
                'overflow' : 'hidden',
            };
            navleft.css(css);
            // Add scroll back into nav.
            navleft.css('height', 'calc(100% - ' + (height + 70) + 'px)');
            navleft.css('overflow-y', 'auto');

            var page = $('#page');
            page.css('margin-top', height+navtop.height());
            body.css('overflow-y', 'auto');
        }
    };

    return {
        init: init
    };
});