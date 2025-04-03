<?php
require_once 'config.php';
require_once 'ajax.php';

$blog = new Blog($conn);
$site_ayarlari = $blog->ayarlariGetir();
$kategoriler = $blog->kategorileriGetir();

$arama_terimi = isset($_GET['q']) ? trim($_GET['q']) : '';
$arama_sonuclari = null;
if (!empty($arama_terimi)) {
    $arama_sonuclari = $blog->aramaYap($arama_terimi, 20);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Arama Sonuçları - <?php echo $site_ayarlari['site_baslik']; ?></title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
				<li><a href="hakkimizda.php"><i class="fas fa-info-circle"></i> Hakkımızda</a></li>
				<li><a href="iletisim.php"><i class="fas fa-envelope"></i> İletişim</a></li>
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
		<section class="arama-sonuclari">
			<div class="container">
				<h1>Arama Sonuçları</h1>
				
				<div class="arama-formu">
					<form action="arama.php" method="GET">
						<input type="text" name="q" value="<?php echo htmlspecialchars($arama_terimi); ?>" placeholder="Blog yazılarında ara..." required>
						<button type="submit" class="buton buton-ana"><i class="fas fa-search"></i> Ara</button>
					</form>
				</div>
				
				<?php if (!empty($arama_terimi)): ?>
					<div class="arama-bilgi">
						<p>"<strong><?php echo htmlspecialchars($arama_terimi); ?></strong>" için arama sonuçları:</p>
						<p class="arama-aciklama">Arama, başlık, içerik, özet ve yazar bilgilerinde yapılmıştır.</p>
					</div>
					
					<?php if ($arama_sonuclari && $arama_sonuclari->num_rows > 0): ?>
						<div class="arama-sonuc-sayisi">
							<p>Toplam <strong><?php echo $arama_sonuclari->num_rows; ?></strong> sonuç bulundu.</p>
						</div>
						<div class="yazilar-grid">
							<?php while ($yazi = $arama_sonuclari->fetch_assoc()): ?>
								<article class="yazi-karti">
									<div class="yazi-resim" style="background-image: url('<?php echo $yazi['kapak_resmi'] ? 'uploads/' . htmlspecialchars($yazi['kapak_resmi']) : 'assets/images/default.jpg'; ?>');">
									</div>
									<div class="yazi-icerik">
										<h3><?php echo htmlspecialchars($yazi['baslik']); ?></h3>
										<p><?php echo mb_substr(strip_tags($yazi['ozet']), 0, 150) . '...'; ?></p>
										<div class="yazi-meta">
											<span class="yazar"><i class="fas fa-user"></i> <?php echo htmlspecialchars($yazi['yazar_adi']); ?></span>
											<span class="kategori">
												<i class="fas fa-folder"></i> 
												<a href="kategori.php?slug=<?php echo htmlspecialchars($yazi['kategori_slug']); ?>">
													<?php echo htmlspecialchars($yazi['kategori_adi']); ?>
												</a>
											</span>
											<span class="tarih"><i class="fas fa-calendar"></i> <?php echo date('d.m.Y', strtotime($yazi['yayin_tarihi'])); ?></span>
										</div>
										<a href="yazi.php?slug=<?php echo $yazi['slug']; ?>" class="buton buton-devam">Devamını Oku <i class="fas fa-arrow-right"></i></a>
									</div>
								</article>
							<?php endwhile; ?>
						</div>
					<?php else: ?>
						<div class="sonuc-bulunamadi">
							<p>Aramanızla eşleşen sonuç bulunamadı.</p>
							<p>Farklı anahtar kelimelerle tekrar deneyebilirsiniz.</p>
						</div>
					<?php endif; ?>
				<?php endif; ?>
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
                    <li><a href="index.php#hakkimizda"><i class="fas fa-chevron-right"></i> Hakkımızda</a></li>
                    <li><a href="index.php#iletisim"><i class="fas fa-chevron-right"></i> İletişim</a></li>
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
				<div class="sosyal-linkler">
					<?php
					$sosyal_medya = json_decode($site_ayarlari['sosyal_medya'], true);
					if($sosyal_medya):
					?>
					<?php if(isset($sosyal_medya['twitter'])): ?>
						<a href="<?php echo $sosyal_medya['twitter']; ?>" class="sosyal-link"><i class="fab fa-twitter"></i></a>
					<?php endif; ?>
					<?php if(isset($sosyal_medya['facebook'])): ?>
						<a href="<?php echo $sosyal_medya['facebook']; ?>" class="sosyal-link"><i class="fab fa-facebook"></i></a>
					<?php endif; ?>
					<?php if(isset($sosyal_medya['instagram'])): ?>
						<a href="<?php echo $sosyal_medya['instagram']; ?>" class="sosyal-link"><i class="fab fa-instagram"></i></a>
					<?php endif; ?>
					<?php if(isset($sosyal_medya['linkedin'])): ?>
						<a href="<?php echo $sosyal_medya['linkedin']; ?>" class="sosyal-link"><i class="fab fa-linkedin"></i></a>
					<?php endif; ?>
					<?php endif; ?>
				</div>
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