<?php
  if (isset($_SESSION['loggedin'])) {
  } else {
    header("Location: ../../index.php");
    exit();
  }
?>