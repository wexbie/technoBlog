<?php
require_once 'config.php';
require_once 'header.php';
yetki_kontrol(['admin', 'editor']);


if(isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    try {
        $stmt = $db->prepare("DELETE FROM yazilar WHERE id = ?");
        $stmt->execute([$_GET['sil']]);
        header('Location: yazilar.php?mesaj=silindi');
        exit;
    } catch(PDOException $e) {
        $hata = 'Yazı silinirken bir hata oluştu.';
    }
}

$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 10;
$offset = ($sayfa - 1) * $limit;
$toplam_yazi = $db->query("SELECT COUNT(*) FROM yazilar")->fetchColumn();
$toplam_sayfa = ceil($toplam_yazi / $limit);

$stmt = $db->prepare("
    SELECT y.*, k.kategori_adi, u.adisoyadi 
    FROM yazilar y 
    LEFT JOIN kategoriler k ON y.kategori_id = k.id 
    LEFT JOIN kullanicilar u ON y.yazar_id = u.id 
    ORDER BY y.olusturma_tarihi DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$yazilar = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <h2><i class="fas fa-file-alt"></i> Yazılar</h2>
            <div class="admin-user">
                <div class="admin-user-info">
                    <span class="admin-user-name"><?php echo $_SESSION['kullanici_adi']; ?></span>
                    <span class="admin-user-role"><?php echo $_SESSION['kullanici_rol']; ?></span>
                </div>
            </div>
        </div>

        <div class="admin-content">
            <?php if(isset($_GET['mesaj'])): ?>
                <div class="admin-alert admin-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    switch($_GET['mesaj']) {
                        case 'eklendi':
                            echo 'Yazı başarıyla eklendi.';
                            break;
                        case 'guncellendi':
                            echo 'Yazı başarıyla güncellendi.';
                            break;
                        case 'silindi':
                            echo 'Yazı başarıyla silindi.';
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Yazı Listesi</h3>
                    <a href="yazi_ekle.php" class="admin-button admin-button-primary">
                        <i class="fas fa-plus"></i> Yeni Yazı
                    </a>
                </div>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Başlık</th>
                                <th>Kategori</th>
                                <th>Yazar</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($yazilar as $yazi): ?>
                            <tr>
                                <td><?php echo $yazi['baslik']; ?></td>
                                <td><?php echo $yazi['kategori_adi']; ?></td>
                                <td><?php echo $yazi['adisoyadi']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $yazi['durum']; ?>">
                                        <?php echo $yazi['durum']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($yazi['olusturma_tarihi'])); ?></td>
                                <td>
                                    <a href="yazi_duzenle.php?id=<?php echo $yazi['id']; ?>" class="admin-button admin-button-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="yazilar.php?sil=<?php echo $yazi['id']; ?>" class="admin-button admin-button-danger" onclick="return confirm('Bu yazıyı silmek istediğinizden emin misiniz?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($toplam_sayfa > 1): ?>
            <div class="pagination">
                <?php for($i = 1; $i <= $toplam_sayfa; $i++): ?>
                    <a href="?sayfa=<?php echo $i; ?>" class="<?php echo $i == $sayfa ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 