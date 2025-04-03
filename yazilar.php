<?php
include 'config.php';
$arama = '';
$kategori = 0;
$siralama = 'yeni';
$sayfa = 1;
$limit = 9;
$offset = 0;
if(isset($_GET['arama'])) {
    $arama = htmlspecialchars(trim($_GET['arama']), ENT_QUOTES, 'UTF-8');
}
if(isset($_GET['kategori'])) {
    $kategori = (int)$_GET['kategori'];
}
if(isset($_GET['siralama'])) {
    $siralama = htmlspecialchars(trim($_GET['siralama']), ENT_QUOTES, 'UTF-8');
}
if(isset($_GET['sayfa'])) {
    $sayfa = (int)$_GET['sayfa'];
    if($sayfa < 1) $sayfa = 1;
}
$offset = ($sayfa - 1) * $limit;
$stmt = $conn->prepare("SELECT id, kategori_adi, slug FROM kategoriler WHERE durum = 1 ORDER BY kategori_adi");
$stmt->execute();
$kategoriler = $stmt->get_result();
$stmt = $conn->prepare("
    SELECT y.id, y.baslik, y.slug
    FROM yazilar y
    WHERE y.durum = 'yayinda'
    ORDER BY y.yayin_tarihi DESC
    LIMIT 5
");
$stmt->execute();
$populer_yazilar = $stmt->get_result();
$siralama_secenekleri = [
    'yeni' => 'y.yayin_tarihi DESC',
    'eski' => 'y.yayin_tarihi ASC'
];
$siralama_sql = isset($siralama_secenekleri[$siralama]) ? $siralama_secenekleri[$siralama] : 'y.yayin_tarihi DESC';
$sql = "
    SELECT y.id, y.baslik, y.slug, y.ozet, y.kapak_resmi, y.yayin_tarihi,
           k.kategori_adi, u.kullaniciadi as yazar_adi
    FROM yazilar y
    LEFT JOIN kategoriler k ON y.kategori_id = k.id
    LEFT JOIN kullanicilar u ON y.yazar_id = u.id
    WHERE y.durum = 'yayinda'
";

$params = [];
$types = "";

if($arama) {
    $sql .= " AND (y.baslik LIKE ? OR y.ozet LIKE ? OR y.icerik LIKE ?)";
    $arama_param = "%$arama%";
    $params[] = $arama_param;
    $params[] = $arama_param;
    $params[] = $arama_param;
    $types .= "sss";
}
if($kategori) {
    $sql .= " AND y.kategori_id = ?";
    $params[] = $kategori;
    $types .= "i";
}
$sql .= " ORDER BY $siralama_sql LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";
$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$yazilar = $stmt->get_result();
$count_sql = "SELECT COUNT(*) as total FROM yazilar y WHERE y.durum = 'yayinda'";
if($arama) {
    $count_sql .= " AND (y.baslik LIKE ? OR y.ozet LIKE ? OR y.icerik LIKE ?)";
}
if($kategori) {
    $count_sql .= " AND y.kategori_id = ?";
}
$stmt = $conn->prepare($count_sql);
if($arama || $kategori) {
    $count_params = [];
    $count_types = "";
    if($arama) {
        $arama_param = "%$arama%";
        $count_params[] = $arama_param;
        $count_params[] = $arama_param;
        $count_params[] = $arama_param;
        $count_types .= "sss";
    }
    if($kategori) {
        $count_params[] = $kategori;
        $count_types .= "i";
    }
    $stmt->bind_param($count_types, ...$count_params);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$toplam_yazi = isset($row['total']) ? (int)$row['total'] : 0;
$toplam_sayfa = ceil($toplam_yazi / $limit);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Yazıları - TechBlog</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
		<nav class="ustmenu">
			<div class="logo">
				<i class="fas fa-laptop-code"></i>
				<h1><?php require_once 'ajax.php'; $blog = new Blog($conn); $site_ayarlari = $blog->ayarlariGetir(); echo $site_ayarlari['site_baslik']; ?></h1>
			</div>
			<ul class="menulinkler">
				<li><a href="index.php"><i class="fas fa-home"></i> Ana Sayfa</a></li>
				<li><a href="yazilar.php"><i class="fas fa-newspaper"></i> Yazılar</a></li>
				<li><a href="kategoriler.php"><i class="fas fa-tags"></i> Kategoriler</a></li>
			</ul>
			<div class="girisbutonlari">
				<?php if(isset($_SESSION['user_id'])): ?>
					<a href="profil.php" class="buton buton-giris"><i class="fas fa-user"></i> <?php echo $_SESSION['kullanici_adi']; ?></a>
					<a href="cikis.php" class="buton buton-kayit"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
				<?php else: ?>
					<a href="giris.php" class="buton buton-giris"><i class="fas fa-sign-in-alt"></i> Giriş Yap</a>
					<a href="kayit.php" class="buton buton-kayit"><i class="fas fa-user-plus"></i> Kayıt Ol</a>
				<?php endif; ?>
			</div>
		</nav>
	</header>

    <main>
        <section class="yazilar-banner">
            <div class="banner-icerik">
                <h1>Blog Yazıları</h1>
                <p>Teknoloji dünyasından en güncel içerikler</p>
            </div>
        </section>

        <section class="yazilar-container">
            <div class="yazilar-sidebar">
                <div class="arama-kutusu">
                    <h3><i class="fas fa-search"></i> Arama</h3>
                    <form action="yazilar.php" method="GET">
                        <input type="text" name="arama" value="<?php echo htmlspecialchars($arama); ?>" placeholder="Yazılarda ara...">
                        <?php if($kategori): ?>
                        <input type="hidden" name="kategori" value="<?php echo $kategori; ?>">
                        <?php endif; ?>
                        <button type="submit" class="buton buton-kucuk">Ara</button>
                    </form>
                </div>

                <div class="kategori-filtre">
                    <h3><i class="fas fa-tags"></i> Kategoriler</h3>
                    <ul>
                        <li><a href="yazilar.php<?php echo $arama ? '?arama=' . urlencode($arama) : ''; ?>" class="<?php echo !$kategori ? 'aktif' : ''; ?>">Tümü</a></li>
                        <?php while($kategori_row = $kategoriler->fetch_assoc()): ?>
                        <li>
                            <a href="yazilar.php?kategori=<?php echo $kategori_row['id']; ?><?php echo $arama ? '&arama=' . urlencode($arama) : ''; ?>" 
                               class="<?php echo $kategori == $kategori_row['id'] ? 'aktif' : ''; ?>">
                                <?php echo htmlspecialchars($kategori_row['kategori_adi']); ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="populer-yazilar">
                    <h3><i class="fas fa-fire"></i> Popüler Yazılar</h3>
                    <ul>
                        <?php while($populer = $populer_yazilar->fetch_assoc()): ?>
                        <li>
                            <a href="yazi.php?slug=<?php echo htmlspecialchars($populer['slug']); ?>">
                                <i class="fas fa-chevron-right"></i> <?php echo htmlspecialchars($populer['baslik']); ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <div class="yazilar-icerik">
                <div class="filtreler">
                    <form action="yazilar.php" method="GET" class="siralama-form">
                        <?php if($arama): ?>
                        <input type="hidden" name="arama" value="<?php echo htmlspecialchars($arama); ?>">
                        <?php endif; ?>
                        <?php if($kategori): ?>
                        <input type="hidden" name="kategori" value="<?php echo $kategori; ?>">
                        <?php endif; ?>
                        <select name="siralama" class="siralama-select" onchange="this.form.submit()">
                            <option value="yeni" <?php echo $siralama == 'yeni' ? 'selected' : ''; ?>>En Yeni</option>
                            <option value="eski" <?php echo $siralama == 'eski' ? 'selected' : ''; ?>>En Eski</option>
                        </select>
                    </form>
                </div>

                <div class="yazilar-grid">
                    <?php if($yazilar->num_rows > 0): ?>
                        <?php while($yazi = $yazilar->fetch_assoc()): ?>
                        <article class="yazi-karti">
                            <?php if($yazi['kapak_resmi']): ?>
                            <div class="yazi-resim" style="background-image: url('uploads/<?php echo htmlspecialchars($yazi['kapak_resmi']); ?>')">
                            </div>
                            <?php else: ?>
                            <div class="yazi-resim">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <?php endif; ?>
                            <div class="yazi-icerik">
                                <h3><?php echo htmlspecialchars($yazi['baslik']); ?></h3>
                                <p><?php echo mb_substr(strip_tags(htmlspecialchars($yazi['ozet'])), 0, 150); ?>...</p>
                                <div class="yazi-meta">
                                    <span class="yazar"><i class="fas fa-user"></i> <?php echo htmlspecialchars($yazi['yazar_adi']); ?></span>
                                    <span class="kategori"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($yazi['kategori_adi']); ?></span>
                                    <span class="tarih"><i class="fas fa-calendar"></i> <?php echo date('d.m.Y', strtotime($yazi['yayin_tarihi'])); ?></span>
                                </div>
                                <a href="yazi.php?slug=<?php echo htmlspecialchars($yazi['slug']); ?>" class="buton buton-devam">Devamını Oku <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </article>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="hata-mesaji">
                            <i class="fas fa-info-circle"></i>
                            <h2>Yazı Bulunamadı</h2>
                            <p>Aradığınız kriterlere uygun yazı bulunamadı.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($toplam_sayfa > 1): ?>
                <div class="sayfalama">
                    <?php if($sayfa > 1): ?>
                        <a href="?sayfa=<?php echo $sayfa-1; ?><?php echo $arama ? '&arama=' . urlencode($arama) : ''; ?><?php echo $kategori ? '&kategori=' . $kategori : ''; ?><?php echo $siralama != 'yeni' ? '&siralama=' . $siralama : ''; ?>" class="buton buton-sayfa">
                            <i class="fas fa-chevron-left"></i> Önceki
                        </a>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $toplam_sayfa; $i++): ?>
                        <a href="?sayfa=<?php echo $i; ?><?php echo $arama ? '&arama=' . urlencode($arama) : ''; ?><?php echo $kategori ? '&kategori=' . $kategori : ''; ?><?php echo $siralama != 'yeni' ? '&siralama=' . $siralama : ''; ?>" 
                           class="buton buton-sayfa <?php echo $i == $sayfa ? 'aktif' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($sayfa < $toplam_sayfa): ?>
                        <a href="?sayfa=<?php echo $sayfa+1; ?><?php echo $arama ? '&arama=' . urlencode($arama) : ''; ?><?php echo $kategori ? '&kategori=' . $kategori : ''; ?><?php echo $siralama != 'yeni' ? '&siralama=' . $siralama : ''; ?>" class="buton buton-sayfa">
                            Sonraki <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="altbilgi-icerik">
            <div class="altbilgi-bolum">
                <div class="logo">
                    <i class="fas fa-laptop-code"></i>
                    <h3>TechBlog</h3>
                </div>
                <p>Teknoloji dünyasının en güncel haberleri ve içerikleri.</p>
            </div>
            <div class="altbilgi-bolum">
                <h3>Hızlı Bağlantılar</h3>
                <ul>
                    <li><a href="yazilar.php"><i class="fas fa-chevron-right"></i> Yazılar</a></li>
                    <li><a href="kategoriler.php"><i class="fas fa-chevron-right"></i> Kategoriler</a></li>
                    <li><a href="index.php#hakkimizda"><i class="fas fa-chevron-right"></i> Hakkımızda</a></li>
                    <li><a href="index.php#iletisim"><i class="fas fa-chevron-right"></i> İletişim</a></li>
                </ul>
            </div>
            <div class="altbilgi-bolum">
                <h3>Kategoriler</h3>
                <ul>
                    <li><a href="kategori.php?id=1"><i class="fas fa-chevron-right"></i> Yazılım</a></li>
                    <li><a href="kategori.php?id=2"><i class="fas fa-chevron-right"></i> Mobil</a></li>
                    <li><a href="kategori.php?id=3"><i class="fas fa-chevron-right"></i> Güvenlik</a></li>
                    <li><a href="kategori.php?id=4"><i class="fas fa-chevron-right"></i> Yapay Zeka</a></li>
                </ul>
            </div>
            <div class="altbilgi-bolum">
                <h3>Bizi Takip Edin</h3>
                <div class="sosyal-linkler">
                    <a href="#" class="sosyal-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="sosyal-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="sosyal-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="sosyal-link"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="altbilgi-alt">
            <p>&copy; <?php echo date('Y'); ?> TechBlog. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html> 