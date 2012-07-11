<?php
$this->page['title'] = 'Översikt';
$this->addController('AdminIndex', '');
$this->addController('AdminLogin', '{"admin": true, "session_name": "admin"}');
?>