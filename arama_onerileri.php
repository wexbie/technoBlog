<?php
require_once 'config.php';
require_once 'ajax.php';

header('Content-Type: application/json');

$arama_terimi = isset($_GET['q']) ? trim($_GET['q']) : '';
if (!empty($arama_terimi)) {
    $blog = new Blog($conn);
    $sonuclar = $blog->aramaYap($arama_terimi, 5);
    $oneriler = [];
    if ($sonuclar && $sonuclar->num_rows > 0) {
        while ($yazi = $sonuclar->fetch_assoc()) {
            $oneriler[] = [
                'id' => $yazi['id'],
                'baslik' => $yazi['baslik'],
                'slug' => $yazi['slug'],
                'ozet' => mb_substr(strip_tags($yazi['ozet']), 0, 100) . '...',
                'kategori' => $yazi['kategori_adi']
            ];
        }
    } echo json_encode($oneriler);
} else {
    echo json_encode([]);
} 