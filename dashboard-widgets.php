<?php
/**
 * Plugin Name:  Dashboard widgets
 * Plugin URI:   https://github.com/sylletka/wordpress-dashboard-widgets
 * Description:  This plugin allows site administrator to easily add simple text widgets to the dashboard
 * Author:       Samuele Saorin
 * Author URI:   https://github.com/sylletka/
 * Version:      1.0.2
 * Text Domain:  dashboard-widgets
 * Domain Path:  /languages
 *
 *
 * Released under the GPL license
 * https://www.gnu.org/licenses/gpl.html
 *
 * This is a plugin for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 *
 *   Copyright (C) 2017 Samuele Saorin
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * **********************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( __DIR__ . '/classes/dashboardWidgets.php' );
add_action( 'plugins_loaded', array( dashboardWidgets::get_instance(), 'init' ) );

