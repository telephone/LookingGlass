<!-- Title Block -->
<div class="container section-block nohighlight">
  <div class="row">
    <div class="span12">
      <div class="section-title">
        <h3>Network Information</h3>
      </div>
    </div>
  </div>
</div>

<!-- Network Information -->
<div class="information container" style="margin-top: 0px;">
  <div class="row">
    <div class="block span4">
      <div class="nohighlight"><i class="icon-globe"></i></div>
      <h4 class="nohighlight">Server Location</h4>
      <p class="block-text"><span><?php echo current($server['location']); ?></span></p>
    </div>
    <div class="block span4">
      <div class="nohighlight"><i class="icon-eye-open"></i></div>
      <h4 class="nohighlight">Test IP</h4>
      <p class="block-text">
        <?php if (!empty($server['ipv4'])) { echo "IPv4: <span>{$server['ipv4']}</span>\n"; } ?>
        <?php if (!empty($server['ipv6'])) { echo "<br>\nIpv6: <span>{$server['ipv6']}</span>\n"; } ?>
      </p>
    </div>
    <div class="block span4">
      <div class="nohighlight"><i class="icon-download-alt"></i></div>
      <h4 class="nohighlight">Test Files</h4>
      <p class="block-text">
        <?php
          $str = '';
          $url = (substr($server['url'], -1, 1) === '/') ? $server['url'] : $server['url'] . '/';
          foreach ($server['files'] as $val) {
            $str .= "<a href=\"{$url}files/{$val}.test\">{$val}</a>&nbsp;&nbsp;";
          }
          echo rtrim($str, '&nbsp;'), "\n";
        ?>
      </p>
    </div>
  </div>
</div>

<!-- Title Block -->
<div class="container section-block nohighlight">
  <div class="row">
    <div class="span12">
      <div class="section-title">
        <h3>Network Tests</h3>
      </div>
    </div>
  </div>
</div>

<!-- Network Test Form -->
<div class="container network">
  <div class="row">
    <div id="tests" class="span12">
      <form class="form-inline" id="networktest" action="#response" method="post">
        <div id="host-error" class="control-group">
          <div class="controls">
            <input name="host" type="text" class="input-xlarge" placeholder="Host or IP address">
          </div>
        </div>
        <div class="control-group">
          <select id="cmd" name="cmd" class="input-medium">
            <option value="host">host</option>
            <option value="mtr">mtr</option>
            <?php if ($server['ipv6']) { echo '<option value="mtr6">mtr6</option>', "\n"; } ?>
            <option value="ping" selected="selected">ping</option>
            <?php if ($server['ipv6']) { echo '<option value="ping6">ping6</option>', "\n"; } ?>
            <option value="traceroute">traceroute</option>
            <?php if ($server['ipv6']) { echo '<option value="traceroute6">traceroute6</option>', "\n"; } ?>
          </select>
        </div>
        <div class="control-group">
          <select id="address4" name="address4" class="input-large">
          <?php
            if (!empty($server['mtr']['ipv4'])) {
              foreach ($server['mtr']['ipv4'] as $key => $val) {
                echo "<option value=\"{$key}\">{$key}</option>\n";
              }
            }
          ?>
          </select>
        </div>
        <div class="control-group">
          <select id="address6" name="address6" class="input-large">
          <?php
            if (!empty($server['mtr']['ipv6'])) {
              foreach ($server['mtr']['ipv6'] as $key => $val) {
                echo "<option value=\"{$key}\">{$key}</option>\n";
              }
            }
          ?>
          </select>
        </div>
        <input name="id" type="hidden" value="<?php echo session_id(); ?>">
        <input name="location" type="hidden" value="<?php echo $server['nickname']; ?>">
        <div class="control-group">
          <button type="submit" id="submit" name="submit">Run Test</button>
          <i class="icon-spinner nohighlight"></i>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Title Block -->
<div id="results-title" class="container section-block nohighlight">
  <div class="row">
    <div class="span12">
      <div class="section-title">
        <h3>Results</h3>
      </div>
    </div>
  </div>
</div>

<!-- Results -->
<div id="results" class="container">
  <div class="row">
    <div class="results-text span12">
      <div id="response">
      </div>
    </div>
  </div>
</div>