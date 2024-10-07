<?php
session_start();
session_destroy();

if (!headers_sent()) {
    header("Location: login.php");
    exit();
} else {
    echo "Headers already sent.";
}

?>