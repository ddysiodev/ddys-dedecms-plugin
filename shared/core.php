<?php
if (!defined('DEDEINC')) {
    exit('DedeCMS Error: Request Error!');
}

if (!defined('DDYS_OPEN_VERSION')) {
    define('DDYS_OPEN_VERSION', '0.1.1');
}
if (!defined('DDYS_OPEN_API_DEFAULT')) {
    define('DDYS_OPEN_API_DEFAULT', 'https://ddys.io/api/v1');
}
if (!defined('DDYS_OPEN_SITE_DEFAULT')) {
    define('DDYS_OPEN_SITE_DEFAULT', 'https://ddys.io');
}

function ddys_open_is_biz()
{
    $inc = defined('DEDEINC') ? str_replace('\\', '/', DEDEINC) : '';
    return preg_match('#/system$#i', $inc) ? true : false;
}

function ddys_open_site_root()
{
    global $cfg_cmspath;
    $root = isset($cfg_cmspath) ? trim((string)$cfg_cmspath) : '';
    if ($root === '' || $root === '/') {
        return '/';
    }
    return rtrim($root, '/') . '/';
}

function ddys_open_runtime_dir()
{
    return DEDEDATA . '/ddys_open';
}

function ddys_open_cache_dir()
{
    return ddys_open_runtime_dir() . '/cache';
}

function ddys_open_rate_dir()
{
    return ddys_open_runtime_dir() . '/rate';
}

function ddys_open_config_file()
{
    return ddys_open_runtime_dir() . '/config.php';
}

function ddys_open_ensure_dir($dir)
{
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (is_dir($dir)) {
        @chmod($dir, 0755);
    }
}

function ddys_open_bootstrap_runtime()
{
    ddys_open_ensure_dir(ddys_open_runtime_dir());
    ddys_open_ensure_dir(ddys_open_cache_dir());
    ddys_open_ensure_dir(ddys_open_rate_dir());
    $index = ddys_open_runtime_dir() . '/index.html';
    if (!is_file($index)) {
        @file_put_contents($index, '');
    }
    $webConfig = ddys_open_runtime_dir() . '/web.config';
    if (!is_file($webConfig)) {
        @file_put_contents($webConfig, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><handlers><add name=\"deny\" path=\"*\" verb=\"*\" modules=\"IsapiModule\" scriptProcessor=\"\" resourceType=\"Unspecified\" requireAccess=\"None\" /></handlers></system.webServer></configuration>\n");
    }
}

function ddys_open_defaults()
{
    return array(
        'api_base_url' => DDYS_OPEN_API_DEFAULT,
        'site_base_url' => DDYS_OPEN_SITE_DEFAULT,
        'api_key' => '',
        'timeout' => 12,
        'default_cache_ttl' => 300,
        'dictionary_cache_ttl' => 86400,
        'fresh_cache_ttl' => 300,
        'list_cache_ttl' => 600,
        'detail_cache_ttl' => 1800,
        'community_cache_ttl' => 120,
        'theme' => 'auto',
        'layout' => 'grid',
        'columns' => 4,
        'target' => '_blank',
        'show_source_link' => 1,
        'enable_styles' => 1,
        'enable_request_form' => 0,
        'request_interval' => 60,
        'show_nav' => 1,
        'enable_pretty_urls' => 0,
        'pretty_base_path' => 'ddys',
        'nonce_salt' => '',
        'debug' => 0,
    );
}

function ddys_open_storage_config()
{
    $file = ddys_open_config_file();
    if (is_file($file)) {
        $data = include $file;
        if (is_array($data)) {
            return $data;
        }
    }
    return array();
}

function ddys_open_storage_save_config($settings)
{
    ddys_open_bootstrap_runtime();
    $settings = ddys_open_normalize_settings($settings);
    $content = "<?php\nreturn " . var_export($settings, true) . ";\n";
    return @file_put_contents(ddys_open_config_file(), $content) !== false;
}

function ddys_open_settings()
{
    static $settings = null;
    if (is_array($settings)) {
        return $settings;
    }
    ddys_open_bootstrap_runtime();
    $settings = ddys_open_normalize_settings(array_merge(ddys_open_defaults(), ddys_open_storage_config()));
    if ($settings['nonce_salt'] === '') {
        $settings['nonce_salt'] = ddys_open_random_token();
        ddys_open_storage_save_config($settings);
    }
    return $settings;
}

function ddys_open_normalize_settings($settings)
{
    $merged = array_merge(ddys_open_defaults(), is_array($settings) ? $settings : array());
    $merged['api_base_url'] = ddys_open_normalize_base_url($merged['api_base_url'], DDYS_OPEN_API_DEFAULT);
    $merged['site_base_url'] = ddys_open_normalize_base_url($merged['site_base_url'], DDYS_OPEN_SITE_DEFAULT);
    $merged['timeout'] = ddys_open_int_range($merged['timeout'], 12, 1, 60);
    $merged['default_cache_ttl'] = ddys_open_int_range($merged['default_cache_ttl'], 300, 0, 604800);
    $merged['dictionary_cache_ttl'] = ddys_open_int_range($merged['dictionary_cache_ttl'], 86400, 0, 604800);
    $merged['fresh_cache_ttl'] = ddys_open_int_range($merged['fresh_cache_ttl'], 300, 0, 604800);
    $merged['list_cache_ttl'] = ddys_open_int_range($merged['list_cache_ttl'], 600, 0, 604800);
    $merged['detail_cache_ttl'] = ddys_open_int_range($merged['detail_cache_ttl'], 1800, 0, 604800);
    $merged['community_cache_ttl'] = ddys_open_int_range($merged['community_cache_ttl'], 120, 0, 604800);
    $merged['columns'] = ddys_open_int_range($merged['columns'], 4, 1, 6);
    $merged['request_interval'] = ddys_open_int_range($merged['request_interval'], 60, 10, 3600);
    $merged['theme'] = ddys_open_choice($merged['theme'], array('auto', 'light', 'dark'), 'auto');
    $merged['layout'] = ddys_open_choice($merged['layout'], array('grid', 'list', 'compact'), 'grid');
    $merged['target'] = ddys_open_choice($merged['target'], array('_blank', '_self'), '_blank');
    $merged['pretty_base_path'] = ddys_open_normalize_base_path($merged['pretty_base_path'], 'ddys');
    foreach (array('show_source_link', 'enable_styles', 'enable_request_form', 'show_nav', 'enable_pretty_urls', 'debug') as $key) {
        $merged[$key] = ddys_open_bool($merged[$key]) ? 1 : 0;
    }
    $merged['api_key'] = trim(ddys_open_scalar($merged['api_key']));
    $merged['nonce_salt'] = trim(ddys_open_scalar($merged['nonce_salt']));
    return $merged;
}

