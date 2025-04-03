<?php
require_once 'config.php';
require_once 'header.php';
yetki_kontrol(['admin', 'editor']);

$stmt = $db->query("SELECT * FROM ayarlar WHERE id = 1");
$site_ayarlari = $stmt->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $site_baslik = temizle($_POST['site_baslik']);
    $site_aciklama = temizle($_POST['site_aciklama']);
    $site_email = temizle($_POST['site_email']);
    
    try {
        $stmt = $db->prepare("UPDATE ayarlar SET 
            site_baslik = ?, 
            site_aciklama = ?, 
            site_email = ?
            WHERE id = 1");
        $stmt->execute([
            $site_baslik,
            $site_aciklama,
            $site_email
        ]);
        header('Location: ayarlar.php?mesaj=guncellendi');
        exit;
    } catch(PDOException $e) {
        error_log("Ayarlar güncelleme hatası: " . $e->getMessage());
        $hata = 'Ayarlar güncellenirken bir hata oluştu: ' . $e->getMessage();
    }
}
?>
<div class="admin-container">
<aside class="admin-sidebar">
            <div class="admin-logo">
                <i class="fas fa-code"></i>
                <h1><?php echo $site_ayarlari['site_baslik']; ?></h1>
            </div>
            <ul class="admin-menu">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'aktif' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a></li>
                <?php if ($kullanici['rol'] == 'admin' || $kullanici['rol'] == 'editor'): ?>
                <li><a href="yazilar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'yazilar.php' ? 'aktif' : ''; ?>">
                    <i class="fas fa-newspaper"></i>
                    <span>Yazılar</span>
                </a></li>
                <?php endif; ?>
                <?php if ($kullanici['rol'] == 'admin'): ?>
                <li><a href="kategoriler.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kategoriler.php' ? 'aktif' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    <span>Kategoriler</span>
                </a></li>
                <li><a href="kullanicilar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kullanicilar.php' ? 'aktif' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Kullanıcılar</span>
                </a></li>
                <?php endif; ?>
                <?php if ($kullanici['rol'] == 'admin' || $kullanici['rol'] == 'editor'): ?>
                <li><a href="yorumlar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'yorumlar.php' ? 'aktif' : ''; ?>">
                    <i class="fas fa-comments"></i>
                    <span>Yorumlar</span>
                </a></li>
                <?php endif; ?>
                <?php if ($kullanici['rol'] == 'admin'): ?>
                <li><a href="ayarlar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ayarlar.php' ? 'aktif' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Ayarlar</span>
                </a></li>
                <?php endif; ?>
                <li><a href="profil.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'aktif' : ''; ?>">
                    <i class="fas fa-user"></i>
                    <span>Profilim</span>
                </a></li>
                <li><a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Çıkış Yap</span>
                </a></li>
            </ul>
        </aside>
    <div class="admin-main">
        <div class="admin-header">
            <h2><i class="fas fa-cog"></i> Site Ayarları</h2>
            <div class="admin-user">
                <div class="admin-user-info">
                    <span class="admin-user-name"><?php echo $_SESSION['kullanici_adi']; ?></span>
                    <span class="admin-user-role"><?php echo $_SESSION['kullanici_rol']; ?></span>
                </div>
            </div>
        </div>
        <div class="admin-content">
            <?php if(isset($hata)): ?>
                <div class="admin-alert admin-alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $hata; ?>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['mesaj']) && $_GET['mesaj'] == 'guncellendi'): ?>
                <div class="admin-alert admin-alert-success">
                    <i class="fas fa-check-circle"></i>
                    Ayarlar başarıyla güncellendi.
                </div>
            <?php endif; ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Genel Ayarlar</h3>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="" class="admin-form">
                        <div class="form-group">
                            <label for="site_baslik">Site Başlığı</label>
                            <input type="text" id="site_baslik" name="site_baslik" value="<?php echo $site_ayarlari['site_baslik']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="site_aciklama">Site Açıklaması</label>
                            <textarea id="site_aciklama" name="site_aciklama" required><?php echo $site_ayarlari['site_aciklama']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="site_email">E-posta</label>
                            <input type="email" id="site_email" name="site_email" value="<?php echo $site_ayarlari['site_email']; ?>" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="admin-button admin-button-primary">
                                <i class="fas fa-save"></i> Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>