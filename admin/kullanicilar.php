<?php
require_once 'config.php';
require_once 'header.php';

yetki_kontrol(['admin', 'editor']);

if(isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    
    try {
        $yazi_sayisi = $db->query("SELECT COUNT(*) FROM yazilar WHERE yazar_id = $id")->fetchColumn();
        
        if($yazi_sayisi > 0) {
            $hata = 'Bu kullanıcıya ait yazılar bulunduğu için silinemez.';
        } else {
            $stmt = $db->prepare("DELETE FROM kullanicilar WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: kullanicilar.php?mesaj=silindi');
            exit;
        }
    } catch(PDOException $e) {
        $hata = 'Kullanıcı silinirken bir hata oluştu.';
    }
}

$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 10;
$offset = ($sayfa - 1) * $limit;
$toplam_kullanici = $db->query("SELECT COUNT(*) FROM kullanicilar")->fetchColumn();
$toplam_sayfa = ceil($toplam_kullanici / $limit);
$kullanicilar = $db->query("
    SELECT k.*, COUNT(y.id) as yazi_sayisi 
    FROM kullanicilar k 
    LEFT JOIN yazilar y ON k.id = y.yazar_id 
    GROUP BY k.id 
    ORDER BY k.kayit_tarihi DESC 
    LIMIT $limit OFFSET $offset
")->fetchAll(PDO::FETCH_ASSOC);
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
            <h2><i class="fas fa-users"></i> Kullanıcılar</h2>
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

            <?php if(isset($_GET['mesaj'])): ?>
                <div class="admin-alert admin-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    switch($_GET['mesaj']) {
                        case 'eklendi':
                            echo 'Kullanıcı başarıyla eklendi.';
                            break;
                        case 'guncellendi':
                            echo 'Kullanıcı başarıyla güncellendi.';
                            break;
                        case 'silindi':
                            echo 'Kullanıcı başarıyla silindi.';
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Kullanıcı Listesi</h3>
                    <a href="kullanici_ekle.php" class="admin-button admin-button-primary">
                        <i class="fas fa-user-plus"></i> Yeni Kullanıcı
                    </a>
                </div>

                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>Kullanıcı Adı</th>
                                <th>E-posta</th>
                                <th>Rol</th>
                                <th>Yazı Sayısı</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kullanicilar as $kullanici): ?>
                            <tr>
                                <td><?php echo $kullanici['id']; ?></td>
                                <td><?php echo $kullanici['adisoyadi']; ?></td>
                                <td><?php echo $kullanici['kullaniciadi']; ?></td>
                                <td><?php echo $kullanici['email']; ?></td>
                                <td>
                                    <span class="admin-badge admin-badge-<?php 
                                        echo $kullanici['rol'] == 'admin' ? 'primary' : 
                                            ($kullanici['rol'] == 'editor' ? 'info' : 'secondary'); 
                                    ?>">
                                        <?php echo ucfirst($kullanici['rol']); ?>
                                    </span>
                                </td>
                                <td><?php echo $kullanici['yazi_sayisi']; ?></td>
                                <td>
                                    <span class="admin-badge <?php echo $kullanici['durum'] ? 'admin-badge-success' : 'admin-badge-danger'; ?>">
                                        <?php echo $kullanici['durum'] ? 'Aktif' : 'Pasif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-table-actions">
                                        <a href="kullanici_duzenle.php?id=<?php echo $kullanici['id']; ?>" class="admin-button admin-button-small admin-button-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($kullanici['yazi_sayisi'] == 0 && $kullanici['id'] != $_SESSION['kullanici_id']): ?>
                                        <a href="kullanicilar.php?sil=<?php echo $kullanici['id']; ?>" class="admin-button admin-button-small admin-button-danger" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($toplam_sayfa > 1): ?>
                <div class="admin-pagination">
                    <?php for($i = 1; $i <= $toplam_sayfa; $i++): ?>
                        <a href="?sayfa=<?php echo $i; ?>" class="admin-button admin-button-small <?php echo $i == $sayfa ? 'admin-button-primary' : 'admin-button-secondary'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 