function ddys_open_save_settings_from_post()
{
    $current = ddys_open_settings();
    $next = array_merge($current, array(
        'api_base_url' => ddys_open_post('api_base_url', DDYS_OPEN_API_DEFAULT),
        'site_base_url' => ddys_open_post('site_base_url', DDYS_OPEN_SITE_DEFAULT),
        'api_key' => ddys_open_post('api_key', ''),
        'timeout' => ddys_open_post('timeout', 12),
        'default_cache_ttl' => ddys_open_post('default_cache_ttl', 300),
        'dictionary_cache_ttl' => ddys_open_post('dictionary_cache_ttl', 86400),
        'fresh_cache_ttl' => ddys_open_post('fresh_cache_ttl', 300),
        'list_cache_ttl' => ddys_open_post('list_cache_ttl', 600),
        'detail_cache_ttl' => ddys_open_post('detail_cache_ttl', 1800),
        'community_cache_ttl' => ddys_open_post('community_cache_ttl', 120),
        'theme' => ddys_open_post('theme', 'auto'),
        'layout' => ddys_open_post('layout', 'grid'),
        'columns' => ddys_open_post('columns', 4),
        'target' => ddys_open_post('target', '_blank'),
        'show_source_link' => ddys_open_post('show_source_link', '0'),
        'enable_styles' => ddys_open_post('enable_styles', '0'),
        'enable_request_form' => ddys_open_post('enable_request_form', '0'),
        'request_interval' => ddys_open_post('request_interval', 60),
        'show_nav' => ddys_open_post('show_nav', '0'),
        'enable_pretty_urls' => ddys_open_post('enable_pretty_urls', '0'),
        'pretty_base_path' => ddys_open_post('pretty_base_path', 'ddys'),
        'debug' => ddys_open_post('debug', '0'),
    ));
    return ddys_open_storage_save_config(array_intersect_key(ddys_open_normalize_settings($next), ddys_open_defaults()));
}

function ddys_open_get($key, $default = '')
{
    if (isset($_GET[$key])) {
        return ddys_open_scalar($_GET[$key], $default);
    }
    return $default;
}

function ddys_open_post($key, $default = '')
{
    if (isset($_POST[$key])) {
        return ddys_open_scalar($_POST[$key], $default);
    }
    return $default;
}

function ddys_open_scalar($value, $default = '')
{
    if (is_array($value) || is_object($value)) {
        return $default;
    }
    return trim(str_replace("\0", '', (string)$value));
}

function ddys_open_charset()
{
    global $cfg_soft_lang;
    $lang = isset($cfg_soft_lang) ? strtolower((string)$cfg_soft_lang) : 'utf-8';
    if ($lang === 'gb2312') {
        return 'gbk';
    }
    return $lang === '' ? 'utf-8' : $lang;
}

function ddys_open_html_charset()
{
    $charset = ddys_open_charset();
    if ($charset === 'utf-8') return 'UTF-8';
    if ($charset === 'big5') return 'BIG5';
    return 'GB2312';
}

function ddys_open_meta_charset()
{
    $charset = ddys_open_charset();
    if ($charset === 'utf-8') return 'utf-8';
    if ($charset === 'big5') return 'big5';
    return 'gb2312';
}

function ddys_open_is_utf8($value)
{
    return preg_match('//u', (string)$value) ? true : false;
}

function ddys_open_site_text($value)
{
    $value = (string)$value;
    $charset = ddys_open_charset();
    if ($charset === 'utf-8') {
        if (!ddys_open_is_utf8($value) && function_exists('gb2utf8')) {
            return gb2utf8($value);
        }
        return $value;
    }
    if (ddys_open_is_utf8($value)) {
        if (($charset === 'gbk' || $charset === 'gb2312') && function_exists('utf82gb')) {
            return utf82gb($value);
        }
        if ($charset === 'big5' && function_exists('gb2big5') && function_exists('utf82gb')) {
            return gb2big5(utf82gb($value));
        }
    }
    return $value;
}

function ddys_open_utf8_text($value)
{
    $value = (string)$value;
    if (ddys_open_is_utf8($value)) {
        return $value;
    }
    if (function_exists('gb2utf8')) {
        return gb2utf8($value);
    }
    return $value;
}

function ddys_open_json_safe($value)
{
    if (is_array($value)) {
        $out = array();
        foreach ($value as $key => $item) {
            $out[$key] = ddys_open_json_safe($item);
        }
        return $out;
    }
    if (is_string($value)) {
        return ddys_open_utf8_text($value);
    }
    return $value;
}

function ddys_open_h($value)
{
    return htmlspecialchars(ddys_open_site_text($value), ENT_QUOTES, ddys_open_html_charset());
}

function ddys_open_attr($value)
{
    return ddys_open_h($value);
}

function ddys_open_substr($value, $start, $length)
{
    $value = (string)$value;
    if (function_exists('mb_substr')) {
        return mb_substr($value, $start, $length, 'UTF-8');
    }
    return substr($value, $start, $length);
}

function ddys_open_bool($value)
{
    if (is_bool($value)) {
        return $value;
    }
    return in_array(strtolower(ddys_open_scalar($value)), array('1', 'true', 'yes', 'on'), true);
}

function ddys_open_int_range($value, $fallback, $min, $max)
{
    if (is_numeric($value)) {
        $value = (int)$value;
        if ($value < $min) {
            return (int)$min;
        }
        if ($value > $max) {
            return (int)$max;
        }
        return $value;
    }
    return (int)$fallback;
}

function ddys_open_choice($value, $allowed, $fallback)
{
    $value = strtolower(ddys_open_scalar($value));
    return in_array($value, $allowed, true) ? $value : $fallback;
}

function ddys_open_normalize_base_url($value, $fallback)
{
    $value = ddys_open_scalar($value);
    if ($value === '' || !preg_match('#^https?://#i', $value)) {
        return $fallback;
    }
    $parts = parse_url($value);
    if (empty($parts['scheme']) || empty($parts['host']) || !empty($parts['user']) || !empty($parts['pass'])) {
        return $fallback;
    }
    return rtrim($value, '/');
}

function ddys_open_normalize_base_path($value, $fallback)
{
    $value = trim(ddys_open_scalar($value), "/ \t\r\n");
    if ($value === '' || strpos($value, '..') !== false || !preg_match('#^[a-zA-Z0-9_\-/]+$#', $value)) {
        return $fallback;
    }
    return $value;
}

function ddys_open_safe_media_url($value)
{
    $value = ddys_open_scalar($value);
    return preg_match('#^https?://#i', $value) ? $value : '';
}

function ddys_open_public_url($kind)
{
    $root = ddys_open_site_root();
    if (ddys_open_is_biz()) {
        if ($kind === 'page') return $root . 'apps/ddys.php';
        if ($kind === 'api') return $root . 'apps/ddys_api.php';
        if ($kind === 'request') return $root . 'apps/ddys_request.php';
        if ($kind === 'static') return $root . 'static/ddys_open';
    }
    if ($kind === 'page') return $root . 'plus/ddys.php';
    if ($kind === 'api') return $root . 'plus/ddys_api.php';
    if ($kind === 'request') return $root . 'plus/ddys_request.php';
    if ($kind === 'static') return $root . 'plus/ddys_open_static';
    return $root;
}

function ddys_open_append_query($url, $params)
{
    $query = ddys_open_clean_query($params);
    if (empty($query)) {
        return $url;
    }
    return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($query, '', '&');
}

function ddys_open_clean_query($params)
{
    $out = array();
    foreach ($params as $key => $value) {
        $value = ddys_open_scalar($value);
        if ($value !== '') {
            $out[$key] = $value;
        }
    }
    return $out;
}

