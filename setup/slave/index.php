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

// set commands
$commands = array('host', 'mtr', 'mtr6', 'ping', 'ping6', 'traceroute', 'traceroute6');

if (isset($_GET['cmd']) && isset($_GET['host'])) {
    // required config/lib
    require 'config/config.php';
    require 'lib/lookingglass.php';

    // check slave for appropriate server
    foreach ($slave as $val) {
        if ($_SERVER['SERVER_ADDR'] === $val['ipv4'] || $_SERVER['SERVER_ADDR'] === $val['ipv6']) {
            if (!in_array($_SERVER['REMOTE_ADDR'], $val['allowed'])) {
                exit('Unauthorized request');
            }

            $server = $val;
            break;
        }
    }

    // exit if local IP is not found
    if (!isset($server)) {
        exit('Unauthorized request');
    }

    // validate command (prevent abuse)
    if (($server['commands'][0] === 'all' && in_array($_GET['cmd'], $commands))
        || in_array($_GET['cmd'], $server['commands'])
    ) {
        // check for MTR and validate address
        if (($_GET['cmd'] === 'mtr' || $_GET['cmd'] === 'mtr6') && (isset($_GET['address'])
            && isset($server['mtr'][0]))
        ) {
            if (isset($server['mtr'][$_GET['address']])) {
                $output = LookingGlass::execute('local', $_GET['cmd'], $_GET['host'],
                    array('address' => $_GET['address']));
            } else {
                exit('Unauthorized request');
            }
        } else {
            $output = LookingGlass::execute('local', $_GET['cmd'], $_GET['host']);
        }

        // confirm response (successful command)
        if ($output) {
            exit();
        }
    }
}

// report error
exit('Unauthorized request');