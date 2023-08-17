// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Toolbar and menu item of the tiny_htmlblock plugin for Moodle.
 *
 * @module      tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {addMenubarItem, addToolbarButton} from 'editor_tiny/utils';
import {component} from './common';

export const configure = (instanceConfig) => {
    // Update the instance configuration to add the htmlblock menu option to the menus and toolbars.
    return {
        toolbar: addToolbarButton(instanceConfig.toolbar, 'content', component),
        menu: addMenubarItem(instanceConfig.menu, 'insert', component),
    };
};