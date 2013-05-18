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
session_regenerate_id(TRUE);

require 'config/config.php';

// resolve request/location
$request = (substr($_GET['location'], 0, 1) === '/')
  ? strtolower(end(explode('/', $_GET['location'])))
  : strtolower($_GET['location']);

if (!empty($request)) {
  if ($request !== 'locations') {
    // check master
    if ($request === strtolower($master['nickname'])) {
      $server = $master;
    } else {
      // check slave/s
      if (isset($slave[0]) && !empty($slave[0]['nickname'])) {
        $locations = true;
        foreach ($slave as $val) {
          if ($request === strtolower($val['nickname'])) {
            $server = $val;
            break;
          }
        }
      }
    }
  } else {
    $server = 'locations';
  }
} else {
  $server = $master;
}

// location request does not match
if (!isset($server) || ($server === 'locations' && (!isset($slave[0])
    || empty($slave[0]['nickname'])))
) {
  header('Location: ' . $master['url']);
  exit;
}

// enable locations menu/page
$loc = array('menu' => false, 'page' => false);
if (isset($locations) || (isset($slave[0]) && !empty($slave[0]['nickname']))) {
  $loc['menu'] = true;
  if ($server === 'locations') {
    $loc['page'] = true;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<!--
                  / .-
                  |/,-'`
               _.-'''-._
             /` __   __ `\
        ___ ;__.--._.--.__;              ********************************
       /  /\  ( O / \ O )  `\            * LookingGlass v2 by Telephone *
       | _\/_  '-'   '-'   _/            *    http://iamtelephone.com   *
      _|_|' |     (_)     |              ********************************
     /  __) |             |
     |  __) |    .___.    |     .-.  _
     | ___)';   /\.-./\   ;     | | / |
     |~||/\ .\    `-`    /    __| |/ /_
     | \_\/==;'._  -  _.'__  (_       _)
     \  8      /\"""""/\   `\  `|  .'`
      '--8----.`-`\^/`-`.    \  |~~|
          8   |   /~\   |`\   \ |  |
          8   |   |\|   |  \   `y  |
          8   |   |\|   |   \      /
          8   |   |\|   |    '.__.'
          8   |___|\|___|
          8   |===\_/===|
-->
  <head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $description; ?>">
    <meta name="author" content="Telephone">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/lookingglass.min.css">
    <!-- FONT -->
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,400|Droid+Sans|Lobster|Ubuntu+Mono">
    <!-- Favicon & Touch Icons -->
    <link rel="shortcut icon" href="assets/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>

    <!-- Wrapper -->
    <div class="wrapper">

      <!-- Header -->
      <div class="container nohighlight">
        <div class="header row">
          <div class="span12">
            <div class="navbar">
              <div class="navbar-inner">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                </a>
                <h1><a href="<?php echo $master['url']; ?>"><?php echo $header; ?></a></h1>
                <div class="nav-collapse collapse">
                  <ul class="nav pull-right">
                    <li class="<?php if ($server !== 'locations') { echo 'current-page'; } ?>">
                      <a href="<?php echo $master['url']; ?>"><i class="icon-home"></i><br>Home</a>
                    </li>
                    <?php
                      if (isset($loc['menu'])) {
                        echo ($server === 'locations') ? '<li class="current-page">': '<li>',
                          '<a href="locations"><i class="icon-globe"></i><br>Locations</a></li>', "\n";
                      }
                    ?>
                    <li>
                      <?php echo "<a href=\"{$website[1]}\"><i class=\"icon-share\"></i><br>{$website[0]}</a>\n"; ?>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Page Title -->
      <div class="page-title nohighlight">
        <div class="container">
          <div class="row">
            <div class="span12">
              <i class="icon-signal page-title-icon"></i>
              <h2>Looking Glass /</h2>
              <p><?php echo $welcome; ?></p>
            </div>
          </div>
        </div>
      </div>

      <?php include ($loc['page']) ? 'template/locations.tpl' : 'template/home.tpl'; ?>

      <!-- Footer -->
      <footer>
        <div class="container">
          <div class="row nohighlight">
            <div class="copyright span12">
              <p class="pull-left">
                Powered by <a href="http://github.com/telephone/LookingGlass">LookingGlass</a>
              </p>
              <p class="pull-right social">
                <?php
                  foreach ($social as $key => $val) {
                    if (!empty($val)) {
                      echo ($key === 'rss') ? "<a class=\"icon-{$key}\" href=\"{$val}\"></a>\n"
                        : "<a class=\"icon-{$key}-sign\" href=\"{$val}\"></a>\n";
                    }
                  }
                ?>
              </p>
            </div>
          </div>
        </div>
      </footer>

    </div> <!-- End Wrapper -->

    <!-- Javascript -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="assets/js/lookingglass.min.js"></script>
  </body>
</html>