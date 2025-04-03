<?php
require_once 'config.php';
require_once 'header.php';
yetki_kontrol(['admin', 'editor']);

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: kategoriler.php');
    exit;
}

$kategori_id = (int)$_GET['id'];
$stmt = $db->prepare("SELECT * FROM kategoriler WHERE id = ?");
$stmt->execute([$kategori_id]);
$kategori = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$kategori) {
    header('Location: kategoriler.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategori_adi = temizle($_POST['kategori_adi']);
    $slug = slug_olustur($kategori_adi);
    $aciklama = temizle($_POST['aciklama']);
    $durum = isset($_POST['durum']) ? 1 : 0;
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM kategoriler WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $kategori_id]);
        if($stmt->fetchColumn() > 0) {
            $hata = 'Bu kategori adı zaten kullanılıyor.';
        } else {
            $stmt = $db->prepare("UPDATE kategoriler SET kategori_adi = ?, slug = ?, aciklama = ?, durum = ? WHERE id = ?");
            $stmt->execute([$kategori_adi, $slug, $aciklama, $durum, $kategori_id]);
            
            header('Location: kategoriler.php?mesaj=guncellendi');
            exit;
        }
    } catch(PDOException $e) {
        $hata = 'Kategori güncellenirken bir hata oluştu.';
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
            <h2><i class="fas fa-edit"></i> Kategori Düzenle</h2>
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

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Kategori Bilgileri</h3>
                </div>
                <form method="POST" action="" class="admin-form">
                    <div class="form-group">
                        <label for="kategori_adi">Kategori Adı</label>
                        <input type="text" id="kategori_adi" name="kategori_adi" value="<?php echo $kategori['kategori_adi']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="aciklama">Açıklama</label>
                        <textarea id="aciklama" name="aciklama" rows="3"><?php echo $kategori['aciklama']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="durum" <?php echo $kategori['durum'] ? 'checked' : ''; ?>>
                            <span>Aktif</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="admin-button admin-button-primary">
                            <i class="fas fa-save"></i> Güncelle
                        </button>
                        <a href="kategoriler.php" class="admin-button admin-button-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 