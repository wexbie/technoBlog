<?php
require_once 'config.php';
require_once 'header.php';
yetki_kontrol(['admin', 'editor']);

$kategoriler = $db->query("SELECT * FROM kategoriler WHERE durum = 1 ORDER BY kategori_adi")->fetchAll(PDO::FETCH_ASSOC);


if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baslik = temizle($_POST['baslik']);
    $slug = temizle($_POST['slug']);
    $icerik = $_POST['icerik'];
    $ozet = temizle($_POST['ozet']);
    $kategori_id = (int)$_POST['kategori_id'];
    $durum = $_POST['durum'];
    $yayin_tarihi = !empty($_POST['yayin_tarihi']) ? $_POST['yayin_tarihi'] : null;
    if(empty($slug)) {
        $slug = slug_olustur($baslik);
    }
    $kapak_resmi = '';
    if(isset($_FILES['kapak_resmi']) && $_FILES['kapak_resmi']['error'] == 0) {
        $izin_verilen_uzantilar = ['jpg', 'jpeg', 'png', 'gif'];
        $dosya_uzantisi = strtolower(pathinfo($_FILES['kapak_resmi']['name'], PATHINFO_EXTENSION));
        
        if(in_array($dosya_uzantisi, $izin_verilen_uzantilar)) {
            $yeni_isim = uniqid() . '.' . $dosya_uzantisi;
            $hedef_klasor = '../uploads/';
            
            if(!file_exists($hedef_klasor)) {
                mkdir($hedef_klasor, 0777, true);
            }
            
            if(move_uploaded_file($_FILES['kapak_resmi']['tmp_name'], $hedef_klasor . $yeni_isim)) {
                $kapak_resmi = $yeni_isim;
            }
        }
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO yazilar (
            baslik, 
            slug, 
            icerik, 
            ozet, 
            kategori_id, 
            yazar_id,
            kapak_resmi,
            durum,
            yayin_tarihi
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        $stmt->execute([
            $baslik,
            $slug,
            $icerik,
            $ozet,
            $kategori_id,
            $_SESSION['kullanici_id'],
            $kapak_resmi,
            $durum,
            $yayin_tarihi
        ]);
        
        header('Location: yazilar.php?mesaj=eklendi');
        exit;
    } catch(PDOException $e) {
        $hata = 'Yazı eklenirken bir hata oluştu.';
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
            <h2><i class="fas fa-plus"></i> Yeni Yazı</h2>
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
                    <h3>Yazı Bilgileri</h3>
                </div>
                <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
                    <div class="form-group">
                        <label for="baslik">Başlık</label>
                        <input type="text" id="baslik" name="baslik" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select id="kategori_id" name="kategori_id" required>
                            <option value="">Kategori Seçin</option>
                            <?php foreach($kategoriler as $kategori): ?>
                                <option value="<?php echo $kategori['id']; ?>">
                                    <?php echo $kategori['kategori_adi']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ozet">Özet</label>
                        <textarea id="ozet" name="ozet" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="icerik">İçerik</label>
                        <textarea id="icerik" name="icerik" rows="10" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="kapak_resmi">Kapak Resmi</label>
                        <input type="file" id="kapak_resmi" name="kapak_resmi" accept="image/*">
                        <small>İzin verilen formatlar: JPG, JPEG, PNG, GIF</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="durum">Durum</label>
                        <select id="durum" name="durum" required>
                            <option value="taslak">Taslak</option>
                            <option value="yayinda">Yayında</option>
                            <option value="arsiv">Arşiv</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="yayin_tarihi">Yayın Tarihi</label>
                        <input type="datetime-local" id="yayin_tarihi" name="yayin_tarihi">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="admin-button admin-button-primary">
                            <i class="fas fa-save"></i> Kaydet
                        </button>
                        <a href="yazilar.php" class="admin-button admin-button-secondary">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 