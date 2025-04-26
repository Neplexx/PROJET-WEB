<?php
// Liste des thèmes autorisés
$allowed_themes = ['light', 'dark'];

// Lire le thème actuel depuis le cookie
$current_theme = isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $allowed_themes) ? $_COOKIE['theme'] : 'light';
?>
