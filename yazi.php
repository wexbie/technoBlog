<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Yazısı - TechBlog</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php
    require_once 'config.php';
    require_once 'ajax.php';
    $blog = new Blog($conn);
    $site_ayarlari = $blog->ayarlariGetir();
    ?>
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
        <div class="container">
            <?php
            if(!isset($_GET['slug']) || empty($_GET['slug'])) {
                header('Location: yazilar.php');
                exit;
            }
            $slug = htmlspecialchars(trim($_GET['slug']), ENT_QUOTES, 'UTF-8');
            $stmt = $conn->prepare("
                SELECT y.*, k.kategori_adi, u.kullaniciadi as yazar_adi, u.rol as yazar_rol
                FROM yazilar y
                LEFT JOIN kategoriler k ON y.kategori_id = k.id
                LEFT JOIN kullanicilar u ON y.yazar_id = u.id
                WHERE y.slug = ? AND y.durum = 'yayinda'
            ");
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $yazi = $stmt->get_result()->fetch_assoc();

            if($yazi) {
                $ip_adresi = $_SERVER['REMOTE_ADDR'];
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as okunma_sayisi 
                    FROM yazi_okunmalar 
                    WHERE yazi_id = ? AND ip_adresi = ? 
                    AND okunma_tarihi >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ");
                $stmt->bind_param("is", $yazi['id'], $ip_adresi);
                $stmt->execute();
                $sonuc = $stmt->get_result()->fetch_assoc();
                if($sonuc['okunma_sayisi'] == 0) {
                    $stmt = $conn->prepare("INSERT INTO yazi_okunmalar (yazi_id, ip_adresi) VALUES (?, ?)");
                    $stmt->bind_param("is", $yazi['id'], $ip_adresi);
                    $stmt->execute();
                }
                $stmt = $conn->prepare("SELECT COUNT(*) as toplam_okunma FROM yazi_okunmalar WHERE yazi_id = ?");
                $stmt->bind_param("i", $yazi['id']);
                $stmt->execute();
                $okunma = $stmt->get_result()->fetch_assoc()['toplam_okunma'];
                ?>
                <article class="yazi-detay">
                    <div class="yazi-baslik">
                        <h1><?php echo htmlspecialchars($yazi['baslik']); ?></h1>
                        <div class="yazi-meta">
                            <span class="yazar"><i class="fas fa-user"></i> <?php echo htmlspecialchars($yazi['yazar_adi']); ?></span>
                            <span class="kategori"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($yazi['kategori_adi']); ?></span>
                            <span class="tarih"><i class="fas fa-calendar"></i> <?php echo date('d.m.Y', strtotime($yazi['yayin_tarihi'])); ?></span>
                            <span class="okunma"><i class="fas fa-eye"></i> <?php echo $okunma; ?> okunma</span>
                        </div>
                    </div>
                    <?php if($yazi['kapak_resmi']): ?>
                    <div class="yazi-kapak">
                        <img src="uploads/<?php echo htmlspecialchars($yazi['kapak_resmi']); ?>" alt="<?php echo htmlspecialchars($yazi['baslik']); ?>">
                    </div>
                    <?php endif; ?>
                    <div class="yazi-icerik">
                        <?php echo $yazi['icerik']; ?>
                    </div>

                    <div class="yazi-paylasim">
                        <h3><i class="fas fa-share-alt"></i> Paylaş</h3>
                        <div class="paylasim-butonlari">
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($yazi['baslik']); ?>" class="paylasim-buton twitter" target="_blank"><i class="fab fa-twitter"></i> Twitter</a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="paylasim-buton facebook" target="_blank"><i class="fab fa-facebook"></i> Facebook</a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&title=<?php echo urlencode($yazi['baslik']); ?>" class="paylasim-buton linkedin" target="_blank"><i class="fab fa-linkedin"></i> LinkedIn</a>
                        </div>
                    </div>

                    <div class="yorumlar">
                        <h3><i class="fas fa-comments"></i> Yorumlar</h3>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="yorum-form">
                            <div id="yorum-mesaj" class="alert" style="display: none;"></div>
                            <form id="yorumForm">
                                <input type="hidden" name="yazi_id" value="<?php echo $yazi['id']; ?>">
                                <textarea name="yorum" placeholder="Yorumunuzu yazın..." required></textarea>
                                <button type="submit" class="buton buton-ana">Yorum Yap</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="yorum-uyari">
                            <p>Yorum yapabilmek için <a href="giris.php">giriş yapın</a> veya <a href="kayit.php">kayıt olun</a>.</p>
                        </div>
                        <?php endif; ?>
                        <div class="yorum-listesi">
                            <?php
                            $stmt = $conn->prepare("
                                SELECT y.*, u.kullaniciadi, u.rol
                                FROM yorumlar y
                                LEFT JOIN kullanicilar u ON y.kullanici_id = u.id
                                WHERE y.yazi_id = ? AND y.durum = 'onaylandi'
                                ORDER BY y.olusturma_tarihi DESC
                            ");
                            $stmt->bind_param("i", $yazi['id']);
                            $stmt->execute();
                            $yorumlar = $stmt->get_result();
                            if($yorumlar->num_rows > 0) {
                                while($yorum = $yorumlar->fetch_assoc()) {
                                    echo '<div class="yorum">';
                                    echo '<div class="yorum-baslik">';
                                    echo '<span class="yorum-yazar">' . htmlspecialchars($yorum['kullaniciadi']);
                                    if($yorum['rol'] == 'admin') {
                                        echo ' <span class="yorum-rol">Admin</span>';
                                    }
                                    if($yorum['rol'] == 'editor') {
                                        echo ' <span class="yorum-rol">Editör</span>';
                                    }
                                    echo '</span>';
                                    echo '<span class="yorum-tarih">' . date('d.m.Y H:i', strtotime($yorum['olusturma_tarihi'])) . '</span>';
                                    echo '</div>';
                                    echo '<div class="yorum-icerik">' . nl2br(htmlspecialchars($yorum['yorum'])) . '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="yorum-yok">Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>';
                            }
                            ?>
                        </div>
                    </div>
                </article>
                <?php
            } else {
                echo '<div class="hata-mesaji">';
                echo '<i class="fas fa-exclamation-circle"></i>';
                echo '<h2>Yazı Bulunamadı</h2>';
                echo '<p>Aradığınız yazı bulunamadı veya silinmiş olabilir.</p>';
                echo '<a href="yazilar.php" class="buton buton-ana">Yazılara Dön</a>';
                echo '</div>';
            }
            ?>
        </div>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const yorumForm = document.getElementById('yorumForm');
        if (yorumForm) {
            yorumForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('islem', 'yorum_ekle');
                
                fetch('ajax.php', {
                    method: 'POST',
                    body: formData
                }) .then(response => response.json())
                .then(data => {
                    const mesajDiv = document.getElementById('yorum-mesaj');
                    mesajDiv.style.display = 'block';
                    
                    if (data.basarili) {
                        mesajDiv.className = 'alert alert-success';
                        yorumForm.reset();
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        mesajDiv.className = 'alert alert-danger';
                    }
                    
                    mesajDiv.textContent = data.mesaj;
                })
                .catch(error => {
                    console.error('Hata:', error);
                    const mesajDiv = document.getElementById('yorum-mesaj');
                    mesajDiv.style.display = 'block';
                    mesajDiv.className = 'alert alert-danger';
                    mesajDiv.textContent = 'Bir hata oluştu!';
                });
            });
        }
    });
    </script>
</body>
</html> 