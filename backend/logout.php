<?php
// logout checkpoint 
session_start();
session_destroy();
header('Location: index.php');
exit();
?>