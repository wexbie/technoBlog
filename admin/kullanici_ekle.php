<?php
require_once 'config.php';
require_once 'header.php';
yetki_kontrol(['admin', 'editor']);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adisoyadi = temizle($_POST['adisoyadi']);
    $kullaniciadi = temizle($_POST['kullaniciadi']);
    $email = temizle($_POST['email']);
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];
    $rol = $_POST['rol'];
    $durum = isset($_POST['durum']) ? 1 : 0;
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM kullanicilar WHERE kullaniciadi = ? OR email = ?");
        $stmt->execute([$kullaniciadi, $email]);
        if($stmt->fetchColumn() > 0) {
            $hata = 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.';
        } elseif($sifre !== $sifre_tekrar) {
            $hata = 'Şifreler eşleşmiyor.';
        } else {
            $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO kullanicilar (adisoyadi, kullaniciadi, email, sifre, rol, durum) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$adisoyadi, $kullaniciadi, $email, $sifre_hash, $rol, $durum]);
            
            header('Location: kullanicilar.php?mesaj=eklendi');
            exit;
        }
    } catch(PDOException $e) {
        $hata = 'Kullanıcı eklenirken bir hata oluştu.';
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
            <h2><i class="fas fa-user-plus"></i> Yeni Kullanıcı</h2>
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
                    <h3>Kullanıcı Bilgileri</h3>
                </div>
                <form method="POST" action="" class="admin-form">
                    <div class="form-group">
                        <label for="adisoyadi">Ad Soyad</label>
                        <input type="text" id="adisoyadi" name="adisoyadi" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kullaniciadi">Kullanıcı Adı</label>
                        <input type="text" id="kullaniciadi" name="kullaniciadi" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-posta</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="sifre">Şifre</label>
                        <input type="password" id="sifre" name="sifre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="sifre_tekrar">Şifre Tekrar</label>
                        <input type="password" id="sifre_tekrar" name="sifre_tekrar" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol" required>
                            <option value="editor">Editör</option>
                            <option value="yazar">Yazar</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="durum" checked>
                            <span>Aktif</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="admin-button admin-button-primary">
                            <i class="fas fa-save"></i> Kaydet
                        </button>
                        <a href="kullanicilar.php" class="admin-button admin-button-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 