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
 * Helper class to add admin settings for this plugin.
 *
 * @package     tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_htmlblock;

/**
 * Helper class to handle the settings for the plugin.
 */
class config {

    /**
     * Add settings to admin page for this plugin.
     * @param \admin_settingpage $settings
     * @return void
     * @throws \dml_exception
     */
    public function add_admin_settings(\admin_settingpage $settings) {

        $settings->add(new \admin_setting_configselect(
            plugininfo::COMPONENT . '/maxcatlevels',
            new \lang_string('maxcatlevels', plugininfo::COMPONENT),
            new \lang_string('maxcatlevels_desc', plugininfo::COMPONENT),
            0,
            [new \lang_string('maxcatlevels_all', plugininfo::COMPONENT), '1', '2', '3', '4', '5']
        ));

        $htmlblocksetting = new admin_setting_htmlblock(
            plugininfo::COMPONENT . '/items',
            new \lang_string('blocks', plugininfo::COMPONENT),
            new \lang_string('blocks_desc', plugininfo::COMPONENT),
            ''
        );
        $htmlblocksetting->set_config($this);
        $settings->add($htmlblocksetting);
    }

    /**
     * File name where to store the settings, because they can extend the max size of the text field in the DB.
     * @return string
     */
    protected function get_config_file(): string {
        global $CFG;
        return $CFG->dataroot . DIRECTORY_SEPARATOR . 'tiny_htmlblock.json';
    }

    /**
     * Get the config of the html blocks from the file.
     * @return array|null
     */
    protected function read_items_from_file(): ?array {
        $data = @file_get_contents($this->get_config_file());
        if ($data) {
            return json_decode($data, true);
        }
        return null;
    }

    /**
     * Write the item settings to the file.
     * @param array $data
     * @return void
     */
    public function write_items_to_file(array $data) {
        if (!file_put_contents($this->get_config_file(), json_encode($data))) {
            throw new \RuntimeException('Can not save tiny_htmlblock settings into file ' . $this->get_config_file());
        }
    }

    /**
     * Return a list of categories to select from. Observe that only categories of a certain level are included,
     * if the setting maxcatlevels is > 0. Also if an category id is in $keepselected, then the category is contained,
     * even though it might be at a lower level than desired.
     *
     * @param int[]|null $keepselected
     * @return array
     * @throws \dml_exception
     */
    public function get_category_list(?array $keepselected = []): array {
        $categories = [];
        $delimiter = '~+!~';
        $level = (int)get_config(plugininfo::COMPONENT, 'maxcatlevels');
        $keepselected = \array_flip($keepselected);
        foreach (\core_course_category::make_categories_list('', 0, $delimiter) as $id => $cat) {
            $id = (int)$id;
            if (!isset($keepselected[$id]) && $level > 0) {
                // Count the delimiters, between one category: Main / sub 1 contains one delimiter and is on level 2.
                $catlevel = substr_count($cat, $delimiter);
                if ($catlevel + 1 > $level) {  // If the current level exceeds the desired level, do not include item in list.
                    continue;
                }
            }
            // In the name we must revert the unique delimiter to something nice. We do this here to circumvent the
            // problem in case a category contains " / " within its name.
            $categories[$id] = str_replace($delimiter, ' / ', $cat);
        }
        return $categories;
    }

    /**
     * Get blocks from file or database setting.
     * @return array
     * @throws \dml_exception
     */
    public function get_blocks_from_setting(): array {
        $blocks = $this->read_items_from_file();
        if (!\is_array($blocks)) {
            $blocks = json_decode(get_config(plugininfo::COMPONENT, 'items'), true);
            if (!\is_array($blocks)) {
                return [];
            }
        }
        return $blocks;
    }

    /**
     * Helper function that is used when the plugin is called during the setup of the editor. It fetches the HTML
     * blocks from the settings, filters the entries by the category settings and returns a list of blocks that are
     * used directly in the Javascript inside the editor.
     * @param \core_course_category|null $category
     * @return array
     * @throws \dml_exception
     */
    public function get_blocks_for_editor(\core_course_category $category = null): array {
        // First fetch the setting with the html blocks.
        $blocks = $this->get_blocks_from_setting();

        // Fetch all blocks and run format_string on its name to possibly apply the mlang filter when used in the name.
        $blocks = array_map(
            function($b){
                format_string(trim($b['name']));
                return $b;
            },
            $blocks
        );

        // Filter now out all blocks, that have a category specified, where the edited item does not belong to.
        // If on the user page or some other context, then all the blocks without any category setting are used. If
        // we are on a course or course category page, use the category setting.
        foreach (\array_keys($blocks) as $row) {
            // The block has no category limitation.
            if (empty($blocks[$row]['cat'])) {
                continue;
            }
            // We are in a category and the html block has this category set as well, or any of its parents is
            // defined as the html block.
            if ($category !== null && (
                    \in_array($category->id, $blocks[$row]['cat']) ||
                    !empty(\array_intersect($category->get_parents(), $blocks[$row]['cat']))
                )) {
                continue;
            }
            // Either, we are on a page with a category but the current html block does not contain this
            // category, or we are on a page with no category but the html block has set one.
            unset($blocks[$row]);
        }

        // Return the values, so that the keys are again in ascending order starting with 0. Otherwise, that
        // causes problems when iteration over the array in a mustache template.
        return \array_values($blocks);
    }
}
