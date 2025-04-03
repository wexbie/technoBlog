<?php
require_once 'config.php';
require_once 'header.php';
yetki_kontrol(['admin', 'editor']);

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: kullanicilar.php');
    exit;
}

$kullanici_id = (int)$_GET['id'];
$stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$kullanici) {
    header('Location: kullanicilar.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adisoyadi = temizle($_POST['adisoyadi']);
    $kullaniciadi = temizle($_POST['kullaniciadi']);
    $email = temizle($_POST['email']);
    $rol = $_POST['rol'];
    $durum = isset($_POST['durum']) ? 1 : 0;
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM kullanicilar WHERE (kullaniciadi = ? OR email = ?) AND id != ?");
        $stmt->execute([$kullaniciadi, $email, $kullanici_id]);
        if($stmt->fetchColumn() > 0) {
            $hata = 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.';
        } else {
            if(!empty($_POST['sifre'])) {
                if($_POST['sifre'] !== $_POST['sifre_tekrar']) {
                    $hata = 'Şifreler eşleşmiyor.';
                } else {
                    $sifre_hash = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE kullanicilar SET adisoyadi = ?, kullaniciadi = ?, email = ?, sifre = ?, rol = ?, durum = ? WHERE id = ?");
                    $stmt->execute([$adisoyadi, $kullaniciadi, $email, $sifre_hash, $rol, $durum, $kullanici_id]);
                }
            } else {
                $stmt = $db->prepare("UPDATE kullanicilar SET adisoyadi = ?, kullaniciadi = ?, email = ?, rol = ?, durum = ? WHERE id = ?");
                $stmt->execute([$adisoyadi, $kullaniciadi, $email, $rol, $durum, $kullanici_id]);
            }
            
            if(!isset($hata)) {
                header('Location: kullanicilar.php?mesaj=guncellendi');
                exit;
            }
        }
    } catch(PDOException $e) {
        $hata = 'Kullanıcı güncellenirken bir hata oluştu.';
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
            <h2><i class="fas fa-user-edit"></i> Kullanıcı Düzenle</h2>
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
                        <input type="text" id="adisoyadi" name="adisoyadi" value="<?php echo $kullanici['adisoyadi']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kullaniciadi">Kullanıcı Adı</label>
                        <input type="text" id="kullaniciadi" name="kullaniciadi" value="<?php echo $kullanici['kullaniciadi']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-posta</label>
                        <input type="email" id="email" name="email" value="<?php echo $kullanici['email']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="sifre">Yeni Şifre (Boş bırakılırsa değişmez)</label>
                        <input type="password" id="sifre" name="sifre">
                    </div>
                    
                    <div class="form-group">
                        <label for="sifre_tekrar">Yeni Şifre Tekrar</label>
                        <input type="password" id="sifre_tekrar" name="sifre_tekrar">
                    </div>
                    
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol" required>
                            <option value="editor" <?php echo $kullanici['rol'] == 'editor' ? 'selected' : ''; ?>>Editör</option>
                            <option value="yazar" <?php echo $kullanici['rol'] == 'yazar' ? 'selected' : ''; ?>>Yazar</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="durum" <?php echo $kullanici['durum'] ? 'checked' : ''; ?>>
                            <span>Aktif</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="admin-button admin-button-primary">
                            <i class="fas fa-save"></i> Güncelle
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