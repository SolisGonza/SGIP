<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  if (isset($_SESSION['loggedin'])) {
  } else {
    header("Location: ../../index.php");
    exit();
  }
?>