function ddys_open_page_url($view = 'latest', $params = array())
{
    $settings = ddys_open_settings();
    $view = ddys_open_choice($view, ddys_open_views(), 'latest');
    if (!empty($settings['enable_pretty_urls'])) {
        $base = ddys_open_site_root() . $settings['pretty_base_path'];
        if ($view === 'latest') {
            return ddys_open_append_query($base . '/', $params);
        }
        if (in_array($view, array('movie', 'sources', 'related', 'comments'), true)) {
            $slug = isset($params['slug']) ? rawurlencode(ddys_open_scalar($params['slug'])) : '';
            unset($params['slug']);
            $suffix = $view === 'movie' ? '' : '/' . $view;
            return ddys_open_append_query($slug === '' ? $base . '/' : $base . '/movie/' . $slug . $suffix, $params);
        }
        if ($view === 'collection') {
            $slug = isset($params['slug']) ? rawurlencode(ddys_open_scalar($params['slug'])) : '';
            unset($params['slug']);
            return ddys_open_append_query($slug === '' ? $base . '/collection' : $base . '/collection/' . $slug, $params);
        }
        if ($view === 'share') {
            $id = isset($params['id']) ? (int)$params['id'] : 0;
            unset($params['id']);
            return ddys_open_append_query($id <= 0 ? $base . '/share' : $base . '/share/' . $id, $params);
        }
        if ($view === 'user') {
            $username = isset($params['username']) ? rawurlencode(ddys_open_scalar($params['username'])) : '';
            unset($params['username']);
            return ddys_open_append_query($username === '' ? $base . '/user' : $base . '/user/' . $username, $params);
        }
        return ddys_open_append_query($base . '/' . rawurlencode($view), $params);
    }
    $query = $view === 'latest' ? $params : array_merge(array('view' => $view), $params);
    return ddys_open_append_query(ddys_open_public_url('page'), $query);
}

function ddys_open_endpoint_url($kind)
{
    $settings = ddys_open_settings();
    if (!empty($settings['enable_pretty_urls'])) {
        $base = ddys_open_site_root() . $settings['pretty_base_path'];
        if ($kind === 'api') return $base . '/api';
        if ($kind === 'request') return $base . '/request-submit';
    }
    return ddys_open_public_url($kind);
}

function ddys_open_random_token()
{
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(24));
    }
    return sha1(uniqid('', true) . mt_rand());
}

function ddys_open_nonce($action = 'default', $bucket = null)
{
    $settings = ddys_open_settings();
    if ($bucket === null) {
        $bucket = floor(time() / 43200);
    }
    return hash_hmac('sha256', $action . '|' . $bucket, $settings['nonce_salt']);
}

function ddys_open_check_nonce($token, $action = 'default')
{
    $token = ddys_open_scalar($token);
    if ($token === '') {
        return false;
    }
    $bucket = floor(time() / 43200);
    return ddys_open_hash_equals(ddys_open_nonce($action, $bucket), $token)
        || ddys_open_hash_equals(ddys_open_nonce($action, $bucket - 1), $token);
}

function ddys_open_hash_equals($known, $user)
{
    if (function_exists('hash_equals')) {
        return hash_equals($known, $user);
    }
    if (strlen($known) !== strlen($user)) {
        return false;
    }
    $result = 0;
    for ($i = 0; $i < strlen($known); $i++) {
        $result |= ord($known[$i]) ^ ord($user[$i]);
    }
    return $result === 0;
}

function ddys_open_current_ip()
{
    foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key) {
        if (!empty($_SERVER[$key])) {
            $parts = explode(',', (string)$_SERVER[$key]);
            $value = trim($parts[0]);
            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }
    }
    return 'unknown';
}

function ddys_open_cache_file($key)
{
    return ddys_open_cache_dir() . '/' . md5($key) . '.php';
}

function ddys_open_cache_get($key)
{
    $file = ddys_open_cache_file($key);
    if (!is_file($file)) {
        return false;
    }
    $data = include $file;
    if (!is_array($data) || empty($data['expires']) || $data['expires'] < time()) {
        @unlink($file);
        return false;
    }
    return isset($data['value']) ? $data['value'] : false;
}

function ddys_open_cache_set($key, $value, $ttl)
{
    $ttl = (int)$ttl;
    if ($ttl <= 0) {
        return false;
    }
    ddys_open_ensure_dir(ddys_open_cache_dir());
    $payload = array('expires' => time() + $ttl, 'value' => $value);
    return @file_put_contents(ddys_open_cache_file($key), "<?php\nreturn " . var_export($payload, true) . ";\n") !== false;
}

function ddys_open_cache_clear()
{
    $count = 0;
    foreach (glob(ddys_open_cache_dir() . '/*.php') as $file) {
        if (@unlink($file)) {
            $count++;
        }
    }
    return $count;
}

function ddys_open_rate_limit($scope, $identifier, $seconds)
{
    $seconds = (int)$seconds;
    if ($seconds <= 0) {
        return true;
    }
    ddys_open_ensure_dir(ddys_open_rate_dir());
    $file = ddys_open_rate_dir() . '/' . md5($scope . '|' . $identifier) . '.txt';
    if (is_file($file) && (int)@file_get_contents($file) > time()) {
        return false;
    }
    @file_put_contents($file, (string)(time() + $seconds));
    return true;
}

function ddys_open_error($message, $status = 0, $payload = array())
{
    return array('ddys_error' => true, 'success' => false, 'message' => (string)$message, 'status' => (int)$status, 'payload' => $payload);
}

function ddys_open_is_error($value)
{
    return is_array($value) && !empty($value['ddys_error']);
}

function ddys_open_build_query($source, $keys)
{
    $out = array();
    foreach ($keys as $key) {
        if (isset($source[$key])) {
            $value = ddys_open_scalar($source[$key]);
            if ($value !== '') {
                $out[$key] = ddys_open_normalize_query_value($key, $value);
            }
        }
    }
    return $out;
}

function ddys_open_normalize_query_value($key, $value)
{
    $value = ddys_open_scalar($value);
    if ($value === '') return '';
    if ($key === 'limit' || $key === 'per_page') return ddys_open_int_range($value, 12, 1, 50);
    if ($key === 'page') return ddys_open_int_range($value, 1, 1, 999);
    if ($key === 'year') return ddys_open_int_range($value, 0, 0, 2099);
    if ($key === 'month') return ddys_open_int_range($value, 0, 0, 12);
    return $value;
}

function ddys_open_ttl_for_path($path, $settings)
{
    if (preg_match('#^/(types|genres|regions|calendar)$#', $path)) return (int)$settings['dictionary_cache_ttl'];
    if (preg_match('#^/(latest|hot)$#', $path)) return (int)$settings['fresh_cache_ttl'];
    if (preg_match('#^/(movies/[^/]+|movies/[^/]+/sources|movies/[^/]+/related|collections/[^/]+|shares/[0-9]+)$#', $path)) return (int)$settings['detail_cache_ttl'];
    if (preg_match('#^/(movies/[^/]+/comments|suggest|shares|requests|activities|user/)#', $path)) return (int)$settings['community_cache_ttl'];
    if (preg_match('#^/(movies|search|collections)#', $path)) return (int)$settings['list_cache_ttl'];
    return (int)$settings['default_cache_ttl'];
}

function ddys_open_api_get($path, $params = array(), $options = array())
{
    return ddys_open_api_request('GET', $path, $params, null, $options);
}

function ddys_open_api_post($path, $body = array(), $options = array())
{
    return ddys_open_api_request('POST', $path, array(), $body, $options);
}

