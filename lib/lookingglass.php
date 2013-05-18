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
 * Output buffering of network commands
 */
class LookingGlass
{
    /**
     * Allowed number of consecutive failed hops via traceroute
     * @var integer
     */
    public static $failedHops = 2;

    /**
     * Number of iterations to perform via ping
     * @var integer
     */
    private static $pingCount = 4;

    /**
     * Execute network command
     *   - Local (master) uses proc. Remote (slave) uses cURL
     *
     * @param  string         $source   Either 'local', or the slave URL
     * @param  string         $cmd      Network command to execute
     * @param  string         $host     IP/URL to perform command against
     * @param  boolean|string $params   Additional parameters to pass to command/s
     * @return boolean|string           True on success
     *                                    - Output result of network command
     */
    public static function execute($source = 'local', $cmd, $host, $params = false)
    {
        $type = substr($cmd, -1, 1);

        if ($type !== '6' && $type !== 't') {
            $type = '4';
        }

        switch ($type) {
            case 't':    // host
            case '4':    // IPv4
                if (Validate::host($host)) {
                    $host = Validate::host($host);
                    break;
                }
            case '6':    // IPv6
                if ($type !== '4' && Validate::host($host, 6)) {
                    $host = Validate::host($host, 6);
                    break;
                }
        }

        if (!isset($host) || $host === false) {
            return false;
        }

        // remote
        if ($source !== 'local') {
            $source .= (substr($source, -1, 1) !== '/') ? '/index.php?' : 'index.php?';
            return Curl::get($source . http_build_query(array('cmd' => $cmd, 'host' => $host)));
        }

        $cmd = preg_replace('[\d]', '', $cmd);

        switch ($cmd) {
            case 'host':
                $cmd = 'host';
                break;
            case 'mtr':
                $cmd = 'mtr -' . $type . ' -r -w';
                if (isset($params['address'])) {
                    $cmd .= ' -a ' . $params['address'];
                }
                break;
            case 'ping':
                $cmd = ($type == '4')
                    ? 'ping  -c' . static::$pingCount . ' -w15'
                    : 'ping6  -c' . static::$pingCount . ' -w15';
                break;
            case 'traceroute':
                $cmd = 'traceroute -' . $type . ' -w2';
                break;
        }

        // local
        return CLI::exec($cmd, $host);
    }
}

/**
 * User validation
 */
class Validate
{
    /**
     * Validate IP and URL
     *
     * @param  string  $host  IP/URL to validate
     * @param  integer $type  Define 4 or 6 for IPv4 or IPv6 validation
     * @return boolean        True on success
     */
    public static function host($host, $type = 4)
    {
        // validate ip
        if (static::ip($host, $type)) {
            return $host;
        }
        // validate that IPv4 doesn't pass as a valid URL for IPv6
        elseif ($type === 6 && static::ip($host, 4)) {
            return false;
        }
        // validate url
        elseif ($host = static::url($host)) {
            return $host;
        }
        return false;
    }

    /**
     * Validate IP: IPv4 or IPv6
     *
     * @param  string  $host  IP to validate
     * @param  integer $type  Define 4 or 6 for IPv4 or IPv6 validation
     * @return boolean        True on success
     */
    private static function ip($host, $type = 4)
    {
        // IPv4
        if ($type === 4) {
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)
            ) {
                return true;
            }
        }
        // IPv6
        else {
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate hostname/URL
     *
     * @param  string  $url  Hostname or URL to validate
     * @return boolean       True on success
     */
    private static function url($url)
    {
        // check for http
        if (substr($url, 0, 4) != 'http') {
            $url = 'http://' . $url;
        }

        // validate url
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            if ($host = parse_url($url, PHP_URL_HOST)) {
                return $host;
            }
            return $url;
        }
        return false;
    }
}

/**
 * Execute a command and open pipes for input/output
 */
