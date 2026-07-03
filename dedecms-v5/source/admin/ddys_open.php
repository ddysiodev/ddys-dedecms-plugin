<?php
require_once(dirname(__FILE__) . '/config.php');
CheckPurview('sys_module');
require_once(DEDEINC . '/ddys_open/core.php');

ddys_open_admin_page();
