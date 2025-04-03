<?php
require_once 'config.php';
require_once 'ajax.php';

$blog = new Blog($conn);
$site_ayarlari = $blog->ayarlariGetir();
$kategoriler = $blog->kategorileriGetir();
$son_yazilar = $blog->yazilariGetir(6, 0);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $site_ayarlari['site_baslik']; ?></title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<script src="js/arama.js"></script>
</head>
<body>
	<header>
		<nav class="ustmenu">
			<div class="logo">
				<i class="fas fa-laptop-code"></i>
				<h1><?php echo $site_ayarlari['site_baslik']; ?></h1>
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
		<section class="banner">
			<div class="banner-icerik">
				<h1><?php echo $site_ayarlari['site_baslik']; ?></h1>
				<p><?php echo $site_ayarlari['site_aciklama']; ?></p>
				<div class="banner-arama">
					<form id="arama-formu" action="arama.php" method="GET">
						<input type="text" name="q" id="arama-kutusu" placeholder="Blog yazılarında ara..." required>
						<button type="submit" class="buton buton-ana"><i class="fas fa-search"></i></button>
					</form>
				</div>
			</div>
		</section>

		<section id="kategoriler" class="kategoriler">
			<div class="kategori-grid">
				<?php while ($kategori = $kategoriler->fetch_assoc()):
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
					<h3><?php echo $kategori['kategori_adi']; ?></h3>
					<p><?php echo $kategori['aciklama']; ?></p>
					<a href="kategori.php?slug=<?php echo $kategori['slug']; ?>" class="buton buton-kucuk">Yazıları Gör</a>
				</div>
				<?php endwhile; ?>
			</div>
		</section>

		<section id="yazilar" class="blog-yazilari">
			<div class="bolum-baslik">
				<h2>Son Yazılar</h2>
				<div class="filtreler">
					<a href="yazilar.php" class="buton buton-kucuk aktif">Tümü</a>
					<?php 
					$kategoriler->data_seek(0);
					while ($kategori = $kategoriler->fetch_assoc()): 
					?>
						<a href="kategori.php?slug=<?php echo $kategori['slug']; ?>" class="buton buton-kucuk">
							<?php echo $kategori['kategori_adi']; ?>
						</a>
					<?php endwhile; ?>
				</div>
			</div>
			<div class="yazilar-grid">
				<?php 
				if ($son_yazilar && $son_yazilar->num_rows > 0):
					while ($yazi = $son_yazilar->fetch_assoc()): 
				?>
				<article class="yazi-karti">
					<div class="yazi-resim" style="background-image: url('<?php echo $yazi['kapak_resmi'] ? 'uploads/' . htmlspecialchars($yazi['kapak_resmi']) : 'assets/images/default.jpg'; ?>');">
					</div>
					<div class="yazi-icerik">
						<h3><?php echo htmlspecialchars($yazi['baslik']); ?></h3>
						<p><?php echo mb_substr(strip_tags($yazi['ozet']), 0, 150) . '...'; ?></p>
						<div class="yazi-meta">
							<span class="yazar"><i class="fas fa-user"></i> <?php echo htmlspecialchars($yazi['yazar_adi']); ?></span>
							<span class="kategori"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($yazi['kategori_adi']); ?></span>
							<span class="tarih"><i class="fas fa-calendar"></i> <?php echo date('d.m.Y', strtotime($yazi['yayin_tarihi'])); ?></span>
						</div>
						<a href="yazi.php?slug=<?php echo $yazi['slug']; ?>" class="buton buton-devam">Devamını Oku <i class="fas fa-arrow-right"></i></a>
					</div>
				</article>
				<?php 
					endwhile;
				else:
				?>
					<div class="yazi-bulunamadi">
						<p>Henüz yayınlanmış yazı bulunmamaktadır.</p>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<section id="hakkimizda" class="hakkimizda">
			<div class="hakkimizda-icerik">
				<div class="hakkimizda-metin">
					<h2>Hakkımızda</h2>
					<p><?php echo $site_ayarlari['site_aciklama']; ?></p>
					<div class="istatistikler">
						<?php
						$istatistikler = $blog->istatistikleriCek();
						?>
						<div class="istatistik">
							<span class="sayi"><?php echo $istatistikler['toplam_yazi']; ?></span>
							<span class="etiket">Blog Yazısı</span>
						</div>
						<div class="istatistik">
							<span class="sayi"><?php echo $istatistikler['toplam_yazar']; ?></span>
							<span class="etiket">Yazar</span>
						</div>
					</div>
				</div>
				<div class="hakkimizda-gorsel">
					<i class="fas fa-rocket"></i>
				</div>
			</div>
		</section>

		<section id="iletisim" class="iletisim">
			<div class="iletisim-icerik">
				<div class="iletisim-bilgi">
					<h2>İletişime Geçin</h2>
					<p>Sorularınız veya önerileriniz için bizimle iletişime geçebilirsiniz.</p>
				</div>
				<form class="iletisim-form" method="POST" action="iletisim.php">
					<input type="text" name="ad" placeholder="Adınız" required>
					<input type="email" name="email" placeholder="E-posta Adresiniz" required>
					<textarea name="mesaj" placeholder="Mesajınız" required></textarea>
					<button type="submit" class="buton buton-ana"><i class="fas fa-paper-plane"></i> Gönder</button>
				</form>
			</div>
		</section>
	</main>

	<footer>
		<div class="altbilgi-icerik">
			<div class="altbilgi-bolum">
				<div class="logo">
					<i class="fas fa-laptop-code"></i>
					<h3><?php echo $site_ayarlari['site_baslik']; ?></h3>
				</div>
				<p><?php echo $site_ayarlari['site_aciklama']; ?></p>
			</div>
			<div class="altbilgi-bolum">
				<h3>Hızlı Bağlantılar</h3>
				<ul>
					<li><a href="yazilar.php"><i class="fas fa-chevron-right"></i> Yazılar</a></li>
					<li><a href="kategoriler.php"><i class="fas fa-chevron-right"></i> Kategoriler</a></li>
					<li><a href="#yazilar"><i class="fas fa-chevron-right"></i> Son Yazılar</a></li>
					<li><a href="#kategoriler"><i class="fas fa-chevron-right"></i> Kategoriler</a></li>
					<li><a href="#hakkimizda"><i class="fas fa-chevron-right"></i> Hakkımızda</a></li>
					<li><a href="#iletisim"><i class="fas fa-chevron-right"></i> İletişim</a></li>
				</ul>
			</div>
			<div class="altbilgi-bolum">
				<h3>Kategoriler</h3>
				<ul>
					<?php 
					$kategoriler->data_seek(0);
					while ($kategori = $kategoriler->fetch_assoc()): 
					?>
						<li><a href="kategori.php?slug=<?php echo $kategori['slug']; ?>">
							<i class="fas fa-chevron-right"></i> <?php echo $kategori['kategori_adi']; ?>
						</a></li>
					<?php endwhile; ?>
				</ul>
			</div>
			<div class="altbilgi-bolum">
				<h3>Bizi Takip Edin</h3>
				<div class="bulten">
					<h4>Bültenimize Katılın</h4>
					<form class="bulten-form" method="POST" action="bulten.php">
						<input type="email" name="email" placeholder="E-posta adresiniz" required>
						<button type="submit" class="buton buton-kucuk">Katıl</button>
					</form>
				</div>
			</div>
		</div>
		<div class="altbilgi-alt">
			<p>&copy; <?php echo date('Y'); ?> <?php echo $site_ayarlari['site_baslik']; ?>. Tüm hakları saklıdır.</p>
		</div>
	</footer>
</body>
</html>