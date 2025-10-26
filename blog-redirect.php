<?php
// Redirect handler for blog page
$request_uri = $_SERVER['REQUEST_URI'];
$decoded_uri = urldecode($request_uri);

// Check if this is the blog URL we want to redirect
if (strpos($decoded_uri, 'блог-ecopackpro-упаковочные-материалы-и-решен-2') !== false) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: https://ecopackpro.ru/blog-ecopackpro-upakovochnye-materialy-i-reshen-2/');
    exit();
}

// Check for URL encoded version
if (strpos($request_uri, '%d0%b1%d0%bb%d0%be%d0%b3-ecopackpro') !== false) {
    header('HTTP/1.1 301 Moved Permanently');  
    header('Location: https://ecopackpro.ru/blog-ecopackpro-upakovochnye-materialy-i-reshen-2/');
    exit();
}
?>
