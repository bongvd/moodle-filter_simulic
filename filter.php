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
 * Entry point for Moodle versions before 4.5, which look for the class filter_simulic in this file.
 *
 * @package    filter_simulic
 * @copyright  2026 TEXVN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class_alias(\filter_simulic\text_filter::class, 'filter_simulic');
