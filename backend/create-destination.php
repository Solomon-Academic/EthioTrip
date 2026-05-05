<?php
session_start();
require_once 'config/database.php';
?>
<form method="POST">
    <input type="text" name="name" placeholder="Name"><br>
    <input type="text" name="location" placeholder="Location"><br>
    <textarea name="description" placeholder="Description"></textarea><br>
    <button type="submit">Create</button>
</form>