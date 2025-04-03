<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoriler - TechBlog</title>
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
        <section class="kategori-banner">
            <div class="banner-icerik">
                <h1>Kategoriler</h1>
                <p>Teknoloji dünyasının farklı alanlarındaki içeriklerimizi keşfedin</p>
            </div>
        </section>
        <section class="kategoriler-detay">
            <div class="kategori-grid">
                <?php
                include 'config.php';
                $stmt = $conn->prepare("
                    SELECT k.*, COUNT(y.id) as yazi_sayisi 
                    FROM kategoriler k 
                    LEFT JOIN yazilar y ON k.id = y.kategori_id AND y.durum = 'yayinda'
                    GROUP BY k.id 
                    ORDER BY k.kategori_adi ASC
                ");
                $stmt->execute();
                $kategoriler = $stmt->get_result();
                while($kategori = $kategoriler->fetch_assoc()) {
                    $ikon = 'fas fa-folder';
                    switch(strtolower($kategori['kategori_adi'])) {
                        case 'yazılım':
                        case 'programlama':
                        case 'kod':
                            $ikon = 'fas fa-code';
                            break;
                        case 'mobil':
                        case 'uygulama':
                        case 'app':
                            $ikon = 'fas fa-mobile-alt';
                            break;
                        case 'siber güvenlik':
                        case 'güvenlik':
                        case 'hack':
                            $ikon = 'fas fa-shield-alt';
                            break;
                        case 'yapay zeka':
                        case 'ai':
                        case 'makine öğrenmesi':
                            $ikon = 'fas fa-robot';
                            break;
                        case 'bulut teknolojileri':
                        case 'cloud':
                        case 'sunucu':
                            $ikon = 'fas fa-cloud';
                            break;
                        case 'ağ teknolojileri':
                        case 'network':
                        case 'iletişim':
                            $ikon = 'fas fa-network-wired';
                            break;
                        case 'veritabanı':
                        case 'database':
                        case 'sql':
                            $ikon = 'fas fa-database';
                            break;
                        case 'web geliştirme':
                        case 'web':
                        case 'frontend':
                        case 'backend':
                            $ikon = 'fas fa-globe';
                            break;
                        case 'oyun':
                        case 'game':
                        case 'gaming':
                            $ikon = 'fas fa-gamepad';
                            break;
                        case 'donanım':
                        case 'hardware':
                        case 'bilgisayar':
                            $ikon = 'fas fa-microchip';
                            break;
                        case 'blockchain':
                        case 'kripto':
                        case 'bitcoin':
                            $ikon = 'fas fa-coins';
                            break;
                        case 'sosyal medya':
                        case 'sosyal':
                            $ikon = 'fas fa-share-alt';
                            break;
                        case 'e-ticaret':
                        case 'ecommerce':
                            $ikon = 'fas fa-shopping-cart';
                            break;
                        case 'ui/ux':
                        case 'tasarım':
                        case 'design':
                            $ikon = 'fas fa-paint-brush';
                            break;
                        case 'test':
                        case 'testing':
                        case 'qa':
                            $ikon = 'fas fa-vial';
                            break;
                        case 'devops':
                        case 'ci/cd':
                            $ikon = 'fas fa-cogs';
                            break;
                        case 'veri analizi':
                        case 'analytics':
                        case 'big data':
                            $ikon = 'fas fa-chart-bar';
                            break;
                        case 'iot':
                        case 'nesnelerin interneti':
                            $ikon = 'fas fa-microchip';
                            break;
                        case 'ar/vr':
                        case 'sanal gerçeklik':
                            $ikon = 'fas fa-vr-cardboard';
                            break;
                        default:
                            $ikon = 'fas fa-folder';
                    }
                    ?>
                    <div class="kategori-kart">
                        <i class="<?php echo $ikon; ?>"></i>
                        <h3><?php echo htmlspecialchars($kategori['kategori_adi']); ?></h3>
                        <p><?php echo htmlspecialchars($kategori['aciklama']); ?></p>
                        <div class="kategori-meta">
                            <span><?php echo $kategori['yazi_sayisi']; ?> Yazı</span>
                        </div>
                        <a href="kategori.php?slug=<?php echo $kategori['slug']; ?>" class="buton buton-kucuk">Yazıları Gör</a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </section>
        <section class="kategori-istatistikler">
            <div class="istatistik-grid">
                <?php
                $stmt = $conn->prepare("SELECT COUNT(*) as toplam FROM yazilar WHERE durum = 'yayinda'");
                $stmt->execute();
                $toplam_yazi = $stmt->get_result()->fetch_assoc()['toplam'];

                $stmt = $conn->prepare("SELECT COUNT(*) as toplam FROM kullanicilar WHERE rol = 'yazar'");
                $stmt->execute();
                $toplam_yazar = $stmt->get_result()->fetch_assoc()['toplam'];
                $stmt = $conn->prepare("SELECT COUNT(*) as toplam FROM kategoriler");
                $stmt->execute();
                $toplam_kategori = $stmt->get_result()->fetch_assoc()['toplam'];
                ?>
                <div class="istatistik-kart">
                    <i class="fas fa-newspaper"></i>
                    <h3>Toplam Yazı</h3>
                    <p class="sayi"><?php echo $toplam_yazi; ?>+</p>
                </div>
                <div class="istatistik-kart">
                    <i class="fas fa-users"></i>
                    <h3>Yazar Sayısı</h3>
                    <p class="sayi"><?php echo $toplam_yazar; ?>+</p>
                </div>
                <div class="istatistik-kart">
                    <i class="fas fa-folder"></i>
                    <h3>Kategori Sayısı</h3>
                    <p class="sayi"><?php echo $toplam_kategori; ?></p>
                </div>
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
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM kategoriler ORDER BY id DESC LIMIT 4");
                    $stmt->execute();
                    $footer_kategoriler = $stmt->get_result();
                    while($kategori = $footer_kategoriler->fetch_assoc()) {
                        echo '<li><a href="kategori.php?id=' . $kategori['id'] . '"><i class="fas fa-chevron-right"></i> ' . htmlspecialchars($kategori['kategori_adi']) . '</a></li>';
                    }
                    ?>
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