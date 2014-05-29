<?php
//if (!file_exists('LookingGlass/Config.php')) {
//  exit('Config.php does not exist. Please run configure.sh');
//}
require 'LookingGlass/Config.php';
?>

<!--
                 __ _
               .: .' '.
              /: /     \_
             ;: ;  ,-'/`:\
             |: | |  |() :|
             ;: ;  '-.\_:/
              \: \     /`
               ':_'._.'
                  ||
                 /__\
      .---.     {====}
    .'   _,"-,__|::  |
   /    ((O)=;--.::  |
  ;      `|: |  |::  |
  |       |: |  |::  |            *****************************
  |       |: |  |::  |            * LookingGlass by Telephone *
  |       |: |  |::  |            *  http://iamtelephone.com  *
  |       |: |  |::  |            *****************************
  |       |: |  |::  |
  |      /:'__\ |::  |
  |     [______]|::  |
  |      `----` |::  |__
  |         _.--|::  |  ''--._
  ;       .'  __{====}__      '.
   \    .'_.-'._ `""` _.'-._    '.
    '--'/`      `''''`      `\    '.__
        '._                _.'
           `""--......--""`
-->
    <!-- Container -->
    <div class="container">

      <!-- Header -->
      <header class="header nohighlight" id="overview">
        <div class="row">
          <div class="span12">
            <h1><a id="title" href="<?php echo $_SERVER['REQUEST_URI']; ?>"><?php echo $siteName; ?></a></h1>
          </div>
        </div>
      </header>

      <!-- Network Information -->
      <section id="information">
        <div class="row">
          <div class="span12">
            <div class="well">
              <span id="legend">Network information</span><!-- IE/Safari dislike <legend> out of context -->
              <p>Server Location: <b><?php echo $serverLocation; ?></b></p>
              <div style="margin-left: 10px;">
                <p>Test IPv4: <?php echo $ipv4; ?></p>
                <p><?php if (!empty($ipv6)) { echo 'Test IPv6: ',$ipv6; } ?></p>
                <p>Test files: <?php
                  foreach ($testFiles as $val) {
                    echo "<a href=\"{$val}.test\" id=\"testfile\">{$val}</a> ";
                  }
                ?></p>
              </div>
              <p>Your IP Address: <b><a href="#tests" id="userip"><?php echo $_SERVER['REMOTE_ADDR']; ?></a></b></p>
            </div>
          </div>
        </div>
      </section>

      <!-- Network Tests -->
      <section id="tests">
        <div class="row">
          <div class="span12">
            <form class="well form-inline" id="networktest" action="#results" method="post">
              <fieldset>
                <span id="legend">Network tests</span>
                <div id="hosterror" class="control-group">
                  <div class="controls">
                    <input id="host" name="host" type="text" class="input-large" placeholder="Host or IP address">
                  </div>
                </div>
                <select name="cmd" class="input-medium" style="margin-left: 5px;">
                  <option value="host">host</option>
                  <option value="mtr">mtr</option>
                  <?php if (!empty($ipv6)) { echo '<option value="mtr6">mtr6</option>'; } ?>
                  <option value="ping" selected="selected">ping</option>
                  <?php if (!empty($ipv6)) { echo '<option value="ping6">ping6</option>'; } ?>
                  <option value="traceroute">traceroute</option>
                  <?php if (!empty($ipv6)) { echo '<option value="traceroute6">traceroute6</option>'; } ?>
                </select>
                <button type="submit" id="submit" name="submit" class="btn btn-primary" style="margin-left: 10px;">Run Test</button>
              </fieldset>
            </form>
          </div>
        </div>
      </section>

      <!-- Results -->
      <section id="results" style="display:none">
        <div class="row">
          <div class="span12">
            <div class="well">
              <span id="legend">Results</span>
              <pre id="response" style="display:none"></pre>
            </div>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <footer class="footer nohighlight">
        <p class="pull-right">
            <a href="#">Back to top</a>
        </p>
        <p>Powered by <a href="http://github.com/telephone/LookingGlass">LookingGlass</a></p>
      </footer>

    </div><!-- /container -->
