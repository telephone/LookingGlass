<?php
/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2012 Nick Adams <nick89@zoho.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick89@zoho.com>
 * @copyright   2012 Nick Adams.
 * @link        http://iamtelephone.com
 * @version     1.2.0
 */
// check whether command and host are set
if (isset($_GET['cmd']) && isset($_GET['host'])) {
    // define available commands
    $cmds = array('host', 'mtr', 'mtr6', 'ping', 'ping6', 'traceroute', 'traceroute6');
    // verify command
    if (in_array($_GET['cmd'], $cmds)) {
        // include required scripts
        $required = array('LookingGlass.php', 'RateLimit.php', 'Config.php');
        foreach ($required as $val) {
            require 'LookingGlass/' . $val;
        }

        // instantiate LookingGlass & RateLimit
        $lg = new Telephone\LookingGlass();
        $limit = new Telephone\LookingGlass\RateLimit($rateLimit);

        // check IP against database
        $limit->rateLimit($rateLimit);

        // execute command
        $output = $lg->$_GET['cmd']($_GET['host']);
        if ($output) {
            exit();
        }
    }
}
// report error
exit('Unauthorized request');