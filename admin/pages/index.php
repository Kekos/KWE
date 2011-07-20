<?php
$this->page['title'] = 'Översikt';
$this->addController('admin_index', '');
$this->addController('admin_login', '{"admin": true, "session_name": "admin"}');
?>