<?php
// Minimal auth helper
function get_bearer_token()
{
    $headers = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('getallheaders')) {
        $all = getallheaders();
        if (isset($all['Authorization'])) $headers = trim($all['Authorization']);
    }
    if (!$headers) return null;
    if (preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
        return $matches[1];
    }
    return null;
}
