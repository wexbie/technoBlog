<?php
session_start();
require_once 'config.php';
class GirisYap {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function girisYap($kullanici_adi, $sifre, $beni_hatirla = false) {
        $stmt = $this->conn->prepare("SELECT * FROM kullanicilar WHERE kullaniciadi = ? AND durum = 1");
        $stmt->bind_param("s", $kullanici_adi);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($sifre, $user['kullaniciparolasi'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['kullanici_adi'] = $user['kullaniciadi'];
                $_SESSION['rol'] = $user['rol'];
                if ($beni_hatirla) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (86400 * 10);
                    setcookie('remember_token', $token, $expires, '/', '', true, true);
                    
                    $stmt = $this->conn->prepare("UPDATE kullanicilar SET remember_token = ? WHERE id = ?");
                    $stmt->bind_param("si", $token, $user['id']);
                    $stmt->execute();
                } return ['basarili' => true, 'mesaj' => 'Giriş başarılı!'];
            }
        } return ['basarili' => false, 'mesaj' => 'Geçersiz kullanıcı adı veya şifre!'];
    }
    public function cikisYap() {
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        return ['basarili' => true, 'mesaj' => 'Çıkış yapıldı!'];
    }
    public function oturumKontrol() {
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        if (isset($_COOKIE['remember_token'])) {
            $stmt = $this->conn->prepare("SELECT * FROM kullanicilar WHERE remember_token = ?");
            $stmt->bind_param("s", $_COOKIE['remember_token']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['kullanici_adi'] = $user['kullaniciadi'];
                $_SESSION['rol'] = $user['rol'];
                return true;
            }
        } return false;
    }
}
class KayitOl {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function kayitOl($kullanici_adi, $sifre, $adisoyadi, $email) {
        $stmt = $this->conn->prepare("SELECT * FROM kullanicilar WHERE kullaniciadi = ? OR email = ?");
        $stmt->bind_param("ss", $kullanici_adi, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ['basarili' => false, 'mesaj' => 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor!'];
        }
        if ($sifre !== $_POST['sifre_tekrar']) {
            return ['basarili' => false, 'mesaj' => 'Şifreler eşleşmiyor!'];
        }
        
        $hash = password_hash($sifre, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO kullanicilar (kullaniciadi, kullaniciparolasi, adisoyadi, email, rol, durum) VALUES (?, ?, ?, ?, 'yazar', 1)");
        $stmt->bind_param("ssss", $kullanici_adi, $hash, $adisoyadi, $email);
        
        if ($stmt->execute()) {
            return ['basarili' => true, 'mesaj' => 'Kayıt başarılı! Giriş yapabilirsiniz.'];
        } else {
            return ['basarili' => false, 'mesaj' => 'Kayıt sırasında bir hata oluştu!'];
        }
    }
}
class Blog {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function kullaniciGiris($kullanici_adi, $sifre) {
        $stmt = $this->conn->prepare("SELECT * FROM kullanicilar WHERE kullaniciadi = ? AND durum = 1");
        $stmt->bind_param("s", $kullanici_adi);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($sifre, $user['kullaniciparolasi'])) { return $user; }
        } return false;
    }
    public function kullaniciKayit($kullanici_adi, $sifre, $adisoyadi, $email, $rol = 'yazar') {
        $hash = password_hash($sifre, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO kullanicilar (kullaniciadi, kullaniciparolasi, adisoyadi, email, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $kullanici_adi, $hash, $adisoyadi, $email, $rol);
        return $stmt->execute();
    }
    public function yazilariGetir($limit = 10, $offset = 0, $kategori_id = null) {
        try {
            $sql = "SELECT y.*, k.kategori_adi, u.kullaniciadi as yazar_adi 
                    FROM yazilar y 
                    LEFT JOIN kategoriler k ON y.kategori_id = k.id 
                    LEFT JOIN kullanicilar u ON y.yazar_id = u.id 
                    WHERE y.durum = 'yayinda'";
            if ($kategori_id) {
                $sql .= " AND y.kategori_id = ?";
            }

            $sql .= " ORDER BY y.yayin_tarihi DESC LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                return false;
            }
            if ($kategori_id) {
                $stmt->bind_param("iii", $kategori_id, $limit, $offset);
            } else {
                $stmt->bind_param("ii", $limit, $offset);
            }
            if (!$stmt->execute()) {
                return false;
            }
            $result = $stmt->get_result();
            
            if (!$result) {
                return false;
            } return $result;
        } catch (Exception $e) {
            error_log("Hata: " . $e->getMessage());
            return false;
        }
    }
    public function yaziGetir($slug) {
        $stmt = $this->conn->prepare("SELECT y.*, k.kategori_adi, u.kullaniciadi as yazar_adi 
        FROM yazilar y 
        LEFT JOIN kategoriler k ON y.kategori_id = k.id 
        LEFT JOIN kullanicilar u ON y.yazar_id = u.id 
        WHERE y.slug = ? AND y.durum = 'yayinda'");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function yaziEkle($baslik, $slug, $icerik, $ozet, $kategori_id, $yazar_id, $kapak_resmi = null) {
        $stmt = $this->conn->prepare("INSERT INTO yazilar (baslik, slug, icerik, ozet, kategori_id, yazar_id, kapak_resmi) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiis", $baslik, $slug, $icerik, $ozet, $kategori_id, $yazar_id, $kapak_resmi);
        return $stmt->execute();
    }
    public function kategorileriGetir() {
        $stmt = $this->conn->prepare("SELECT * FROM kategoriler WHERE durum = 1 ORDER BY kategori_adi");
        $stmt->execute();
        return $stmt->get_result();
    }
    public function kategoriGetir($slug) {
        $stmt = $this->conn->prepare("SELECT * FROM kategoriler WHERE slug = ? AND durum = 1");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function yorumlariGetir($yazi_id) {
        $stmt = $this->conn->prepare("SELECT y.*, u.kullaniciadi 
        FROM yorumlar y 
        LEFT JOIN kullanicilar u ON y.kullanici_id = u.id 
        WHERE y.yazi_id = ? AND y.durum = 'onaylandi' 
        ORDER BY y.olusturma_tarihi DESC");
        $stmt->bind_param("i", $yazi_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function yorumEkle($yazi_id, $kullanici_id, $yorum) {
        $stmt = $this->conn->prepare("SELECT rol FROM kullanicilar WHERE id = ?");
        $stmt->bind_param("i", $kullanici_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $kullanici = $result->fetch_assoc();
        
        // spam yorumu önler
        $durum = ($kullanici['rol'] == 'admin' || $kullanici['rol'] == 'editor') ? 'onaylandi' : 'beklemede';
        
        $stmt = $this->conn->prepare("INSERT INTO yorumlar (yazi_id, kullanici_id, yorum, durum, olusturma_tarihi) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiss", $yazi_id, $kullanici_id, $yorum, $durum);
        return $stmt->execute();
    }
    public function ayarlariGetir() {
        $stmt = $this->conn->prepare("SELECT * FROM ayarlar ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function ayarGuncelle($site_baslik, $site_aciklama, $site_logo, $site_email, $sosyal_medya, $iletisim_bilgileri) {
        $stmt = $this->conn->prepare("INSERT INTO ayarlar (site_baslik, site_aciklama, site_logo, site_email, sosyal_medya, iletisim_bilgileri) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $site_baslik, $site_aciklama, $site_logo, $site_email, $sosyal_medya, $iletisim_bilgileri);
        return $stmt->execute();
    }
    public function aramaYap($arama_terimi, $limit = 10) {
        $arama_terimi = "%$arama_terimi%";
        $stmt = $this->conn->prepare("SELECT y.*, k.kategori_adi, k.slug as kategori_slug, u.kullaniciadi as yazar_adi, u.adisoyadi 
                                    FROM yazilar y 
                                    LEFT JOIN kategoriler k ON y.kategori_id = k.id 
                                    LEFT JOIN kullanicilar u ON y.yazar_id = u.id 
                                    WHERE y.durum = 'yayinda' 
                                    AND (y.baslik LIKE ? 
                                    OR y.icerik LIKE ? 
                                    OR y.ozet LIKE ? 
                                    OR u.kullaniciadi LIKE ? 
                                    OR u.adisoyadi LIKE ?
                                    OR k.kategori_adi LIKE ?) 
                                    ORDER BY y.yayin_tarihi DESC LIMIT ?");
        $stmt->bind_param("ssssssi", $arama_terimi, $arama_terimi, $arama_terimi, $arama_terimi, $arama_terimi, $arama_terimi, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function istatistikleriCek() {
        $istatistikler = [];
        $stmt = $this->conn->prepare("SELECT COUNT(*) as toplam_yazi FROM yazilar WHERE durum = 'yayinda'");
        $stmt->execute();
        $result = $stmt->get_result();
        $istatistikler['toplam_yazi'] = $result->fetch_assoc()['toplam_yazi'];
        $stmt = $this->conn->prepare("SELECT COUNT(*) as toplam_yazar FROM kullanicilar WHERE rol = 'yazar'");
        $stmt->execute();
        $result = $stmt->get_result();
        $istatistikler['toplam_yazar'] = $result->fetch_assoc()['toplam_yazar'];
        return $istatistikler;
    }
    function kategorileriCek() {
        global $kategoriler, $blog;
        foreach ($kategoriler as $kategori): 
            $yazi_sayisi = $blog->kategoriGetir($kategori['id']); ?>
            <div class="kategori-kart">
                <i class="fas fa-folder"></i>
                <h3><?= htmlspecialchars($kategori['kategori_adi']); ?></h3>
                <p><?= htmlspecialchars($kategori['aciklama']); ?></p>
                <span class="kategori-sayi"><?= $yazi_sayisi; ?> Yazı</span>
                <a href="kategori.php?slug=<?= urlencode($kategori['slug']); ?>" class="buton buton-kucuk">Yazıları Gör</a>
            </div>
        <?php endforeach;
    }
}
$blog = new Blog($conn);
$auth = new GirisYap($conn);
$kayit = new KayitOl($conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? '';
    switch ($islem) {
        case 'giris':
            $kullanici_adi = $_POST['kullanici_adi'] ?? '';
            $sifre = $_POST['sifre'] ?? '';
            $beni_hatirla = isset($_POST['beni_hatirla']) ? true : false;
            $sonuc = $auth->girisYap($kullanici_adi, $sifre, $beni_hatirla);
            echo json_encode($sonuc);
            break;
        case 'kayit':
            $kullanici_adi = $_POST['kullanici_adi'] ?? '';
            $sifre = $_POST['sifre'] ?? '';
            $adisoyadi = $_POST['ad'] ?? '';
            $email = $_POST['email'] ?? '';
            
            $sonuc = $kayit->kayitOl($kullanici_adi, $sifre, $adisoyadi, $email);
            echo json_encode($sonuc);
            break;
        case 'cikis':
            $sonuc = $auth->cikisYap();
            echo json_encode($sonuc);
            break;
        case 'yorum_ekle':
            if ($auth->oturumKontrol()) {
                $yazi_id = $_POST['yazi_id'] ?? 0;
                $yorum = $_POST['yorum'] ?? '';
                
                if ($yazi_id && $yorum) {
                    $sonuc = $blog->yorumEkle($yazi_id, $_SESSION['user_id'], $yorum);
                    if ($sonuc) {
                        $stmt = $conn->prepare("SELECT rol FROM kullanicilar WHERE id = ?");
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $kullanici = $result->fetch_assoc();
                        if ($kullanici['rol'] == 'admin' || $kullanici['rol'] == 'editor') {
                            echo json_encode(['basarili' => true, 'mesaj' => 'Yorumunuz başarıyla eklendi!']);
                        } else {
                            echo json_encode(['basarili' => true, 'mesaj' => 'Yorumunuz başarıyla gönderildi! Yorumunuz onaylandıktan sonra yayınlanacaktır.']);
                        }
                    } else {
                        echo json_encode(['basarili' => false, 'mesaj' => 'Yorum eklenirken bir hata oluştu!']);
                    }
                } else {
                    echo json_encode(['basarili' => false, 'mesaj' => 'Lütfen tüm alanları doldurun!']);
                }
            } else {
                echo json_encode(['basarili' => false, 'mesaj' => 'Yorum yapabilmek için giriş yapmalısınız!']);
            }
            break;
        case 'arama':
            $arama_terimi = $_POST['arama_terimi'] ?? '';
            if ($arama_terimi) {
                $sonuclar = $blog->aramaYap($arama_terimi);
                $yazilar = [];
                while ($row = $sonuclar->fetch_assoc()) {
                    $yazilar[] = $row;
                }
                echo json_encode(['yazilar' => $yazilar]);
            }
            break;
            
        default:
            echo json_encode(['hata' => 'Geçersiz işlem']);
    }
}
?>
