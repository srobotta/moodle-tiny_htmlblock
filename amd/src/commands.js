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
 * Commands helper for the Moodle tiny_fontcolor plugin.
 *
 * @module      tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getButtonImage} from 'editor_tiny/utils';
import {component} from './common';
import {get_string as getString} from 'core/str';
import {handleAction} from './ui';
import {getHtmlBlocks} from "./options";

/**
 * Get the setup function for the buttons.
 *
 * This is performed in an async function which ultimately returns the registration function as the
 * Tiny.AddOnManager.Add() function does not support async functions.
 *
 * @returns {function} The registration function to call within the Plugin.add function.
 */
export const getSetup = async() => {
    const [
        buttonText,
        buttonImage,
        menuText,
    ] = await Promise.all([
        getString('buttontext', component),
        getButtonImage('icon', component),
        getString('menutext', component)
    ]);

    return (editor) => {
        // Check if we have any html blocks defined
        if (getHtmlBlocks(editor).length === 0) {
            return;
        }

        // Register the Icon.
        editor.ui.registry.addIcon(component, buttonImage.html);

        // Register the Menu Button.
        editor.ui.registry.addButton(component, {
            icon: component,
            tooltip: buttonText,
            onAction: () => handleAction(editor),
        });

        // Add the menu item.
        // This allows it to be added to a standard menu, or a context menu.
        editor.ui.registry.addMenuItem(component, {
            icon: component,
            text: menuText,
            onAction: () => handleAction(editor),
        });
    };
};
