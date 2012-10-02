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