<?php
require_once(dirname(__FILE__) . '/../system/common.inc.php');
require_once(DEDEINC . '/ddys_open/core.php');

ddys_open_json_response(ddys_open_handle_request_form());
