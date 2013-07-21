<?php
/**
 * Email - Email AR class
 * File : /^/models/Email.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * Email class - for managing Emails
 * Stores an email as the raw headers and body
 * Stores redundantly headers for {to, from, subject, date, message-id} for
 *  purposes of indexing and ease of query
 * Ties setting of these cache fields to setting of headers
 * Automatically sanitizes headers when set as array
 * Does NOT automatically sanitize headers when set as raw string
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Record.php
 */
class Email extends Record {
    /** @var string Table name, un-prefixed */
    protected static $table = G_DB_TABL.'Emails';
    /** @var string Primary Key */
    protected static $pkey = 'email_id';
    /** @var string Select query, without WHERE clause */
    protected static $query = '';
    /** @var array Table definition as collection of fields */
    protected static $vars = [
        'email_id'      => ['type' => 'i', 'min' => 1, 'guard' => true],
        'recordChanged' => ['type' => 'dt', 'min' => 0, 'guard' => true],
        'created_uts'   => ['type' => 'ts', 'min' => 1, 'guard' => true],
        'headerRaw'     => ['type' => 's', 'max' => 65535],
        'bodyRaw'       => ['type' => 's', 'max' => 65535],
        'to'            => ['type' => 's', 'max' => 65535],
        'from'          => ['type' => 's', 'max' => 65535],
        'subject'       => ['type' => 's', 'max' => 65535],
        'date'          => ['type' => 'dt', 'format' => 'r'],
        'messageId'     => ['type' => 's', 'max' => 155],
    ];

    /** @var array Dictionary of fields which are cached from the headerRaw */
    protected static $cacheFields = [
        'To'         => 'to',
        'From'       => 'from',
        'Subject'    => 'subject',
        'Date'       => 'date',
        'Message-ID' => 'messageId',
    ];

    /**
     * Parses the raw email headers into an array
     *
     * @param string $headerRaw the raw email headers
     *
     * @return array
     */
    public static function parseHeaders($headerRaw) {
        $headers = array();
        // Un-wrap lines
        $tmp = str_replace(array("\r\n ", "\r ", "\n ", "\r\n\t", "\r\t", "\n\t"), ' ', $headerRaw);
        // Convert all line breaks to single newlines for exploding
        $tmp = str_replace(array("\r\n", "\r"), "\n", $tmp);
        // The actual parsing
        foreach (explode("\n", $tmp) as $line) {
            $key = substr($line, 0, strpos($line, ":"));
            if ($key != '' && !isset($headers[$key])) {
                $headers[$key] = trim(substr($line, strpos($line, ":") + 1));
            }
        }
        // Prevent keyless entry
        unset($headers['']);
        unset($headers[null]);

        return $headers;
    }

    /**
     * Converts an associative array of header information to a usable string
     *
     * @param array $headers The array of headers
     *
     * @return string
     */
    public static function unparseHeaders($headers) {
        $headerRaw = '';
        foreach ($headers as $key => $val) {
            $headerRaw = $key.': '.$val."\n";
        }
        $headerRaw = wordwrap($headerRaw, 78, "\n ");

        return $headerRaw;
    }

    /**
     * Cleans up email headers, ensuring
     *  - all email fields are valid,
     *  - a Subject header is set,
     *  - a Date header is set
     *
     * @param array|string $headers Email headers to clean
     *
     * @return array|string
     */
    public static function sanitizeHeaders($headers) {
        if (is_string($headers)) {
            $headers = self::parseHeaders($headers);
            $toString = true;
        }

        // sanitize recipient lists
        if (isset($headers['Sender'])) {
            $headers['Sender'] = array_shift(explode(',', self::email_unique($headers['Sender'], true)));
        }
        if (isset($headers['From'])) {
            $headers['From'] = array_shift(explode(',', self::email_unique($headers['From'], true)));
        }
        if (isset($headers['Resent-From'])) {
            $headers['Resent-From'] = array_shift(explode(',', self::email_unique($headers['Resent-From'], true)));
        }
        if (isset($headers['Reply-To'])) {
            $headers['Reply-To'] = self::email_unique($headers['Reply-To'], true);
        }
        if (isset($headers['Resent-To'])) {
            $headers['Resent-To'] = self::email_unique($headers['Resent-To'], true);
        }
        if (isset($headers['To'])) {
            $headers['To'] = self::email_unique($headers['To'], true);
        }
        if (isset($headers['Resent-Cc'])) {
            $headers['Resent-Cc'] = self::email_unique($headers['Resent-Cc'], true);
        }
        if (isset($headers['Cc'])) {
            $headers['Cc'] = self::email_unique($headers['Cc'], true);
        }
        if (isset($headers['Resent-Bcc'])) {
            $headers['Resent-Bcc'] = self::email_unique($headers['Resent-Bcc'], true);
        }
        if (isset($headers['Bcc'])) {
            $headers['Bcc'] = self::email_unique($headers['Bcc'], true);
        }
        if (isset($headers['Subject'])) {
            $headers['Subject'] = str_replace(array("\r", "\n"), '', $headers['Subject']);
        } else {
            // ensure Subject header
            $headers['Subject'] = '';
        }
        if (isset($headers['Date'])) {
            $headers['Date'] = date(self::$vars['Date']['format'], strtotime($headers['Date']));
        } else {
            // ensure Date header
            $headers['Date'] = date(self::$vars['Date']['format']);
        }

        // If we came in as a string, go out as a string
        if (isset($toString)) {
            $headers = self::unparseHeaders($headers);
        }

        return $headers;
    }

