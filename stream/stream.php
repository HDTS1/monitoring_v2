<?php
// Cesta k súboru MP4
$videoFile = '../test.mp4';

// Skontrolujte, či súbor existuje
if (!file_exists($videoFile)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Získajte veľkosť súboru
$filesize = filesize($videoFile);

$etag = "02785bff94835821362b7ca606f4ef0e";

header("ETag: \"$etag\"");


if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === "\"$etag\"") {
    header("HTTP/1.0 304 Not Modified");
    exit;
}



// Získajte MIME typ
$mimeType = 'video/mp4';

// Nastavte hlavičky pre streaming
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $filesize);
header('Accept-Ranges: bytes');
header("ETag: \"$etag\"");

// Skontrolujte, či sú požiadavky na rozsah (Range requests)
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    list($unit, $range) = explode('=', $range, 2);
    if ($unit !== 'bytes') {
        header("HTTP/1.0 416 Requested Range Not Satisfiable");
        exit;
    }
    
    // Rozdelenie rozsahu
    list($start, $end) = explode('-', $range, 2);
    $start = intval($start);
    
    if ($end === '') {
        $end = $filesize - 1;
    } else {
        $end = intval($end);
    }

    // Nastavenie hlavičiek pre rozsah
    $length = $end - $start + 1;
    header("HTTP/1.1 206 Partial Content");
    header("Content-Range: bytes $start-$end/$filesize");
    header("Content-Length: $length");
    
    // Otvorenie súboru a preskočenie na požadovaný rozsah
    $fp = fopen($videoFile, 'rb');
    fseek($fp, $start);
    fpassthru($fp);
    fclose($fp);
    exit;
} else {
    // Bez požiadavky na rozsah, prehrajte celý súbor
    readfile($videoFile);
}


