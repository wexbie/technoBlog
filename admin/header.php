<?php
require_once 'config.php';
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
$kullanici_id = $_SESSION['kullanici_id'];
$kullanici_sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$kullanici_sorgu->execute([$kullanici_id]);
$kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo $site_ayarlari['site_baslik']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>