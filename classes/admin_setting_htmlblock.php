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
 * Class that allow configuring a list of htmlblock elements.
 *
 * @package     tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_htmlblock;

use admin_setting;

/**
 * Tiny htmlblock plugin config utility.
 *
 * @package     tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_htmlblock extends admin_setting {

    /**
     * Placeholder that is used as a value for the value of the original settings hidden input field.
     * @var string
     */
    private const PLACEHOLDER_ORIG_VALUE = '~~1~~+';

    /**
     * Store here the data that where extracted from the post request when saving.
     * @var array
     */
    private $settingval;

    /**
     * The current config object.
     * @var $config
     */
    private $config;

    /**
     * @param config $config
     * @return admin_setting_htmlblock
     */
    public function set_config(config $config): self {
        $this->config = $config;
        return $this;
    }

    /**
     * Get the config object.
     * @return array
     */
    public function get_config(): config {
        if ($this->config === null) {
            $this->config = new config();
        }
        return $this->config;
    }

    /**
     * Return an XHTML string for the setting.
     * @param mixed $data
     * @param string $query
     * @return string Returns an XHTML string
     * @throws \coding_exception
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        // The original object is destroyed, so we don't have information about the error. However, if
        // we identify the value being sent from the current post, then just fetch the original data again
        // from the request, validate it to know which field exactly caused the trouble being not valid.
        if ($data === static::PLACEHOLDER_ORIG_VALUE) {
            $this->get_setting_val_from_request();
            $htmlblocks = $this->settingval;
        } else {
            // Assume here that we read the json from the config and have a valid array.
            if (\is_array($data) && isset($data[0]) && isset($data[0]['html'])) {
                $htmlblocks = $data;
            } else {
                $htmlblocks = [];
            }
        }

        // Add an empty value to have a black input line below the already defined colors.
        $htmlblocks[] = [
            'name' => '',
            'cat' => [],
            'html' => '',
        ];

        $default = $this->get_defaultsetting();
        $context = (object) [
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'value' => static::PLACEHOLDER_ORIG_VALUE,
            'forceltr' => $this->get_force_ltr(),
            'readonly' => $this->is_readonly(),
            'items' => [],
        ];

        foreach (\array_keys($htmlblocks) as $i) {
            $row = [
                'first' => $i === 0,
                'last' => $i + 1 === count($htmlblocks),
            ];
            foreach (['name', 'cat', 'html'] as $field) {
                $suffix = '_' . $field . '_' . ($i + 1);
                $value = $htmlblocks[$i][$field] ?? '';
                // The cat is a selection field with multiple possible values.
                if ($field === 'cat') {
                    $selectedIds = \is_array($value) ? $value : [];
                    $value = [];
                    foreach ($this->get_config()->get_category_list($selectedIds) as $catid => $catname) {
                        $value[] = [
                            'id' => (int)$catid,
                            'name' => $catname,
                            'isselected' => (bool)count(array_filter($selectedIds, fn($selid) => $selid === $catid)),
                        ];
                    }
                }

                $row[$field] = (object)[
                    'id' => $this->get_id() . $suffix,
                    'name' => $this->get_full_name() . $suffix,
                    'value' => $value,
                ];
            }
            $context->items[] = $row;
        }
        $html = $OUTPUT->render_from_template('tiny_htmlblock/setting_config_htmlblock', $context);

        return format_admin_setting($this, $this->visiblename, $html, $this->description, true, '', $default, $query);
    }

    /**
     * Data must be validated.
     * @return bool
     */
    public function validate(): bool {
        return true;
    }

    /**
     * Get complex settings value (that is later converted into a json) from the
     * POST params (i.e. from the single input fields of color name and value).
     *
     * @return array
     */
    protected function get_setting_val_from_request(): array {
        if ($this->settingval === null) {
            $this->settingval = [];
            $blocks = [];
            $categories = [];
            $names = [];
            foreach ($_REQUEST as $key => $val) {
                if (strpos($key, $this->name . '_html_') !== false) {
                    $blocks[$key] = trim($val);
                } else if (strpos($key, $this->name . '_cat_') !== false) {
                    $categories[$key] = \is_array($val) ? \array_map(fn($v) => (int)$v, $val) : [];
                } else if (strpos($key, $this->name . '_name_') !== false) {
                    $names[$key] = trim($val);
                }
            }
            foreach (\array_keys($blocks) as $i) {
                if (empty($blocks[$i])) {
                    continue;
                }
                $c = str_replace('_html_', '_cat_', $i);
                $n = str_replace('_html_', '_name_', $i);
                $this->settingval[] = [
                    'html' => $blocks[$i],
                    'cat' => $categories[$c] ?? '',
                    'name' => $names[$n] ?? '',
                ];
            }
        }
        return $this->settingval;
    }

    /**
     * Reads out a setting
     *
     * @return false|mixed|string|null
     */
    public function get_setting() {
        return $this->settingval ?? (new config())->get_blocks_from_setting();
    }

    /**
     * Writes in a setting
     *
     * @param array $data The data to write
     * @return bool|\lang_string|string
     * @throws \coding_exception
     */
    public function write_setting($data) {
        // The content of $data is ignored here, it must be fetched from the request.
        $data = $this->get_setting_val_from_request();
        if ($this->validate() !== true) {
            return false;
        }
        (new config())->write_items_to_file($data);
        return ($this->config_write($this->name, json_encode($data)) ? '' : get_string('errorsetting', 'admin'));
    }
}