function ddys_open_api_request($method, $path, $params, $body, $options)
{
    $settings = ddys_open_settings();
    $method = strtoupper($method);
    $path = '/' . ltrim((string)$path, '/');
    $base = rtrim($settings['api_base_url'], '/');
    $params = ddys_open_clean_query($params);
    $url = $base . $path . (empty($params) ? '' : '?' . http_build_query($params, '', '&'));
    $useCache = $method === 'GET' && empty($options['no_cache']);
    $cacheKey = $method . '|' . $base . '|' . $path . '|' . serialize($params);
    if ($useCache) {
        $cached = ddys_open_cache_get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
    }
    $headers = array('Accept: application/json', 'User-Agent: ddys-dedecms-plugin/' . DDYS_OPEN_VERSION);
    if (!empty($options['auth'])) {
        if ($settings['api_key'] === '') {
            return ddys_open_error('低端影视 API Key 尚未配置。', 403);
        }
        $headers[] = 'Authorization: Bearer ' . $settings['api_key'];
    }
    $raw = ddys_open_http_request($method, $url, $body, $headers, $settings['timeout']);
    if (ddys_open_is_error($raw)) {
        return $raw;
    }
    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return ddys_open_error('低端影视 API 返回了无效 JSON。', 0, array('raw' => $raw));
    }
    if (isset($json['success']) && $json['success'] === false) {
        return ddys_open_error(isset($json['message']) ? $json['message'] : '低端影视 API 请求失败。', 0, $json);
    }
    if ($useCache) {
        $ttl = isset($options['cache_ttl']) ? (int)$options['cache_ttl'] : ddys_open_ttl_for_path($path, $settings);
        ddys_open_cache_set($cacheKey, $json, $ttl);
    }
    return $json;
}

function ddys_open_http_request($method, $url, $body, $headers, $timeout)
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int)$timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(ddys_open_json_safe($body)));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false) {
            return ddys_open_error('低端影视 API 网络请求失败：' . $err, $status);
        }
        if ($status >= 400) {
            return ddys_open_error('低端影视 API HTTP ' . $status . '。', $status, array('raw' => $raw));
        }
        return $raw;
    }
    $opts = array('http' => array('method' => $method, 'timeout' => (int)$timeout, 'header' => implode("\r\n", $headers), 'ignore_errors' => true));
    if ($body !== null) {
        $opts['http']['header'] .= "\r\nContent-Type: application/json";
        $opts['http']['content'] = json_encode(ddys_open_json_safe($body));
    }
    $raw = @file_get_contents($url, false, stream_context_create($opts));
    if ($raw === false) {
        return ddys_open_error('当前 PHP 环境无法请求低端影视 API。', 0);
    }
    $status = ddys_open_stream_status_code(isset($http_response_header) ? $http_response_header : array());
    if ($status >= 400) {
        return ddys_open_error('低端影视 API HTTP ' . $status . '。', $status, array('raw' => $raw));
    }
    return $raw;
}

function ddys_open_stream_status_code($headers)
{
    if (!is_array($headers) || empty($headers[0])) return 0;
    return preg_match('#\s([0-9]{3})\s#', $headers[0], $matches) ? (int)$matches[1] : 0;
}

function ddys_open_api_data($path, $params = array(), $options = array())
{
    $payload = ddys_open_api_get($path, $params, $options);
    if (ddys_open_is_error($payload)) return $payload;
    return isset($payload['data']) ? $payload['data'] : $payload;
}

function ddys_open_api_paginated($path, $params = array(), $options = array())
{
    $payload = ddys_open_api_get($path, $params, $options);
    if (ddys_open_is_error($payload)) return $payload;
    return $payload;
}

function ddys_open_views()
{
    return array('latest', 'movies', 'hot', 'search', 'calendar', 'movie', 'sources', 'related', 'comments', 'collections', 'collection', 'shares', 'share', 'requests', 'activities', 'user', 'types', 'genres', 'regions', 'request_form');
}

function ddys_open_required_arg($args, $key, $label)
{
    $value = ddys_open_scalar(isset($args[$key]) ? $args[$key] : '');
    if ($value === '') {
        return ddys_open_error('缺少 ' . $label . ' 参数。', 400);
    }
    return $value;
}

function ddys_open_render($type, $args = array())
{
    $type = ddys_open_choice($type, ddys_open_views(), 'latest');
    if ($type === 'movies') return ddys_open_render_list(ddys_open_api_paginated('/movies', ddys_open_build_query($args, array('type', 'genre', 'region', 'year', 'sort', 'page', 'per_page'))), $args);
    if ($type === 'latest') return ddys_open_render_list(ddys_open_api_data('/latest', ddys_open_build_query($args, array('type', 'limit'))), $args);
    if ($type === 'hot') return ddys_open_render_list(ddys_open_api_data('/hot', ddys_open_build_query($args, array('type', 'genre', 'region', 'limit'))), $args);
    if ($type === 'search') return ddys_open_render_search($args);
    if ($type === 'calendar') return ddys_open_render_calendar(ddys_open_api_data('/calendar', ddys_open_build_query($args, array('year', 'month'))), $args);
    if ($type === 'movie' || $type === 'sources' || $type === 'related' || $type === 'comments') {
        $slug = ddys_open_required_arg($args, 'slug', 'slug');
        if (ddys_open_is_error($slug)) return ddys_open_render_error($slug, $args);
        if ($type === 'movie') return ddys_open_render_detail(ddys_open_api_data('/movies/' . rawurlencode($slug)), $args);
        if ($type === 'sources') return ddys_open_render_sources(ddys_open_api_data('/movies/' . rawurlencode($slug) . '/sources'), $args);
        if ($type === 'related') return ddys_open_render_list(ddys_open_api_data('/movies/' . rawurlencode($slug) . '/related'), $args);
        return ddys_open_render_list(ddys_open_api_paginated('/movies/' . rawurlencode($slug) . '/comments', ddys_open_build_query($args, array('page', 'per_page'))), $args);
    }
    if ($type === 'collections') return ddys_open_render_list(ddys_open_api_paginated('/collections', ddys_open_build_query($args, array('page', 'per_page'))), $args);
    if ($type === 'collection') {
        $slug = ddys_open_required_arg($args, 'slug', 'slug');
        if (ddys_open_is_error($slug)) return ddys_open_render_error($slug, $args);
        return ddys_open_render_detail(ddys_open_api_get('/collections/' . rawurlencode($slug), ddys_open_build_query($args, array('page', 'per_page'))), $args);
    }
    if ($type === 'shares') return ddys_open_render_list(ddys_open_api_paginated('/shares', ddys_open_build_query($args, array('page', 'per_page'))), $args);
    if ($type === 'share') {
        $id = (int)(isset($args['id']) ? $args['id'] : 0);
        if ($id <= 0) return ddys_open_render_error(ddys_open_error('缺少 id 参数。', 400), $args);
        return ddys_open_render_detail(ddys_open_api_data('/shares/' . $id), $args);
    }
    if ($type === 'requests') return ddys_open_render_list(ddys_open_api_paginated('/requests', ddys_open_build_query($args, array('page', 'per_page'))), $args);
    if ($type === 'activities') return ddys_open_render_list(ddys_open_api_paginated('/activities', ddys_open_build_query($args, array('type', 'page', 'per_page'))), $args);
    if ($type === 'user') {
        $username = ddys_open_required_arg($args, 'username', 'username');
        if (ddys_open_is_error($username)) return ddys_open_render_error($username, $args);
        return ddys_open_render_detail(ddys_open_api_data('/user/' . rawurlencode($username)), $args);
    }
    if ($type === 'types') return ddys_open_render_dictionary(ddys_open_api_data('/types'), $args);
    if ($type === 'genres') return ddys_open_render_dictionary(ddys_open_api_data('/genres'), $args);
    if ($type === 'regions') return ddys_open_render_dictionary(ddys_open_api_data('/regions'), $args);
    if ($type === 'request_form') return ddys_open_render_request_form($args);
    return '';
}

