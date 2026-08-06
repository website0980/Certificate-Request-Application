<?php
require_once __DIR__ . '/../includes/common.php';
require_once __DIR__ . '/../includes/auth.php';
logoutAdmin();
redirect('/admin/login.php');
