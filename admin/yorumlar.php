<?php
require_once 'config.php';
require_once 'header.php';
yetki_kontrol(['admin', 'editor']);

if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $yorum_id = $_GET['sil'];
    $db->prepare("DELETE FROM yorumlar WHERE id = ?")->execute([$yorum_id]);
    header("Location: yorumlar.php?mesaj=silindi");
    exit;
}

if (isset($_GET['durum']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $yorum_id = $_GET['id'];
    $yeni_durum = $_GET['durum'];

    if (in_array($yeni_durum, ['beklemede', 'onaylandi', 'reddedildi'])) {
        $db->prepare("UPDATE yorumlar SET durum = ? WHERE id = ?")->execute([$yeni_durum, $yorum_id]);
        header("Location: yorumlar.php?mesaj=guncellendi");
        exit;
    }
}

$sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
$limit = 10;
$offset = ($sayfa - 1) * $limit;
$toplam_yorum = $db->query("SELECT COUNT(*) FROM yorumlar")->fetchColumn();
$toplam_sayfa = ceil($toplam_yorum / $limit);

$stmt = $db->prepare("
    SELECT y.*, yz.baslik as yazi_baslik, k.adisoyadi as kullanici_adi
    FROM yorumlar y
    LEFT JOIN yazilar yz ON y.yazi_id = yz.id
    LEFT JOIN kullanicilar k ON y.kullanici_id = k.id
    ORDER BY y.olusturma_tarihi DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$yorumlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <li><a href="yazilar.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'yazilar.php' ? 'aktif' : ''; ?>">
                        <i class="fas fa-newspaper"></i>
                        <span>Yazılar</span>
                    </a></li>
            <?php endif; ?>
            <?php if ($kullanici['rol'] == 'admin'): ?>
                <li><a href="kategoriler.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'kategoriler.php' ? 'aktif' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Kategoriler</span>
                    </a></li>
                <li><a href="kullanicilar.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'kullanicilar.php' ? 'aktif' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Kullanıcılar</span>
                    </a></li>
            <?php endif; ?>
            <?php if ($kullanici['rol'] == 'admin' || $kullanici['rol'] == 'editor'): ?>
                <li><a href="yorumlar.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'yorumlar.php' ? 'aktif' : ''; ?>">
                        <i class="fas fa-comments"></i>
                        <span>Yorumlar</span>
                    </a></li>
            <?php endif; ?>
            <?php if ($kullanici['rol'] == 'admin'): ?>
                <li><a href="ayarlar.php"
                        class="<?php echo basename($_SERVER['PHP_SELF']) == 'ayarlar.php' ? 'aktif' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Ayarlar</span>
                    </a></li>
            <?php endif; ?>
            <li><a href="profil.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'aktif' : ''; ?>">
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
            <h2><i class="fas fa-home"></i> Yorumlar</h2>
            <div class="admin-user">
                <div class="admin-user-info">
                    <span class="admin-user-name"><?php echo $_SESSION['kullanici_adi']; ?></span>
                    <span class="admin-user-role"><?php echo $_SESSION['kullanici_rol']; ?></span>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['mesaj'])): ?>
            <div class="alert alert-success">
                <?php
                switch ($_GET['mesaj']) {
                    case 'silindi':
                        echo 'Yorum başarıyla silindi.';
                        break;
                    case 'guncellendi':
                        echo 'Yorum durumu güncellendi.';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        <div class="admin-content">
            <div class="admin-card-header" style="background-color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h3>Yorumlar</h3>
            </div>
            <div class="admin-card">
                <div class="admin-table">
                    <table>
                        <thead style="background-color: white;">
                            <tr>
                                <th>Yazı</th>
                                <th>Kullanıcı</th>
                                <th>Yorum</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yorumlar as $yorum): ?>
                                <tr>
                                    <td>
                                        <a href="../yazi.php?id=<?php echo $yorum['yazi_id']; ?>" target="_blank">
                                            <?php echo $yorum['yazi_baslik']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo $yorum['kullanici_adi']; ?></td>
                                    <td><?php echo mb_substr($yorum['yorum'], 0, 100) . '...'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $yorum['durum']; ?>">
                                            <?php echo $yorum['durum']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($yorum['olusturma_tarihi'])); ?></td>
                                    <td>
                                        <div class="admin-actions">
                                            <?php if ($yorum['durum'] == 'beklemede'): ?>
                                                <a href="?durum=onaylandi&id=<?php echo $yorum['id']; ?>"
                                                    class="admin-button admin-button-success" title="Onayla">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="?durum=reddedildi&id=<?php echo $yorum['id']; ?>"
                                                    class="admin-button admin-button-warning" title="Reddet">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?sil=<?php echo $yorum['id']; ?>"
                                                class="admin-button admin-button-danger" title="Sil"
                                                onclick="return confirm('Bu yorumu silmek istediğinizden emin misiniz?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($toplam_sayfa > 1): ?>
                    <div class="admin-pagination">
                        <?php for ($i = 1; $i <= $toplam_sayfa; $i++): ?>
                            <a href="?sayfa=<?php echo $i; ?>"
                                class="admin-button <?php echo $sayfa == $i ? 'admin-button-primary' : 'admin-button-secondary'; ?>">
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