function ddys_open_payload_data($payload)
{
    if (is_array($payload) && array_key_exists('data', $payload)) return $payload['data'];
    return $payload;
}

function ddys_open_to_list($data)
{
    if (!is_array($data)) return array();
    if (isset($data['data']) && is_array($data['data'])) return $data['data'];
    foreach (array('items', 'movies', 'results', 'related', 'series', 'shares', 'requests', 'activities', 'comments') as $key) {
        if (isset($data[$key]) && is_array($data[$key])) return $data[$key];
    }
    if (ddys_open_is_assoc($data)) return array($data);
    return $data;
}

function ddys_open_is_assoc($array)
{
    return is_array($array) && !empty($array) && array_keys($array) !== range(0, count($array) - 1);
}

function ddys_open_item_value($item, $keys, $fallback = '')
{
    foreach ($keys as $key) {
        if (isset($item[$key]) && $item[$key] !== '') {
            return is_array($item[$key]) ? implode(', ', $item[$key]) : $item[$key];
        }
    }
    return $fallback;
}

function ddys_open_site_url($item)
{
    $settings = ddys_open_settings();
    if (isset($item['url']) && preg_match('#^https?://#i', $item['url'])) return $item['url'];
    if (isset($item['url']) && substr($item['url'], 0, 1) === '/') return rtrim($settings['site_base_url'], '/') . $item['url'];
    if (isset($item['slug']) && $item['slug'] !== '') return rtrim($settings['site_base_url'], '/') . '/movie/' . rawurlencode($item['slug']);
    return '';
}

function ddys_open_wrap($html, $args = array())
{
    $settings = ddys_open_settings();
    $layout = ddys_open_choice(isset($args['layout']) ? $args['layout'] : $settings['layout'], array('grid', 'list', 'compact'), $settings['layout']);
    $theme = ddys_open_choice(isset($args['theme']) ? $args['theme'] : $settings['theme'], array('auto', 'light', 'dark'), $settings['theme']);
    $columns = ddys_open_int_range(isset($args['columns']) ? $args['columns'] : $settings['columns'], 4, 1, 6);
    return '<div class="ddys-dede ddys-dede-theme-' . ddys_open_attr($theme) . ' ddys-dede-layout-' . ddys_open_attr($layout) . '" style="--ddys-dede-columns:' . $columns . '">' . $html . '</div>';
}

function ddys_open_render_error($payload, $args = array())
{
    $message = is_array($payload) && isset($payload['message']) ? $payload['message'] : '低端影视内容加载失败。';
    return ddys_open_wrap('<div class="ddys-dede-error">' . ddys_open_h($message) . '</div>', $args);
}

function ddys_open_render_empty($message, $args = array())
{
    return ddys_open_wrap('<div class="ddys-dede-empty">' . ddys_open_h($message) . '</div>', $args);
}

function ddys_open_render_card($item, $settings)
{
    if (!is_array($item)) return '';
    $title = ddys_open_item_value($item, array('title', 'name', 'cn_name', 'en_name', 'username', 'search_keyword'), 'Untitled');
    $poster = ddys_open_safe_media_url(ddys_open_item_value($item, array('poster', 'cover', 'avatar'), ''));
    $url = ddys_open_site_url($item);
    $meta = array();
    foreach (array('year', 'type', 'type_code', 'region', 'quality', 'episode', 'status', 'resource_type') as $key) {
        if (!empty($item[$key])) $meta[] = is_array($item[$key]) ? implode(', ', $item[$key]) : $item[$key];
    }
    if (!empty($item['rating'])) $meta[] = '评分 ' . $item['rating'];
    if (!empty($item['is_premiere'])) $meta[] = '首播';
    if (!empty($item['is_finale'])) $meta[] = '季终';
    $summary = ddys_open_item_value($item, array('description', 'intro', 'summary', 'note', 'content', 'bio'), '');
    $html = '<article class="ddys-dede-card">';
    if ($poster !== '') $html .= '<div class="ddys-dede-poster"><img src="' . ddys_open_attr($poster) . '" alt="' . ddys_open_attr($title) . '" loading="lazy" /></div>';
    $html .= '<div class="ddys-dede-card-body"><h3 class="ddys-dede-title">';
    if ($url !== '' && !empty($settings['show_source_link'])) {
        $html .= '<a href="' . ddys_open_attr($url) . '" target="' . ddys_open_attr($settings['target']) . '" rel="noopener">' . ddys_open_h($title) . '</a>';
    } else {
        $html .= ddys_open_h($title);
    }
    $html .= '</h3>';
    if (!empty($meta)) $html .= '<div class="ddys-dede-meta">' . ddys_open_h(implode(' / ', $meta)) . '</div>';
    if ($summary !== '') $html .= '<div class="ddys-dede-summary">' . ddys_open_h(ddys_open_substr(strip_tags((string)$summary), 0, 160)) . '</div>';
    $html .= '</div></article>';
    return $html;
}

