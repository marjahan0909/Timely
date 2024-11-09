<?php
$uploadsDir = 'uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true); // Creates the directory with the necessary permissions
}
