<?php
require_once(dirname(__FILE__) . '/../include/common.inc.php');
require_once(DEDEINC . '/ddys_open/core.php');

$view = ddys_open_get('view', 'latest');
$params = array();
foreach ($_GET as $key => $value) {
    $params[$key] = ddys_open_scalar($value);
}
echo ddys_open_render_full_page($view, $params);