function ddys_open_render_list($payload, $args = array())
{
    if (ddys_open_is_error($payload)) return ddys_open_render_error($payload, $args);
    $items = ddys_open_to_list(ddys_open_payload_data($payload));
    if (empty($items)) return ddys_open_render_empty('暂无低端影视内容。', $args);
    $settings = ddys_open_settings();
    $html = '<div class="ddys-dede-items">';
    foreach ($items as $item) $html .= ddys_open_render_card($item, $settings);
    $html .= '</div>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_detail($payload, $args = array())
{
    if (ddys_open_is_error($payload)) return ddys_open_render_error($payload, $args);
    $data = ddys_open_payload_data($payload);
    if (!is_array($data)) return ddys_open_render_empty('暂无详情。', $args);
    $settings = ddys_open_settings();
    $html = '<div class="ddys-dede-detail">' . ddys_open_render_card($data, $settings);
    $intro = ddys_open_item_value($data, array('intro', 'description', 'summary', 'note', 'bio'), '');
    if ($intro !== '') $html .= '<div class="ddys-dede-description">' . nl2br(ddys_open_h($intro)) . '</div>';
    if (!empty($data['movies']) && is_array($data['movies'])) {
        $html .= '<h3>' . ddys_open_h('影片') . '</h3><div class="ddys-dede-items">';
        foreach ($data['movies'] as $item) $html .= ddys_open_render_card($item, $settings);
        $html .= '</div>';
    }
    if (!empty($data['resources']) || !empty($data['sources']) || !empty($data['online']) || !empty($data['download'])) {
        $html .= ddys_open_render_sources($data, $args, true);
    }
    $html .= '</div>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_sources($payload, $args = array(), $inner = false)
{
    if (ddys_open_is_error($payload)) return ddys_open_render_error($payload, $args);
    $data = ddys_open_payload_data($payload);
    $groups = array();
    if (is_array($data)) {
        if (isset($data['resources'])) $groups['资源'] = $data['resources'];
        elseif (isset($data['sources'])) $groups['资源'] = $data['sources'];
        elseif (isset($data['online']) || isset($data['download'])) {
            if (isset($data['online'])) $groups['在线播放'] = $data['online'];
            if (isset($data['download'])) $groups['下载资源'] = $data['download'];
        } else {
            $groups = ddys_open_is_assoc($data) ? $data : array('资源' => $data);
        }
    }
    $html = '<div class="ddys-dede-sources">';
    foreach ($groups as $name => $resources) {
        if (!is_array($resources)) continue;
        $html .= '<section class="ddys-dede-source-group"><h3>' . ddys_open_h($name) . '</h3>';
        foreach ($resources as $resource) {
            if (!is_array($resource)) continue;
            $title = ddys_open_item_value($resource, array('title', 'name', 'download_type', 'type', 'quality'), '资源');
            $url = ddys_open_item_value($resource, array('url', 'link', 'href'), '');
            $safe = preg_match('#^(https?:|magnet:|ed2k:|thunder:)#i', $url) ? $url : '';
            $html .= '<p class="ddys-dede-resource">';
            $html .= $safe !== '' ? '<a href="' . ddys_open_attr($safe) . '" target="_blank" rel="noopener">' . ddys_open_h($title) . '</a>' : ddys_open_h($title);
            $html .= '</p>';
        }
        $html .= '</section>';
    }
    $html .= '</div>';
    return $inner ? $html : ddys_open_wrap($html, $args);
}

function ddys_open_render_calendar($payload, $args = array())
{
    if (ddys_open_is_error($payload)) return ddys_open_render_error($payload, $args);
    $data = ddys_open_payload_data($payload);
    $days = is_array($data) && isset($data['days']) ? $data['days'] : $data;
    if (!is_array($days)) return ddys_open_render_list($payload, $args);
    $settings = ddys_open_settings();
    $html = '<div class="ddys-dede-calendar">';
    foreach ($days as $day => $dayData) {
        $label = (string)$day;
        $items = $dayData;
        if (is_array($dayData) && (isset($dayData['shows']) || isset($dayData['day']) || isset($dayData['weekday']))) {
            $parts = array();
            if (!empty($dayData['day'])) $parts[] = (string)$dayData['day'] . '日';
            if (!empty($dayData['weekday'])) $parts[] = (string)$dayData['weekday'];
            if (!empty($parts)) $label = implode(' ', $parts);
            $items = isset($dayData['shows']) && is_array($dayData['shows']) ? $dayData['shows'] : array();
        }
        $html .= '<section class="ddys-dede-calendar-day"><h3>' . ddys_open_h($label) . '</h3>';
        if (is_array($items) && !empty($items)) {
            $html .= '<div class="ddys-dede-items">';
            foreach ($items as $item) $html .= ddys_open_render_card($item, $settings);
            $html .= '</div>';
        } else {
            $html .= '<p class="ddys-dede-empty-inline">' . ddys_open_h('暂无更新。') . '</p>';
        }
        $html .= '</section>';
    }
    $html .= '</div>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_dictionary($payload, $args = array())
{
    if (ddys_open_is_error($payload)) return ddys_open_render_error($payload, $args);
    $items = ddys_open_to_list(ddys_open_payload_data($payload));
    if (empty($items)) return ddys_open_render_empty('暂无字典数据。', $args);
    $html = '<div class="ddys-dede-tags">';
    foreach ($items as $item) {
        $label = is_array($item) ? ddys_open_item_value($item, array('name', 'title', 'label', 'value'), '') : $item;
        if ($label !== '') $html .= '<span>' . ddys_open_h($label) . '</span>';
    }
    $html .= '</div>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_search($args = array())
{
    $q = ddys_open_get('q', ddys_open_get('ddys_q', isset($args['q']) ? $args['q'] : ''));
    $type = ddys_open_choice(ddys_open_get('type', ddys_open_get('ddys_type', isset($args['type']) ? $args['type'] : 'movie')), array('movie', 'share', 'request'), 'movie');
    $html = '<form class="ddys-dede-search" method="get" action="' . ddys_open_attr(ddys_open_page_url('search')) . '">';
    $settings = ddys_open_settings();
    if (empty($settings['enable_pretty_urls'])) $html .= '<input type="hidden" name="view" value="search" />';
    $html .= '<input type="search" name="q" value="' . ddys_open_attr($q) . '" placeholder="' . ddys_open_attr('搜索低端影视') . '" />';
    $html .= '<select name="type"><option value="movie"' . ($type === 'movie' ? ' selected' : '') . '>' . ddys_open_h('影片') . '</option><option value="share"' . ($type === 'share' ? ' selected' : '') . '>' . ddys_open_h('分享') . '</option><option value="request"' . ($type === 'request' ? ' selected' : '') . '>' . ddys_open_h('求片') . '</option></select>';
    $html .= '<button type="submit">' . ddys_open_h('搜索') . '</button></form>';
    if ($q !== '') $html .= ddys_open_render_list(ddys_open_api_paginated('/search', array('q' => $q, 'type' => $type, 'per_page' => isset($args['per_page']) ? $args['per_page'] : 12)), $args);
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_request_form($args = array())
{
    $settings = ddys_open_settings();
    if (empty($settings['enable_request_form'])) return ddys_open_render_empty('求片表单未启用。', $args);
    $html = '<form class="ddys-dede-request-form" method="post" action="' . ddys_open_attr(ddys_open_endpoint_url('request')) . '" data-ddys-dede-request-form>';
    $html .= '<input type="hidden" name="ddys_nonce" value="' . ddys_open_attr(ddys_open_nonce('request')) . '" />';
    $html .= '<input class="ddys-dede-hp" type="text" name="ddys_website" value="" tabindex="-1" autocomplete="off" />';
    $html .= '<label>' . ddys_open_h('片名') . '<input type="text" name="title" maxlength="255" required /></label>';
    $html .= '<label>' . ddys_open_h('年份') . '<input type="number" name="year" min="1900" max="2099" /></label>';
    $html .= '<label>' . ddys_open_h('类型') . '<select name="type"><option value=""></option><option value="movie">' . ddys_open_h('电影') . '</option><option value="series">' . ddys_open_h('剧集') . '</option><option value="variety">' . ddys_open_h('综艺') . '</option><option value="anime">' . ddys_open_h('动漫') . '</option></select></label>';
    $html .= '<label>' . ddys_open_h('豆瓣 ID') . '<input type="text" name="douban_id" maxlength="30" /></label>';
    $html .= '<label>' . ddys_open_h('备注') . '<textarea name="description" maxlength="1000"></textarea></label>';
    $html .= '<button type="submit">' . ddys_open_h('提交求片') . '</button><p class="ddys-dede-status" role="status"></p></form>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_frontend_assets()
{
    $settings = ddys_open_settings();
    $assets = '';
    if (!empty($settings['enable_styles'])) {
        $assets .= '<link rel="stylesheet" type="text/css" href="' . ddys_open_attr(ddys_open_public_url('static') . '/css/frontend.css?v=' . DDYS_OPEN_VERSION) . '" />';
    }
    $assets .= '<script defer src="' . ddys_open_attr(ddys_open_public_url('static') . '/js/frontend.js?v=' . DDYS_OPEN_VERSION) . '"></script>';
    return $assets;
}

function ddys_open_page_title($view)
{
    $titles = array(
        'latest' => '低端影视', 'movies' => '影片库', 'hot' => '热门影片', 'search' => '搜索',
        'calendar' => '日历', 'movie' => '影片详情', 'sources' => '播放与下载资源',
        'related' => '相关影片', 'comments' => '评论', 'collections' => '片单',
        'collection' => '片单详情', 'shares' => '分享', 'share' => '分享详情',
        'requests' => '求片', 'activities' => '动态', 'user' => '用户',
        'types' => '类型', 'genres' => '分类', 'regions' => '地区',
    );
    return isset($titles[$view]) ? $titles[$view] : '低端影视';
}

function ddys_open_render_nav($active = 'latest')
{
    $settings = ddys_open_settings();
    if (empty($settings['show_nav'])) return '';
    $items = array('latest' => '最新', 'movies' => '影片', 'hot' => '热门', 'search' => '搜索', 'calendar' => '日历', 'collections' => '片单', 'shares' => '分享', 'requests' => '求片');
    $html = '<nav class="ddys-dede-nav">';
    foreach ($items as $view => $label) {
        $class = $view === $active ? ' class="is-active"' : '';
        $html .= '<a' . $class . ' href="' . ddys_open_attr(ddys_open_page_url($view)) . '">' . ddys_open_h($label) . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

function ddys_open_render_page($view, $params)
{
    $view = ddys_open_choice($view, ddys_open_views(), 'latest');
    if ($view === 'request_form') $view = 'requests';
    if ($view === 'search') return ddys_open_render_search($params);
    if ($view === 'requests') {
        $settings = ddys_open_settings();
        $html = empty($settings['enable_request_form']) ? '' : ddys_open_render_request_form(array());
        return $html . ddys_open_render('requests', $params);
    }
    return ddys_open_render($view, $params);
}

function ddys_open_render_full_page($view, $params = array())
{
    $view = ddys_open_choice($view, ddys_open_views(), 'latest');
    $title = ddys_open_page_title($view);
    $assets = ddys_open_frontend_assets();
    $nav = ddys_open_render_nav($view);
    $content = ddys_open_render_page($view, $params);
    $charset = ddys_open_meta_charset();
    return '<!doctype html><html><head><meta charset="' . $charset . '"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . ddys_open_h($title) . '</title>' . $assets . '</head><body class="ddys-dede-page"><main class="ddys-dede-shell"><header class="ddys-dede-page-header"><a class="ddys-dede-brand" href="' . ddys_open_attr(ddys_open_page_url('latest')) . '"><img src="' . ddys_open_attr(ddys_open_public_url('static') . '/images/logo.png') . '" width="32" height="32" alt=""> ' . ddys_open_h('低端影视') . '</a>' . $nav . '</header>' . $content . '</main></body></html>';
}

function ddys_open_allowed_route($route)
{
    return in_array($route, array('movies', 'latest', 'hot', 'search', 'suggest', 'calendar', 'movie', 'sources', 'related', 'comments', 'collections', 'collection', 'shares', 'share', 'requests', 'activities', 'user', 'types', 'genres', 'regions'), true);
}

function ddys_open_proxy_path($route)
{
    $slug = ddys_open_get('slug');
    $id = ddys_open_get('id');
    $username = ddys_open_get('username');
    switch ($route) {
        case 'movies': return '/movies';
        case 'latest': return '/latest';
        case 'hot': return '/hot';
        case 'search': return '/search';
        case 'suggest': return '/suggest';
        case 'calendar': return '/calendar';
        case 'movie': return $slug === '' ? '' : '/movies/' . rawurlencode($slug);
        case 'sources': return $slug === '' ? '' : '/movies/' . rawurlencode($slug) . '/sources';
        case 'related': return $slug === '' ? '' : '/movies/' . rawurlencode($slug) . '/related';
        case 'comments': return $slug === '' ? '' : '/movies/' . rawurlencode($slug) . '/comments';
        case 'collections': return '/collections';
        case 'collection': return $slug === '' ? '' : '/collections/' . rawurlencode($slug);
        case 'shares': return '/shares';
        case 'share': return $id === '' ? '' : '/shares/' . intval($id);
        case 'requests': return '/requests';
        case 'activities': return '/activities';
        case 'user': return $username === '' ? '' : '/user/' . rawurlencode($username);
        case 'types': return '/types';
        case 'genres': return '/genres';
        case 'regions': return '/regions';
    }
    return '';
}

function ddys_open_proxy_query()
{
    return ddys_open_build_query($_GET, array('type', 'genre', 'region', 'year', 'sort', 'page', 'per_page', 'limit', 'q', 'month'));
}

function ddys_open_proxy_response()
{
    $route = strtolower(ddys_open_get('route', 'latest'));
    if (!ddys_open_allowed_route($route)) return ddys_open_error('Route not allowed.', 403);
    $path = ddys_open_proxy_path($route);
    if ($path === '') return ddys_open_error('Invalid route parameters.', 400);
    return ddys_open_api_get($path, ddys_open_proxy_query(), array());
}

function ddys_open_handle_request_form()
{
    $settings = ddys_open_settings();
    if (empty($settings['enable_request_form'])) return ddys_open_error('求片表单未启用。', 403);
    if ($settings['api_key'] === '') return ddys_open_error('低端影视 API Key 尚未配置。', 403);
    if (ddys_open_post('ddys_website') !== '') return ddys_open_error('Invalid request.', 400);
    if (!ddys_open_check_nonce(ddys_open_post('ddys_nonce'), 'request')) return ddys_open_error('表单校验失败，请刷新页面后重试。', 403);
    if (!ddys_open_rate_limit('request', ddys_open_current_ip(), (int)$settings['request_interval'])) return ddys_open_error('提交过于频繁，请稍后再试。', 429);
    $title = ddys_open_post('title');
    if ($title === '') return ddys_open_error('请填写片名。', 400);
    $year = ddys_open_post('year');
    if ($year !== '' && !preg_match('/^[0-9]{4}$/', $year)) return ddys_open_error('年份格式不正确。', 400);
    $yearValue = $year === '' ? '' : ddys_open_int_range($year, 0, 1900, 2099);
    if ($year !== '' && ((int)$year < 1900 || (int)$year > 2099)) return ddys_open_error('年份范围应为 1900-2099。', 400);
    $body = array(
        'title' => ddys_open_substr($title, 0, 255),
        'year' => $yearValue,
        'type' => ddys_open_choice(ddys_open_post('type'), array('movie', 'series', 'variety', 'anime'), ''),
        'description' => ddys_open_substr(ddys_open_post('description'), 0, 1000),
        'douban_id' => ddys_open_substr(ddys_open_post('douban_id'), 0, 30),
    );
    return ddys_open_api_post('/requests', $body, array('auth' => true, 'no_cache' => true));
}

function ddys_open_json_response($payload, $status = 200)
{
    if ($status === 200 && ddys_open_is_error($payload) && !empty($payload['status'])) {
        $status = ddys_open_int_range($payload['status'], 500, 400, 599);
    }
    if (!headers_sent()) {
        if (function_exists('http_response_code')) http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode(ddys_open_json_safe($payload), defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
    exit;
}

function ddys_open_admin_message($message, $type = 'info')
{
    return '<div class="ddys-admin-message ddys-admin-' . ddys_open_attr($type) . '">' . ddys_open_h($message) . '</div>';
}

function ddys_open_admin_page()
{
    $op = ddys_open_get('op');
    $message = '';
    if ($op === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (ddys_open_check_nonce(ddys_open_post('ddys_nonce'), 'admin')) {
            $message = ddys_open_save_settings_from_post() ? ddys_open_admin_message('设置已保存。', 'success') : ddys_open_admin_message('设置保存失败，请检查 data/ddys_open 写入权限。', 'error');
        } else {
            $message = ddys_open_admin_message('表单校验失败，请刷新页面后重试。', 'error');
        }
    } elseif ($op === 'clear' && ddys_open_check_nonce(ddys_open_get('ddys_nonce'), 'admin')) {
        $message = ddys_open_admin_message('已清理缓存文件：' . ddys_open_cache_clear() . ' 个。', 'success');
    } elseif ($op === 'test' && ddys_open_check_nonce(ddys_open_get('ddys_nonce'), 'admin')) {
        $payload = ddys_open_api_get('/types', array(), array('no_cache' => true));
        $message = ddys_open_is_error($payload) ? ddys_open_admin_message($payload['message'], 'error') : ddys_open_admin_message('低端影视 API 连接成功。', 'success');
    }
    $settings = ddys_open_settings();
    $nonce = ddys_open_nonce('admin');
    $self = basename($_SERVER['SCRIPT_NAME']);
    $shortcode = "{dede:ddys type='latest' row='12'/}\n{dede:ddys type='movies' per_page='24'/}\n{dede:ddys type='hot' row='10'/}\n{dede:ddys type='search' q='星际' per_page='12'/}\n{dede:ddys type='calendar' year='2026' month='7'/}\n{dede:ddys type='movie' slug='i-robot'/}\n{dede:ddys type='sources' slug='i-robot'/}\n{dede:ddys type='related' slug='i-robot'/}\n{dede:ddys type='comments' slug='i-robot' per_page='20'/}\n{dede:ddys type='collections' per_page='10'/}\n{dede:ddys type='collection' slug='classic-sci-fi'/}\n{dede:ddys type='shares' per_page='10'/}\n{dede:ddys type='share' id='1'/}\n{dede:ddys type='requests' per_page='10'/}\n{dede:ddys type='activities' per_page='10'/}\n{dede:ddys type='user' username='demo'/}\n{dede:ddys type='types'/}\n{dede:ddys type='genres'/}\n{dede:ddys type='regions'/}\n{dede:ddys type='request_form'/}";
    echo '<!doctype html><html><head><meta charset="' . ddys_open_meta_charset() . '"><title>' . ddys_open_h('低端影视设置') . '</title><style>
body{font:14px/1.6 Arial,sans-serif;margin:18px;color:#1f2937}.ddys-admin-wrap{max-width:1080px}.ddys-admin-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.ddys-admin-box{border:1px solid #d8e0e6;background:#fff;padding:16px}.ddys-admin-box h2{margin:0 0 12px;font-size:16px}.ddys-admin-box label{display:grid;gap:5px;margin:0 0 10px;font-weight:bold}.ddys-admin-box input,.ddys-admin-box select,.ddys-admin-box textarea{box-sizing:border-box;width:100%;padding:7px;border:1px solid #cbd5df}.ddys-admin-actions{display:flex;gap:8px;margin:12px 0}.button{display:inline-block;padding:7px 12px;background:#0f766e;color:#fff;text-decoration:none;border:0;cursor:pointer}.button.secondary{background:#475569}.ddys-admin-message{padding:10px;margin:0 0 12px;border:1px solid #cbd5df}.ddys-admin-success{border-color:#86efac;background:#f0fdf4}.ddys-admin-error{border-color:#fecaca;background:#fef2f2}.ddys-admin-code{white-space:pre-wrap;background:#0f172a;color:#e5e7eb;padding:12px;overflow:auto}@media(max-width:800px){.ddys-admin-grid{grid-template-columns:1fr}}</style></head><body><div class="ddys-admin-wrap"><h1>' . ddys_open_h('低端影视 API 设置') . '</h1>' . $message;
    echo '<div class="ddys-admin-actions"><a class="button secondary" href="' . ddys_open_attr($self . '?op=test&ddys_nonce=' . $nonce) . '">' . ddys_open_h('测试低端影视 API') . '</a><a class="button secondary" href="' . ddys_open_attr($self . '?op=clear&ddys_nonce=' . $nonce) . '">' . ddys_open_h('清理缓存') . '</a><a class="button secondary" target="_blank" href="' . ddys_open_attr(ddys_open_page_url('latest')) . '">' . ddys_open_h('打开前台页面') . '</a></div>';
    echo '<form method="post" action="' . ddys_open_attr($self . '?op=save') . '"><input type="hidden" name="ddys_nonce" value="' . ddys_open_attr($nonce) . '"><div class="ddys-admin-grid"><section class="ddys-admin-box"><h2>' . ddys_open_h('接口') . '</h2>';
    echo ddys_open_admin_input('api_base_url', 'API Base URL', $settings['api_base_url']);
    echo ddys_open_admin_input('site_base_url', '站点来源 URL', $settings['site_base_url']);
    echo ddys_open_admin_input('api_key', 'API Key', $settings['api_key']);
    echo ddys_open_admin_input('timeout', '请求超时秒数', $settings['timeout'], 'number');
    echo '</section><section class="ddys-admin-box"><h2>' . ddys_open_h('缓存') . '</h2>';
    foreach (array('default_cache_ttl' => '默认缓存', 'fresh_cache_ttl' => '最新/热门缓存', 'list_cache_ttl' => '列表缓存', 'detail_cache_ttl' => '详情缓存', 'dictionary_cache_ttl' => '字典缓存', 'community_cache_ttl' => '社区缓存') as $key => $label) echo ddys_open_admin_input($key, $label . ' TTL', $settings[$key], 'number');
    echo '</section><section class="ddys-admin-box"><h2>' . ddys_open_h('展示') . '</h2>';
    echo ddys_open_admin_select('theme', '主题', $settings['theme'], array('auto' => '自动', 'light' => '浅色', 'dark' => '深色'));
    echo ddys_open_admin_select('layout', '布局', $settings['layout'], array('grid' => '网格', 'list' => '列表', 'compact' => '紧凑'));
    echo ddys_open_admin_input('columns', '列数', $settings['columns'], 'number');
    echo ddys_open_admin_select('target', '链接打开方式', $settings['target'], array('_blank' => '新窗口', '_self' => '当前窗口'));
    foreach (array('show_source_link' => '显示来源链接', 'enable_styles' => '加载前台样式', 'show_nav' => '显示导航', 'enable_pretty_urls' => '启用伪静态链接') as $key => $label) echo ddys_open_admin_checkbox($key, $label, $settings[$key]);
    echo ddys_open_admin_input('pretty_base_path', '伪静态基础路径', $settings['pretty_base_path']);
    echo '</section><section class="ddys-admin-box"><h2>' . ddys_open_h('求片与标签') . '</h2>';
    echo ddys_open_admin_checkbox('enable_request_form', '启用求片表单', $settings['enable_request_form']);
    echo ddys_open_admin_input('request_interval', '同 IP 提交间隔秒数', $settings['request_interval'], 'number');
    echo '<p>' . ddys_open_h('模板标签示例') . '</p><pre class="ddys-admin-code">' . ddys_open_h($shortcode) . '</pre></section></div><p><button class="button" type="submit">' . ddys_open_h('保存设置') . '</button></p></form></div></body></html>';
}

function ddys_open_admin_input($name, $label, $value, $type = 'text')
{
    return '<label>' . ddys_open_h($label) . '<input type="' . ddys_open_attr($type) . '" name="' . ddys_open_attr($name) . '" value="' . ddys_open_attr($value) . '"></label>';
}

function ddys_open_admin_select($name, $label, $value, $options)
{
    $html = '<label>' . ddys_open_h($label) . '<select name="' . ddys_open_attr($name) . '">';
    foreach ($options as $key => $text) $html .= '<option value="' . ddys_open_attr($key) . '"' . ($value === $key ? ' selected' : '') . '>' . ddys_open_h($text) . '</option>';
    return $html . '</select></label>';
}

function ddys_open_admin_checkbox($name, $label, $value)
{
    return '<label><span><input type="checkbox" name="' . ddys_open_attr($name) . '" value="1"' . (!empty($value) ? ' checked' : '') . '> ' . ddys_open_h($label) . '</span></label>';
}
