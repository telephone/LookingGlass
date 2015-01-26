<?php
/**
 * LookingGlass - User friendly PHP Looking Glass
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick@iamtelephone.com>
 * @copyright   2015 Nick Adams.
 * @link        http://iamtelephone.com
 * @license     http://opensource.org/licenses/MIT MIT License
 * @version     1.3.0
 */
namespace Telephone\LookingGlass;

/**
 * Implement rate limiting of network commands
 */
class RateLimit
{
    /**
     * Check rate limit against SQLite database
     *
     * @param  integer $limit
     *   Number of commands per hour
     * @return boolean
     *   True on success
     */
    public function rateLimit($limit)
    {
        // check if rate limit is disabled
        if ($limit === 0) {
            return false;
        }

        /**
         * check for DB file
         * if nonexistent, no rate limit is applied
         */
        if (!file_exists('LookingGlass/ratelimit.db')) {
            return false;
        }

        // connect to DB
        try {
            $dbh = new \PDO('sqlite:LookingGlass/ratelimit.db');
        } catch (PDOException $e) {
            exit($e->getMessage());
        }

        // check for IP
        $q = $dbh->prepare('SELECT * FROM RateLimit WHERE ip = ?');
        $q->execute(array($_SERVER['REMOTE_ADDR']));
        $row = $q->fetch(\PDO::FETCH_ASSOC);

        // save time by declaring time()
        $time = time();

        // if IP does not exist
        if (!isset($row['ip'])) {
            // create new record
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
                    exit('Rate limit exceeded. Try again in: 1 minute');
                }
                exit('Rate limit exceeded. Try again in: ' . $reset . ' minutes');
            }
            // update hits
            $q = $dbh->prepare('UPDATE RateLimit SET hits = ? WHERE ip = ?');
            $q->execute(array(($hits + 1), $_SERVER['REMOTE_ADDR']));
        } else {
            // reset hits + accessed time
            $q = $dbh->prepare('UPDATE RateLimit SET hits = ?, accessed = ? WHERE ip = ?');
            $q->execute(array(1, time(), $_SERVER['REMOTE_ADDR']));
        }

        $dbh = null;
        return true;
    }
}