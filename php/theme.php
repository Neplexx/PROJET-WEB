<?php
$allowed_themes = ['light', 'dark'];
$current_theme = isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $allowed_themes) ? $_COOKIE['theme'] : 'light';
?>
