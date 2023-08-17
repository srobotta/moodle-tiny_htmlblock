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
 * Options helper for Tiny Font Color plugin.
 *
 * @module      tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getPluginOptionName} from 'editor_tiny/options';
import {pluginName} from './common';

const htmlblocks = getPluginOptionName(pluginName, 'htmlblocks');
const valid_children = getPluginOptionName(pluginName, 'valid_children');

/**
 * Register the options for the tiny_htmlblock plugin.
 *
 * @param {TinyMCE.Editor} editor
 */
export const register = (editor) => {

  editor.options.register(htmlblocks, {
    processor: 'Array',
    "default": [],
  });

  editor.options.register(valid_children, {
    processor: 'String',
    "default": '',
  });

};

/**
 * Get the defined html blocks for this instance.
 *
 * @param {TinyMCE.Editor} editor
 * @returns {array}
 */
export const getHtmlBlocks = (editor) => editor.options.get(htmlblocks);

/**
 * Get the valid_children setting from the plugin.
 * @param {TinyMCE.Editor} editor
 * @return {String}
 */
export const getValidChildren = (editor) => editor.options.get(valid_children);
