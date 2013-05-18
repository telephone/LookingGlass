<?php
/**
 * LookingGlass - User friendly PHP Looking Glass
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick89@zoho.com>
 * @copyright   2012 Nick Adams.
 * @link        http://iamtelephone.com
 * @license     http://opensource.org/licenses/MIT MIT License
 * @version     2.0.0
 */
session_start();
if (!isset($_GET['id']) || $_GET['id'] !== session_id()) {
    exit('Unauthorized request');
}

/**
 * Output Buffering for older browser releases (Chrome)
 */
header("Content-Type: text/plain");

// set commands
$commands = array('host', 'mtr', 'mtr6', 'ping', 'ping6', 'traceroute', 'traceroute6');

if (isset($_GET['cmd']) && isset($_GET['host']) && isset($_GET['location'])) {
    // required config/lib
    require 'config/config.php';
    require 'lib/lookingglass.php';

    // validate/identify location
    if ($master['nickname'] === $_GET['location']) {
        $server = $master;
        $source = 'local';
    } else {
        foreach ($slave as $val) {
            if ($val['nickname'] === $_GET['location']) {
                $server = $val;
                $source = $val['url'];
                break;
            }
        }

        // report error
        if (!isset($server)) {
            exit('Unauthorized request');
        }
    }

    // validate command
    if (($server['commands'][0] === 'all' && in_array($_GET['cmd'], $commands))
        || in_array($_GET['cmd'], $server['commands'])
    ) {
        // check IP against database
        RateLimit::limit($limit, $database);

        // check for MTR and validate address
        if (($_GET['cmd'] === 'mtr' || $_GET['cmd'] === 'mtr6') && (isset($_GET['address'])
            && isset($server['mtr'][0]))
        ) {
            if (isset($server['mtr'][$_GET['address']])) {
                $output = LookingGlass::execute($source, $_GET['cmd'], $_GET['host'],
                    array('address' => $_GET['address']));
            } else {
                exit('Unauthorized request');
            }
        } else {
            $output = LookingGlass::execute($source, $_GET['cmd'], $_GET['host']);
        }

        // confirm response (successful command)
        if ($output) {
            exit();
        }
    }
}

// report error
exit('Unauthorized request');