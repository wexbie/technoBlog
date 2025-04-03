<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - TechBlog</title>
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
        <?php
        include 'config.php';
        
        if(!isset($_GET['slug']) || empty($_GET['slug'])) {
            header('Location: kategoriler.php');
            exit;
        }
        $slug = htmlspecialchars(trim($_GET['slug']), ENT_QUOTES, 'UTF-8');
        $stmt = $conn->prepare("SELECT id, kategori_adi, aciklama FROM kategoriler WHERE slug = ? AND durum = 1");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $kategori = $stmt->get_result()->fetch_assoc();
        if($kategori) {
            $sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
            $limit = 6;
            $offset = ($sayfa - 1) * $limit;
            $count_stmt = $conn->prepare("
                SELECT COUNT(*) as toplam 
                FROM yazilar 
                WHERE kategori_id = ? AND durum = 'yayinda'
            ");
            $count_stmt->bind_param("i", $kategori['id']);
            $count_stmt->execute();
            $toplam_yazi = $count_stmt->get_result()->fetch_assoc()['toplam'];
            $toplam_sayfa = ceil($toplam_yazi / $limit);
            $stmt = $conn->prepare("
                SELECT y.id, y.baslik, y.slug, y.ozet, y.kapak_resmi, y.yayin_tarihi,
                       k.kategori_adi, u.kullaniciadi as yazar_adi 
                FROM yazilar y 
                LEFT JOIN kategoriler k ON y.kategori_id = k.id 
                LEFT JOIN kullanicilar u ON y.yazar_id = u.id 
                WHERE y.kategori_id = ? AND y.durum = 'yayinda' 
                ORDER BY y.yayin_tarihi DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("iii", $kategori['id'], $limit, $offset);
            $stmt->execute();
            $yazilar = $stmt->get_result();
            ?>
            
            <section class="kategori-banner">
                <div class="banner-icerik">
                    <h1><?php echo htmlspecialchars($kategori['kategori_adi']); ?></h1>
                    <p><?php echo htmlspecialchars($kategori['aciklama']); ?></p>
                </div>
            </section>

            <section class="blog-yazilari">
                <div class="yazilar-grid">
                    <?php 
                    if($yazilar->num_rows > 0) {
                        while ($yazi = $yazilar->fetch_assoc()): 
                    ?>
                    <article class="yazi-karti">
                        <div class="yazi-resim" style="background-image: <?php echo $yazi['kapak_resmi'] ? 'url(uploads/' . htmlspecialchars($yazi['kapak_resmi']) . ')' : 'linear-gradient(rgba(128, 128, 128, 0.3), rgba(128, 128, 128, 0.3))'; ?>; backdrop-filter: blur(20px); height: 200px;">
                        </div>
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
                    <?php 
                        endwhile;
                    } else {
                        echo '<div class="hata-mesaji">';
                        echo '<i class="fas fa-info-circle"></i>';
                        echo '<h2>Henüz Yazı Bulunmuyor</h2>';
                        echo '<p>Bu kategoride henüz yayınlanmış yazı bulunmamaktadır.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <?php if($toplam_sayfa > 1): ?>
                <div class="sayfalama">
                    <?php if($sayfa > 1): ?>
                        <a href="?slug=<?php echo $slug; ?>&sayfa=<?php echo $sayfa-1; ?>" class="buton buton-sayfa"><i class="fas fa-chevron-left"></i> Önceki</a>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $toplam_sayfa; $i++): ?>
                        <a href="?slug=<?php echo $slug; ?>&sayfa=<?php echo $i; ?>" class="buton buton-sayfa <?php echo $i == $sayfa ? 'aktif' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if($sayfa < $toplam_sayfa): ?>
                        <a href="?slug=<?php echo $slug; ?>&sayfa=<?php echo $sayfa+1; ?>" class="buton buton-sayfa">Sonraki <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
            <?php
        } else {
            echo '<div class="hata-mesaji">';
            echo '<i class="fas fa-exclamation-circle"></i>';
            echo '<h2>Kategori Bulunamadı</h2>';
            echo '<p>Aradığınız kategori bulunamadı veya silinmiş olabilir.</p>';
            echo '<a href="kategoriler.php" class="buton buton-ana">Kategorilere Dön</a>';
            echo '</div>';
        }
        ?>
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
                    <li><a href="kategori.php?slug=yazilim"><i class="fas fa-chevron-right"></i> Yazılım</a></li>
                    <li><a href="kategori.php?slug=mobil"><i class="fas fa-chevron-right"></i> Mobil</a></li>
                    <li><a href="kategori.php?slug=guvenlik"><i class="fas fa-chevron-right"></i> Güvenlik</a></li>
                    <li><a href="kategori.php?slug=yapay-zeka"><i class="fas fa-chevron-right"></i> Yapay Zeka</a></li>
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