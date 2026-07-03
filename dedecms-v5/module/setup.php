<?php
if (!defined('DEDEINC')) {
    exit('DedeCMS Error: Request Error!');
}
require_once(DEDEINC . '/ddys_open/core.php');

ddys_open_bootstrap_runtime();
$settings = ddys_open_settings();
ddys_open_storage_save_config($settings);

if (function_exists('ShowMsg')) {
    ShowMsg('低端影视 API 模块安装完成，请进入后台“低端影视 API”配置。', 'module_main.php');
    exit;
}
echo '低端影视 API 模块安装完成。';
