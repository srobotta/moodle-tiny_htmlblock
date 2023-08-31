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
 * HTML Block settings.
 *
 * @package   tiny_htmlblock
 * @copyright 2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use \tiny_htmlblock\config;
use \tiny_htmlblock\plugininfo;

$ADMIN->add('editortiny', new admin_category(plugininfo::COMPONENT, new lang_string('pluginname', plugininfo::COMPONENT)));

$settings = new admin_settingpage(plugininfo::COMPONENT . '_settings', new lang_string('settings', plugininfo::COMPONENT));

if ($ADMIN->fulltree) {
    $config = new config();
    $config->add_admin_settings($settings);
}


