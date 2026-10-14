<?php

/**
 * Vercel serverless entrypoint for the Laravel application.
 *
 * The vercel-php runtime runs PHP's built-in server with this file as the
 * router and the `public` directory as the document root (see the
 * VERCEL_PHP_DOCROOT environment variable). Existing static assets are served
 * directly by returning `false`; everything else is handled by the Laravel
 * front controller.
 */
$publicPath = __DIR__.'/../public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
);

if ($uri !== '/' && is_file($publicPath.$uri)) {
    return false;
}

require $publicPath.'/index.php';
