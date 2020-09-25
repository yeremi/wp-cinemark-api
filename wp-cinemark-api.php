<?php
/**
 * Plugin Name: Cinemark Api Integration
 * Plugin URI:  https://www.linkedin.com/in/yeremiloli/
 * Description: API integration with Cinemark movie theaters
 * Version:     1.0.0
 * Author:      Yeremi Loli
 * Author URI:  https://www.linkedin.com/in/yeremiloli/
 * Text Domain: cinemark-api
 * Domain Path: /language
 */

/**
 * Copyright (C) 2020 Yeremi Felix Loli Saquiray
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (defined('ABSPATH') === false) {
    exit;
}

/**
 * Define the plugin version
 */
define('CINEMARK_API_VERSION', '1.0.0');

/**
 * Define the plugin directory path
 */
define('CINEMARK_API_PATH', plugin_dir_path(__FILE__));

/**
 * Define the plugin directory url
 */
define('CINEMARK_API_URL', plugin_dir_url(__FILE__));

/**
 *
 */
require_once(__DIR__ . '/classes/YeremiCinemarkApi.php');

/**
 * Init plugin
 */
new YeremiCinemarkApi();

/**
 * Check if remote image exists
 * @param $url
 * @return bool
 * https://stackoverflow.com/questions/7684771/how-to-check-if-a-file-exists-from-a-url/29714882
 */
function cinemark_poster_exists($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($code == 200) {
        $status = true;
    } else {
        $status = false;
    }
    curl_close($ch);
    return $status;
}