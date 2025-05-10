<?php
// includes/functions.php
function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}
?>
