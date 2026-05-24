<?php
session_start();

require_once '../includes/helpers.php';

session_unset();
session_destroy();

session_start();

set_flash('success', 'সফলভাবে লগআউট হয়েছে।');

header("Location: login.php");
exit;