class CLI
{
    /**
     * Execute command via proc_open
     * This is a work around to terminate the command if X consecutive
     * timeouts occur (traceroute)
     *
     * @param  string  $cmd   Command to perform
     * @param  string  $host  IP/URL to issue command against
     * @return boolean        True on success
     */
    public static function exec($cmd, $host)
    {
        // define output pipes
        $spec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        $host = str_replace('\'', '', filter_var($host, FILTER_SANITIZE_URL));
        $process = proc_open("{$cmd} '{$host}'", $spec, $pipes, null);

        if (!is_resource($process)) {
            return false;
        }

        // check for mtr/traceroute
        if (strpos($cmd, 'mtr') !== false) {
            $type = 'mtr';
        } elseif (strpos($cmd, 'traceroute') !== false) {
            $type = 'traceroute';
        } else {
            $type = '';
        }
        $fail = $match = $traceCount = 0;
        $lastFail = 'start';

        // iterate stdout
        while (($str = fgets($pipes[1], 1024)) != null) {
            ob_start();
            $str = trim($str);

            // correct output for mtr
            if ($type === 'mtr') {
                if ($match < 10 && preg_match('/^[\d]\. /', $str, $string)) {
                    $str = preg_replace('/^[\d]\. /', '&nbsp;&nbsp;' . $string[0], $str, 1);
                    $match++;
                } else {
                    $str = preg_replace('/^[\d]{2}\. /', '&nbsp;' . substr($str, 0, 4), $str);
                }
                preg_match('/([\d]+\.[\d]+%|100\.0\s{4}(10|9))/', $str, $string);
                // correct Ubuntu
                if (strpos($str, '|--') !== false) {
                    $str = str_replace('|--', '', $str);
                    $str = str_replace($string[0], '&nbsp;&nbsp;&nbsp;' . $string[0], $str);
                } else {
                    $str = str_replace($string[0], '&nbsp;' . $string[0], $str);
                }
            }
            // correct output for traceroute
            elseif ($type === 'traceroute') {
                if ($match < 10 && preg_match('/^[\d] /', $str, $string)) {
                    $str = preg_replace('/^[\d] /', '&nbsp;' . $string[0], $str, 1);
                    $match++;
                }
                // check for consecutive failed hops
                if (strpos($str, '* * *') !== false) {
                    $fail++;
                    if ($lastFail !== 'start' && ($traceCount - 1) === $lastFail
                        && $fail >= LookingGlass::$failedHops
                    ) {
                        /**
                         * Traceroute has to be killed first (before cleaning up proc_)
                         *   - Fixes issue of delayed output
                         */
                        $status = proc_get_status($process);
                        posix_kill($status['pid'], 9);
                        foreach ($pipes as $pipe) {
                            fclose($pipe);
                        }
                        proc_close($process);
                        static::responsePad($str . '<br><b>-- Traceroute timed out --</b><br>');
                        break;
                    }
                    $lastFail = $traceCount;
                }
                $traceCount++;
            }

            // output response
            static::responsePad($str . '<br>');
        }

        // check for error in host
        $error = fgets($pipes[2], 1024);
        if (!empty($error) && ((strpos($error, 'Name or service not known') !== false
            || strpos($error, 'unknown host') !== false
            || strpos($error, 'Cannot handle "host"') !== false))
        ) {
            static::responsePad($str . '<br><b>Error: Name or service not known</b><br><br>');
        } elseif (strpos($error, 'no version information available') !== false) {
            static::responsePad('<br><b>Error: OpenSSL error (report to admin)</b><br><br>');
        }

        $status = proc_get_status($process);
        if ($status['running'] === true) {
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }

            // kill remaining processes
            $ppid = $status['pid'];
            $pids = preg_split('/\s+/', `ps -o pid --no-heading --ppid $ppid`);
            foreach($pids as $pid) {
                if (is_numeric($pid)) {
                    posix_kill($pid, 9);
                }
            }
            proc_close($process);
        }
        return true;
    }

    /**
     * Pad response to enable output buffering
     *
     * @param  string $str Response (string to send)
     * @return             Output padded response
     */
    private static function responsePad($str)
    {
        (ob_get_level()) ? ob_end_flush() : ob_start();

        echo str_pad($str, 1024, ' ', STR_PAD_RIGHT);
        @ob_flush();
        flush();
    }
}

/**
 * cURL class to communicate with slave server/s
 */
