<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8">
<script>
  localStorage.removeItem('token');
  window.location.href = '/login.php';
</script>
</head><body></body></html>


