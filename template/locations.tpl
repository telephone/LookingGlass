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

// set master location
$locations = array(
  key($master['location']) => array(
    $master['nickname'] => current($master['location'])
  )
);

// set slave location/s
foreach ($slave as $val) {
  $locations = array_merge_recursive($locations, array(
    key($val['location']) => array($val['nickname'] => current($val['location']))
  ));
}

ksort($locations);

// separate locations based on key
foreach ($locations as $key => $val) {
  asort($val, SORT_STRING);

  echo <<<EOD
    <!-- Title Block -->
    <div class="container section-block nohighlight">
      <div class="row">
        <div class="span12">
          <div class="section-title">
            <h3>{$key}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Location -->
    <div class="container nohighlight">
      <div class="row">
        <div class="span12 location">
          <ul class="nav nav-pills nav-stacked">
EOD;

  foreach ($val as $nick => $loc) {
    echo "<li><a href=\"{$nick}\">{$loc}</a></li>\n";
  }

  echo <<<EOD
          </ul>
        </div>
      </div>
    </div>
EOD;
}