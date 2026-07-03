<?php
if (!defined('DEDEINC')) {
    exit('DedeCMS Error: Request Error!');
}
require_once(DEDEINC . '/ddys_open/core.php');

function lib_ddys(&$ctag, &$refObj)
{
    $args = array();
    foreach (array('type', 'row', 'limit', 'page', 'per_page', 'q', 'slug', 'id', 'username', 'year', 'month', 'genre', 'region', 'sort', 'layout', 'theme', 'columns') as $key) {
        $value = $ctag->GetAtt($key);
        if ($value !== '') {
            $args[$key] = $value;
        }
    }
    if (isset($args['row']) && !isset($args['limit'])) {
        $args['limit'] = $args['row'];
    }
    $type = isset($args['type']) ? $args['type'] : 'latest';
    unset($args['type']);
    return ddys_open_render($type, $args);
}
