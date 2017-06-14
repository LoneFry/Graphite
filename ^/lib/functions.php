<?php
/**
 * Useful functions to use
 *
 * PHP version 5.6
 *
 * @category Graphite
 * @package  Graphite
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * Fetch raw HTTP request headers
 * Cache the result for successive calls to avoid manifest issues with successive calls.
 *
 * @return string Full representation of HTTP request headers
 */
function php_getRawInputHeader() {
    static $output = '';
    if ('' == $output && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $k => $v) {
            $output .= $k . ': ' . $v . "\n";
        }
    }

    return $output;
}

/**
 * Fetch raw HTTP request body
 * Cache the result for successive calls to avoid manifest issues with successive calls.
 *
 * @return string Full representation of HTTP request body
 */
function php_getRawInputBody() {
    static $output = '';
    if ('' == $output) {
        $output = file_get_contents('php://input', null, null, 0);
    }

    return $output;
}

/**
 * Fetch raw HTTP request
 *
 * @return string Full representation of HTTP request headers and body
 */
function php_getRawInput() {
    return php_getRawInputHeader() ."\n". php_getRawInputBody();
}

/**
 * Updates a variable in a url
 *
 * @param string $url      URL to add the variable to
 * @param string $variable Variable in the query string to alter
 * @param mixed  $value    Value to set the query string to
 *
 * @return string
 */
function updateQueryString($url, $variable, $value) {
    $baseUrl = $url;
    $query = array();

    if (strpos($url, '?') !== false) {
        $parts = explode('?', $url);
        $baseUrl = reset($parts);
        $queryString = end($parts);

        parse_str($queryString, $query);
    }

    $query[$variable] = $value;

    return $baseUrl . '?' . http_build_query($query);
}

/**
 * A shorthand for the frequent
 *   `isset($var) ? $var : '';`
 * statements that salt our codebase.  Used as
 *   `ifset($var)`
 *
 * @param mixed $test    Value to test and return if set.
 * @param mixed $default Value to return if $test is empty
 *
 * @return mixed
 */
function ifset(&$test, $default = null) {
    return isset($test) ? $test : $default;
}
