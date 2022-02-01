
<?php

error_reporting(E_ALL);


/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linkedin.com/in/saidul-alam-6697591b5/
 * @since             1.0.0
 * @package           Gpldl_Plugins_Auto_Update
 *
 * @wordpress-plugin
 * Plugin Name:       GPLDL Plugins Auto Update
 * Plugin URI:
 * Description:       Codibu.com - Auto plugin update / Only works with plugins in Codibu's free plugin list.
 * Version:           1.0.0
 * Author:            Saidul Alam
 * Author URI:        https://www.linkedin.com/in/saidul-alam-6697591b5/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gpldl-plugins-auto-update
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('GPLDL_PLUGINS_AUTO_UPDATE_VERSION', '1.0.0');

/**
 * The code that runs by WP-cron
 * This action is documented gpldl-plugins-auto-update-activator
 */
function fetch_api_be()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://codibu.com/api/download?client_root=' . $_SERVER['SERVER_NAME']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $result = json_decode($response);
    curl_close($ch);

    return $result;
}


/**
 * The code that runs by WP-cron
 * This action is documented in includes/class-gpldl-plugins-auto-update-activator.php
 */

//define('WORDPRESS_BASE_PATH', '/opt/bitnami/apps/wordpress/htdocs');
define('WORDPRESS_BASE_PATH', '/bitnami/wordpress');


function gpldl_plugins_auto_update()
{
    $pluginExtractBaseDestination = WORDPRESS_BASE_PATH . "/wp-content/uploads/plugins/";

    $results     = fetch_api_be();

    if ($results) {
        foreach ($results as $index => $item) {

            $pluginZipDownloadDestination = $pluginExtractBaseDestination . basename($item->download_url);
            $downloadUrl = $item->download_url;
            file_put_contents($pluginZipDownloadDestination, file_get_contents($downloadUrl));
            exec("unzip -o {$pluginZipDownloadDestination} -d {$pluginExtractBaseDestination}");

            unlink($pluginZipDownloadDestination);
        }

        update_plugin_zip();
    }
}


function update_plugin_zip()
{
    $folderToBeCompressed =  WORDPRESS_BASE_PATH . '/wp-content/uploads/plugins/';
    $zipDestinationDirectory = WORDPRESS_BASE_PATH . '/wp-content/plugins/';
    $zipDestinationFile = WORDPRESS_BASE_PATH . '/wp-content/plugins/plugins.zip';


    if (file_exists($zipDestinationFile)) {
        $backupName = "plugins_backup.zip";
        exec("mv {$zipDestinationFile} {$zipDestinationDirectory}{$backupName}");
    }

    exec("cd {$folderToBeCompressed} && zip -r {$zipDestinationFile} .");
}
add_action('gpldl_plugins_auto_update_hook', 'gpldl_plugins_auto_update');
