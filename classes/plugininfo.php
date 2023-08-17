<?php
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

namespace tiny_htmlblock;

use context;
use editor_tiny\editor;
use editor_tiny\plugin;
use editor_tiny\plugin_with_menuitems;
use editor_tiny\plugin_with_buttons;
use editor_tiny\plugin_with_configuration;

/**
 * Tiny HTML Blocks plugin.
 *
 * @package     tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugininfo extends plugin implements plugin_with_menuitems, plugin_with_buttons, plugin_with_configuration {

    public const COMPONENT = 'tiny_htmlblock';

    /**
     * Get a list of the menu items provided by this plugin.
     *
     * @return string[]
     */
    public static function get_available_menuitems(): array {
        return [
            'tiny_htmlblock/tiny_htmlblock_menu',
        ];
    }

    /**
     * Get a list of the buttons provided by this plugin.
     * @return string[]
     */
    public static function get_available_buttons(): array {
        return [
            'tiny_fontcolor/tiny_htmlblock_btn',
        ];
    }

    /**
     * Returns the configuration values the plugin needs to take into consideration.
     *
     * @param context $context
     * @param array $options
     * @param array $fpoptions
     * @param editor|null $editor
     * @return array
     * @throws \dml_exception
     */
    public static function get_plugin_configuration_for_context(context $context, array $options, array $fpoptions,
                                                                ?editor $editor = null): array {
        global $DB, $PAGE;

        $blocks = json_decode(get_config(self::COMPONENT, 'items'), true);
        if (!\is_array($blocks)) {
            $blocks = [];
        }

        // Fetch all blocks and run format_string on its name to possibly apply the mlang filter when used in the name.
        $blocks = array_map(function($b){ format_string(trim($b['name'])); return $b; }, $blocks);

        // Filter now out all blocks, that have a category specified, where the edited item does not belong to.
        // If on the user page or some other context, then all the blocks without any category setting are used. If
        // we are on a course or course category page, use the category setting.
        $currentcat = null;
        if ($PAGE->course) {
            $currentcat = \core_course_category::get($PAGE->course->category);
        }
        // Now filter the blocks based on the currentcat that we have retrieved from the page we are on.
        foreach (\array_keys($blocks) as $row) {
            // The block has no category limitation.
            if (empty($blocks[$row]['cat'])) {
                continue;
            }
            // We are in a category and the html block has this category set as well, or any of its parents is
            // defined as the html block.
            if ($currentcat !== null && (
                \in_array($currentcat->id, $blocks[$row]['cat']) ||
                !empty(\array_intersect($currentcat->get_parents(), $blocks[$row]['cat']))
            )) {
                continue;
            }
            // Either, we are on a page with a category but the current html block does not contain this
            // category, or we are on a page with no category but the html block has set one.
            unset($blocks[$row]);
        }

        return [
            'htmlblocks' => \array_values($blocks),
            'valid_children' => get_config(self::COMPONENT, 'valid_children'),
        ];
    }
}
