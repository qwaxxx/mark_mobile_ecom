<?php
session_start();
$unread_count = isset($_SESSION['unread_count']);
echo $unread_count;
