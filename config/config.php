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

/**
 * General config
 *
 * $header      -> Logo. Wrap letters in span tags to display in $sheme color
 * $title       -> HTML title
 * $description -> HTML description
 * $website     -> Link back to your main website. array('Link name' => 'Http link')
 * $welcome     -> Sub-Header text (breadcrumbs)
 * $scheme      -> Color scheme (violet)
 */
$header      = '<span>L</span>ooking<span>G</span>lass';
$title       = 'Telephone\'s Looking Glass';
$description = 'LookingGlass - Open Source PHP Looking Glass';
$website     = array('Download', 'https://github.com/telephone/LookingGlass');
$welcome     = 'Welcome to Telephone\'s network Looking Glass';
$scheme      = 'violet';

/**
 * Rate limit
 *
 * $database -> Location of SQLite database file (relative to base directory)
 * $limit    -> Rate limit per hour for network commands/tests (User IP)
 *              0 = disabled
 */
$database = 'db/ratelimit.db';
$limit    = 0;

/**
 * Social Media
 *
 * $social -> URL/s to your given profile pages for various social networks
 *            (Empty urls are not shown)
 */
$social = array(
    'google-plus' => '',
    'github'      => 'http://github.com/telephone',
    'linkedin'    => '',
    'facebook'    => '',
    'twitter'     => 'http://twitter.com/bigtelephone',
    'rss'         => ''
);

/**
 * Master server
 *
 * $master -> Config/Setup for main/front-end website. This server will be
 *            loaded as the index page for your Looking Glass
 *   'commands' -> Network commands to enable. Use 'all' to enable all available options
 *                 Options include: host, mtr, mtr6, ping, pin6, traceroute,
 *                                  traceroute6
 *   'ipv4'     -> Test IPv4 address (Can be left empty)
 *   'ipv6'     -> Test IPv6 address (Can be left empty)
 *   'files'    -> Size of test files to server. Use 'MB' or 'GB'
 *   'location' -> Server location
 *                   array('Sub location... Continent, or country', 'Server location')
 *   'mtr'      -> Bind outgoing packets' to a specific interface. (Use IP Address of interface)
 *                 E.g. 'mtr' => array(
 *                          'ipv4' => array('Cogent' => '10.0.0.2', 'Level3' => '10.0.0.1'),
 *                          'ipv6' => array('Hurricane Electric' => '2001:470:c:988::5',
 *                          'Level3' => '2001:48c8:0010::5')
 *                      )
 *   'nickname' -> Used to provide routing to a specific location. (Must be unique)
 *                 E.g. http://mywebsite.com/Milan -> Will display the Looking Glass
 *                   that has the matching 'nickname'
 *   'url'      -> URL to access the homepage of your Looking Glass install
 *
 *  ** NOTE **
 *    DO NOT REMOVE any options from array. Leave blank if do not wish to enable
 *    a feature
 */
$master = array(
    'commands' => array('all'),
    'ipv4'     => '199.241.138.76',
    'ipv6'     => '',
    'files'    => array('25MB', '50MB', '100MB'),
    'location' => array('North America' => 'Las Vegas, Nevada'),
    'mtr'      => array(),
    'nickname' => 'Las_Vegas',
    'url'      => 'http://lg.iamtelephone.com/dev/'
);

/**
 * Slave server/s
 *
 * $slave -> Follow the same directions as your '$master'. Differences are below:
 *   'allowed' -> The IP address of your master server that will be connecting
 *                to the slave servers
 *                (Your outgoing/public IP for your Looking Glass)
 *   'url'     -> Public URL to the Looking Glass install
 *
 * ** NOTE **
 *   For multiple slave servers, simply create a new array for each server
 *   E.g.
 *     $slave[] = array('slave #1');
 *     $slave[] = array('slave #2');
 */
$slave = array();   // DO NOT DELETE OR EDIT THIS LINE

/**
 * If you do not want a slave (or multiple slave) server/s, then delete
 * below this line.
 */
$slave[] = array(
    'allowed'  => array('199.241.138.76'),
    'commands' => array('all'),
    'ipv4'     => '37.247.50.228',
    'ipv6'     => '2a00:dcc0:eda:88:50:192:3871:83f5',
    'files'    => array('10MB', '25MB', '50MB'),
    'location' => array('Europe' => 'Milan, Italy'),
    'mtr'      => array(),
    'nickname' => 'Milan',
    'url'      => 'http://milan.iamtelephone.com/'
);