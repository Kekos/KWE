<?php
$this->page['title'] = __('HEADER_DASHBOARD');
$this->addController('AdminIndex', '');
$this->addController('AdminLogin', '{"admin": true, "session_name": "admin"}');
?>