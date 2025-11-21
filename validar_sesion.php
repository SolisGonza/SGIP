<?php
  session_start();
  if (isset($_SESSION['loggedin'])) {
  } else {
    header("Location: ../../index.php");
    exit();
  }
?>