class Curl
{
    /**
     * Default cURL options for output buffering
     * @var array
     */
    protected static $options = array(
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_WRITEFUNCTION  => 'static::outputResponse'
    );

    /**
     * Perform a cURL request
     *
     * @param  string  $url      URL to request
     * @param  boolean $options  cURL options
     * @return string            Response if options do not declare write function
     */
    public static function get($url, $options = false)
    {
        // use default options if none are provided
        $setopt = array(CURLOPT_URL => $url) + (($options) ? $options : static::$options);

        // initiate curl
        $ch = curl_init();
        curl_setopt_array($ch, $setopt);
        $res = curl_exec($ch);

        // check for timeout
        if (curl_errno($ch) === 28) {
            exit("\n<br>\n** Connection timed out. Try again later **\n");
        }

        curl_close($ch);

        if (!isset($options[CURLOPT_WRITEFUNCTION])) {
            return $res;
        }

        return true;
    }

    /**
     * Provide output buffering via cURL (write function)
     *
     * @param  object  $ch      cURL object
     * @param  string  $string  cURL response
     * @return integer          Length of cURL response
     */
    private static function outputResponse($ch, $string)
    {
        if (ob_get_level() == 0) {
            ob_start();
        }

        echo $string;

        @ob_flush();
        flush();

        return strlen($string);
    }
}

/**
 * Rate limiting via SQLite
 */
class RateLimit
{
    /**
     * Check rate limit against SQLite database
     *
     * @param  integer $limit  Number of commands per hour
     * @return boolean         True on success
     */
    public static function limit($limit, $database)
    {
        /**
         * - check if rate limit is disabled
         * - if DB file does not exist, check permissions
         */
        if ($limit == 0) {
            return false;
        }

        if (is_file($database)) {
            // connect to DB
            try {
                $dbh = new PDO('sqlite:' . $database);
            } catch (PDOException $e) {
                exit($e->getMessage());
            }
        } else {
            $path = dirname($database);
            if (!is_writable(($path != '') ? $path : getcwd())) {
                return false;
            }

            // connect to DB
            try {
                $dbh = new PDO('sqlite:' . $database);
            } catch (PDOException $e) {
                exit($e->getMessage());
            }

            // create table
            $dbh->exec(
                'CREATE TABLE RateLimit (
                    ip TEXT UNIQUE NOT NULL,
                    hits INTEGER NOT NULL DEFAULT 0,
                    accessed INTEGER NOT NULL
                )'
            );
            $dbh->exec('CREATE UNIQUE INDEX "RateLimit_ip" ON "RateLimit" ("ip")');
        }

        // check for IP
        $q = $dbh->prepare('SELECT * FROM RateLimit WHERE ip = ?');
        $q->execute(array($_SERVER['REMOTE_ADDR']));
        $row = $q->fetch(PDO::FETCH_ASSOC);

        $time = time();

        // create new record (if IP does not exist)
        if (!isset($row['ip'])) {
            $q = $dbh->prepare('INSERT INTO RateLimit (ip, hits, accessed) VALUES (?, ?, ?)');
            $q->execute(array($_SERVER['REMOTE_ADDR'], 1, $time));
            return true;
        }

        // typecast SQLite results
        $accessed = (int) $row['accessed'] + 3600;
        $hits = (int) $row['hits'];

        // apply rate limit
        if ($accessed > $time) {
            if ($hits >= $limit) {
                $reset = (int) (($accessed - $time) / 60);
                if ($reset <= 1) {
                    exit('<b>Rate limit exceeded. Try again in: 1 minute</b>');
                }
                exit('<b>Rate limit exceeded. Try again in: ' . $reset . ' minutes</b>');
            }
            // update hits
            $q = $dbh->prepare('UPDATE RateLimit SET hits = ? WHERE ip = ?');
            $q->execute(array(($hits + 1), $_SERVER['REMOTE_ADDR']));
        } else {
            // reset hits + accessed time
            $q = $dbh->prepare('UPDATE RateLimit SET hits = ?, accessed = ? WHERE ip = ?');
            $q->execute(array(1, $time, $_SERVER['REMOTE_ADDR']));
        }

        // close DB connection
        $dbh = null;
        return true;
    }
}