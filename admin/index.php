<?php
require_once 'config.php';
require_once 'header.php';
yetki_kontrol(['admin', 'editor', 'yazar']);

$yazi_sayisi = $db->query("SELECT COUNT(*) FROM yazilar")->fetchColumn();
$kategori_sayisi = $db->query("SELECT COUNT(*) FROM kategoriler")->fetchColumn();
$kullanici_sayisi = $db->query("SELECT COUNT(*) FROM kullanicilar")->fetchColumn();
$yorum_sayisi = $db->query("SELECT COUNT(*) FROM yorumlar")->fetchColumn();
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
            <h2><i class="fas fa-home"></i> Kontrol Paneli</h2>
            <div class="admin-user">
                <div class="admin-user-info">
                    <span class="admin-user-name"><?php echo $_SESSION['kullanici_adi']; ?></span>
                    <span class="admin-user-role"><?php echo $_SESSION['kullanici_rol']; ?></span>
                </div>
            </div>
        </div>
        <div class="admin-content">
            <div class="admin-stats">
                <div class="admin-stat-item">
                    <i class="fas fa-newspaper"></i>
                    <div class="admin-stat-info">
                        <span class="admin-stat-value"><?php echo $yazi_sayisi; ?></span>
                        <span class="admin-stat-label">Yazı</span>
                    </div>
                </div>
                <div class="admin-stat-item">
                    <i class="fas fa-tags"></i>
                    <div class="admin-stat-info">
                        <span class="admin-stat-value"><?php echo $kategori_sayisi; ?></span>
                        <span class="admin-stat-label">Kategori</span>
                    </div>
                </div>
                <div class="admin-stat-item">
                    <i class="fas fa-users"></i>
                    <div class="admin-stat-info">
                        <span class="admin-stat-value"><?php echo $kullanici_sayisi; ?></span>
                        <span class="admin-stat-label">Kullanıcı</span>
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

            <br>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><i class="fas fa-file-alt"></i> Son Yazılar</h3>
                    <a href="yazilar.php" class="admin-button admin-button-secondary">Tümünü Gör</a>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("
                                SELECT y.*, k.kategori_adi, u.adisoyadi 
                                FROM yazilar y 
                                LEFT JOIN kategoriler k ON y.kategori_id = k.id 
                                LEFT JOIN kullanicilar u ON y.yazar_id = u.id 
                                ORDER BY y.olusturma_tarihi DESC LIMIT 5
                            ");
                            while($yazi = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
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
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
<?php require_once 'footer.php'; ?> 