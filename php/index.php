<?php
/**
 * NetaTrack India — Redirect helper
 * If someone accidentally points document root to php/ instead of php/public/,
 * this redirects them to the correct public index.
 *
 * CORRECT document root = php/public/
 * Upload public/ contents directly to public_html/ on shared hosting.
 */
header('Location: public/index.php', true, 301);
exit;
