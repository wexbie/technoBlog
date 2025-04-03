<?php
require_once 'config.php';
require_once 'header.php';

$stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$_SESSION['kullanici_id']]);
$kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adisoyadi = temizle($_POST['adisoyadi']);
    $kullaniciadi = temizle($_POST['kullaniciadi']);
    $email = temizle($_POST['email']);
    $mevcut_sifre = $_POST['mevcut_sifre'];
    $yeni_sifre = $_POST['yeni_sifre'];
    $yeni_sifre_tekrar = $_POST['yeni_sifre_tekrar'];
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM kullanicilar WHERE (kullaniciadi = ? OR email = ?) AND id != ?");
        $stmt->execute([$kullaniciadi, $email, $_SESSION['kullanici_id']]);
        if($stmt->fetchColumn() > 0) {
            $hata = 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.';
        } else {
            if(!empty($yeni_sifre)) {
                if(!password_verify($mevcut_sifre, $kullanici['sifre'])) {
                    $hata = 'Mevcut şifre yanlış.';
                } elseif($yeni_sifre !== $yeni_sifre_tekrar) {
                    $hata = 'Yeni şifreler eşleşmiyor.';
                } else {
                    $sifre_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE kullanicilar SET adisoyadi = ?, kullaniciadi = ?, email = ?, sifre = ? WHERE id = ?");
                    $stmt->execute([$adisoyadi, $kullaniciadi, $email, $sifre_hash, $_SESSION['kullanici_id']]);
                }
            } else {
                $stmt = $db->prepare("UPDATE kullanicilar SET adisoyadi = ?, kullaniciadi = ?, email = ? WHERE id = ?");
                $stmt->execute([$adisoyadi, $kullaniciadi, $email, $_SESSION['kullanici_id']]);
            }
            if(!isset($hata)) {
                $_SESSION['kullanici_adi'] = $adisoyadi;
                $_SESSION['kullanici_email'] = $email;
                
                header('Location: profil.php?mesaj=guncellendi');
                exit;
            }
        }
    } catch(PDOException $e) {
        $hata = 'Profil güncellenirken bir hata oluştu.';
    }
}

$yazi_sayisi = $db->query("SELECT COUNT(*) FROM yazilar WHERE yazar_id = " . $_SESSION['kullanici_id'])->fetchColumn();
$yorum_sayisi = $db->query("SELECT COUNT(*) FROM yorumlar WHERE kullanici_id = " . $_SESSION['kullanici_id'])->fetchColumn();
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
            <h2><i class="fas fa-user"></i> Profilim</h2>
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
                    Profil başarıyla güncellendi.
                </div>
            <?php endif; ?>

            <div class="admin-row">
                <div class="admin-col-md-4">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>Profil Bilgileri</h3>
                        </div>
                        <div class="admin-card-body">
                            <div class="admin-profile-info">
                                <div class="admin-profile-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="admin-profile-details">
                                    <h4><?php echo $kullanici['adisoyadi']; ?></h4>
                                    <p><?php echo $kullanici['email']; ?></p>
                                    <span class="admin-badge admin-badge-<?php 
                                        echo $kullanici['rol'] == 'admin' ? 'primary' : 
                                            ($kullanici['rol'] == 'editor' ? 'info' : 'secondary'); 
                                    ?>">
                                        <?php echo ucfirst($kullanici['rol']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-col-md-4">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>İstatistikler</h3>
                        </div>
                        <div class="admin-card-body">
                            <div class="admin-stats">
                                <div class="admin-stat-item">
                                    <i class="fas fa-newspaper"></i>
                                    <div class="admin-stat-info">
                                        <span class="admin-stat-value"><?php echo $yazi_sayisi; ?></span>
                                        <span class="admin-stat-label">Yazı</span>
                                    </div>
                                </div>
                                <div class="admin-stat-item">
                                    <i class="fas fa-comments"></i>
                                    <div class="admin-stat-info">
                                        <span class="admin-stat-value"><?php echo $yorum_sayisi; ?></span>
                                        <span class="admin-stat-label">Yorum</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-col-md-4">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>Profil Düzenle</h3>
                        </div>
                        <div class="admin-card-body">
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
                                    <label for="mevcut_sifre">Mevcut Şifre</label>
                                    <input type="password" id="mevcut_sifre" name="mevcut_sifre">
                                </div>
                                
                                <div class="form-group">
                                    <label for="yeni_sifre">Yeni Şifre</label>
                                    <input type="password" id="yeni_sifre" name="yeni_sifre">
                                </div>
                                
                                <div class="form-group">
                                    <label for="yeni_sifre_tekrar">Yeni Şifre Tekrar</label>
                                    <input type="password" id="yeni_sifre_tekrar" name="yeni_sifre_tekrar">
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="admin-button admin-button-primary">
                                        <i class="fas fa-save"></i> Güncelle
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 