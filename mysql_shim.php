<?php
// PHP 7/8 Compatibility Shim for legacy mysql_* functions using mysqli
if (!function_exists('mysql_connect')) {
    global $__mysql_shim_link;
    $__mysql_shim_link = null;

    function mysql_connect($host = '', $username = '', $password = '', $new_link = false, $client_flags = 0) {
        global $__mysql_shim_link;
        $port = null;
        $socket = null;
        if (strpos($host, ':') !== false) {
            list($host, $port_or_socket) = explode(':', $host, 2);
            if (is_numeric($port_or_socket)) {
                $port = (int)$port_or_socket;
            } else {
                $socket = $port_or_socket;
            }
        }
        $link = mysqli_connect($host, $username, $password, '', $port, $socket);
        if ($link) {
            $__mysql_shim_link = $link;
        }
        return $link;
    }

    function mysql_select_db($database_name, $link_identifier = null) {
        global $__mysql_shim_link;
        $link = $link_identifier ?: $__mysql_shim_link;
        if (!$link) return false;
        return mysqli_select_db($link, $database_name);
    }

    function mysql_query($query, $link_identifier = null) {
        global $__mysql_shim_link;
        $link = $link_identifier ?: $__mysql_shim_link;
        if (!$link) return false;
        return mysqli_query($link, $query);
    }

    function mysql_fetch_array($result, $result_type = MYSQLI_BOTH) {
        if (!$result || !($result instanceof mysqli_result)) return false;
        return mysqli_fetch_array($result, $result_type);
    }

    function mysql_fetch_assoc($result) {
        if (!$result || !($result instanceof mysqli_result)) return false;
        return mysqli_fetch_assoc($result);
    }

    function mysql_fetch_object($result, $class_name = null, array $params = null) {
        if (!$result || !($result instanceof mysqli_result)) return false;
        if ($class_name) {
            return mysqli_fetch_object($result, $class_name, $params);
        }
        return mysqli_fetch_object($result);
    }

    function mysql_num_rows($result) {
        if (!$result || !($result instanceof mysqli_result)) return 0;
        return mysqli_num_rows($result);
    }

    function mysql_error($link_identifier = null) {
        global $__mysql_shim_link;
        $link = $link_identifier ?: $__mysql_shim_link;
        if (!$link) return '';
        return mysqli_error($link);
    }

    function mysql_real_escape_string($unescaped_string, $link_identifier = null) {
        global $__mysql_shim_link;
        $link = $link_identifier ?: $__mysql_shim_link;
        if (!$link) {
            return addslashes($unescaped_string);
        }
        return mysqli_real_escape_string($link, $unescaped_string);
    }

    function mysql_close($link_identifier = null) {
        global $__mysql_shim_link;
        $link = $link_identifier ?: $__mysql_shim_link;
        if (!$link) return false;
        return mysqli_close($link);
    }
}