    /**
     * Cleans a list of emails
     *
     * @param string $emails   The list of email addresses
     * @param bool   $validate Whether to validate each address
     *
     * @return string a string of unique email addresses
     */
    public static function email_unique($emails, $validate = false) {
        // normalize delimiter
        $emails = str_replace(array(';', "\r", "\n", ',,'), ',', $emails);
        // to array for further processing
        $emails = explode(',', $emails);
        // trim all addresses to remove edge whitespace
        $emails = array_map('trim', $emails);
        // remove duplicates to prevent double sending
        $emails = array_unique($emails);
        // validate each address
        if ($validate) {
            foreach ($emails as $key => $val) {
                if (false === $emails[$key] = filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    unset($emails[$key]);
                }
            }
        }
        // back to a comma delimited string
        $emails = implode(',', $emails);
        // remove blanks
        $emails = str_replace(',,', ',', $emails);
        // trim edge commas
        $emails = trim($emails, ',');

        return $emails;
    }

    /**
     * Set specified header
     *
     * @param string $key   Header to set
     * @param string $val   Value to set header to
     * @param bool   $cache Whether to set cache field, if applicable
     *
     * @return void
     */
    public function setHeader($key, $val, $cache = true) {
        $headers = self::parseHeaders($this->_s('headerRaw'));

        if ($cache && in_array($key, array_keys(static::$cacheFields))) {
            $func = '_'.self::$vars[$key]['type'];
            $val  = $this->$func($key, $val);
        }

        $headers[$key] = $val;
        $this->_s('headerRaw', self::unparseHeaders($headers));
    }

    /**
     * Returns the parsed object of email headers
     *
     * @return object
     */
    public function headerObject() {
        return imap_rfc822_parse_headers($this->_s('headerRaw'));
    }

    /**
     * Sets up an array of the outgoing email headers
     *
     * ~param array $args[0] Array of email headers
     *
     * @return array An array of email headers
     */
    public function headers() {
        if (count($args = func_get_args())) {
            $this->headerRaw($args[0]);
        }

        return self::parseHeaders($this->_s('headerRaw'));
    }

    /**
     * Wrap setting of raw headers to copy cached headers
     *
     * ~param string $args[0] Raw email headers
     *
     * @return string Raw email headers
     */
    public function headerRaw() {
        if (count($args = func_get_args())) {
            if (is_array($args[0])) {
                $headers = $args[0];
                $headerRaw = self::unparseHeaders($headers);
            } else {
                $headerRaw = $args[0];
                $headers = self::parseHeaders($headerRaw);
            }

            // If any of our cached fields are included, set them
            foreach (static::$cacheFields as $key) {
                if (isset($headers[$key])) {
                    $this->$key($headers[$key]);
                }
            }

            return $this->_s('headerRaw', $headerRaw);
        }

        return $this->_s('headerRaw');
    }

    /**
     * Wrapper for setting the 'To' header which also adjusts headerRaw
     *
     * ~param string $args[0] Raw email headers
     *
     * @return mixed
     */
    public function to() {
        if (count($args = func_get_args())) {
            $val = self::email_unique($args[0], true);
            $val = $this->_s(__FUNCTION__, $val);
            $this->setHeader(__FUNCTION__, $val, false);

            return $val;
        }

        return $this->_s(__FUNCTION__);
    }

    /**
     * Wrapper for setting the 'From' header which also adjusts headerRaw
     *
     * ~param string $args[0] Raw email headers
     *
     * @return mixed
     */
    public function from() {
        if (count($args = func_get_args())) {
            $val = array_shift(explode(',', self::email_unique($args[0], true)));
            $val = $this->_s(__FUNCTION__, $val);
            $this->setHeader(__FUNCTION__, $val, false);

            return $val;
        }

        return $this->_s(__FUNCTION__);
    }

    /**
     * Wrapper for setting the 'Subject' header which also adjusts headerRaw
     *
     * ~param string $args[0] Raw email headers
     *
     * @return mixed
     */
    public function subject() {
        if (count($args = func_get_args())) {
            $val = str_replace(array("\r", "\n"), '', $args[0]);
            $val = $this->_s(__FUNCTION__, $val);
            $this->setHeader(__FUNCTION__, $val, false);

            return $val;
        }

        return $this->_s(__FUNCTION__);
    }

    /**
     * Wrapper for setting the 'Date' header which also adjusts headerRaw
     *
     * ~param string $args[0] Raw email headers
     *
     * @return mixed
     */
    public function date() {
        if (count($args = func_get_args())) {
            $val = date(self::$vars[__FUNCTION__]['format'], strtotime($args[0]));
            $val = $this->_dt(__FUNCTION__, $val);
            $this->setHeader(__FUNCTION__, $val, false);

            return $val;
        }

        return $this->_dt(__FUNCTION__);
    }
}
