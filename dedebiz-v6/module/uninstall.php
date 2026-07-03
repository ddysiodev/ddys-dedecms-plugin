<?php
if (!defined('DEDEINC')) {
    exit('DedeCMS Error: Request Error!');
}

function ddys_open_remove_dir($dir, $root)
{
    $dir = str_replace('\\', '/', $dir);
    $root = rtrim(str_replace('\\', '/', $root), '/');
    if ($dir === '' || ($dir !== $root && strpos($dir, $root . '/') !== 0) || !is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        $path = $dir . '/' . $name;
        if (is_dir($path)) {
            ddys_open_remove_dir($path, $root);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

ddys_open_remove_dir(DEDEDATA . '/ddys_open', DEDEDATA . '/ddys_open');

if (function_exists('ShowMsg')) {
    ShowMsg('低端影视 API 模块已卸载。', 'module_main.php');
    exit;
}
echo '低端影视 API 模块已卸载。';
