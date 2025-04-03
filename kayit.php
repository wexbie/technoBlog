<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - TechBlog</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="ustmenu">
            <div class="logo">
                <i class="fas fa-laptop-code"></i>
                <h1>TechBlog</h1>
            </div>
            <ul class="menulinkler">
                <li><a href="index.php"><i class="fas fa-home"></i> Ana Sayfa</a></li>
                <li><a href="yazilar.php"><i class="fas fa-newspaper"></i> Yazılar</a></li>
                <li><a href="kategoriler.php"><i class="fas fa-tags"></i> Kategoriler</a></li>
            </ul>
        </nav>
    </header>
    <main class="auth-container">
        <div class="auth-box">
            <h2><i class="fas fa-user-plus"></i> Kayıt Ol</h2>
            <div id="mesaj" class="alert" style="display: none;"></div>
            <form id="kayitForm" class="auth-form">
                <div class="form-group">
                    <label for="ad"><i class="fas fa-user"></i> Ad Soyad</label>
                    <input type="text" id="ad" name="ad" required>
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-posta</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="kullanici_adi"><i class="fas fa-user-circle"></i> Kullanıcı Adı</label>
                    <input type="text" id="kullanici_adi" name="kullanici_adi" required>
                </div>
                <div class="form-group">
                    <label for="sifre"><i class="fas fa-lock"></i> Şifre</label>
                    <input type="password" id="sifre" name="sifre" required>
                </div>
                <div class="form-group">
                    <label for="sifre_tekrar"><i class="fas fa-lock"></i> Şifre Tekrar</label>
                    <input type="password" id="sifre_tekrar" name="sifre_tekrar" required>
                </div>
                <button type="submit" class="buton buton-ana"><i class="fas fa-user-plus"></i> Kayıt Ol</button>
            </form>
            <p class="auth-link">Zaten hesabınız var mı? <a href="giris.php">Giriş Yap</a></p>
        </div>
    </main>
    <footer>
        <div class="altbilgi-alt">
            <p>&copy; <?php echo date('Y'); ?> TechBlog. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
    document.getElementById('kayitForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const sifre = document.getElementById('sifre').value;
        const sifreTekrar = document.getElementById('sifre_tekrar').value;
        
        if (sifre !== sifreTekrar) {
            const mesajDiv = document.getElementById('mesaj');
            mesajDiv.style.display = 'block';
            mesajDiv.className = 'alert alert-danger';
            mesajDiv.textContent = 'Şifreler eşleşmiyor!';
            return;
        }
        const formData = new FormData(this);
        formData.append('islem', 'kayit');
        
        fetch('ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const mesajDiv = document.getElementById('mesaj');
            mesajDiv.style.display = 'block';
            
            if (data.basarili) {
                mesajDiv.className = 'alert alert-success';
                setTimeout(() => {
                    window.location.href = 'giris.php';
                }, 1500);
            } else {
                mesajDiv.className = 'alert alert-danger';
            }
            
            mesajDiv.textContent = data.mesaj;
        })
        .catch(error => {
            console.error('Hata:', error);
            const mesajDiv = document.getElementById('mesaj');
            mesajDiv.style.display = 'block';
            mesajDiv.className = 'alert alert-danger';
            mesajDiv.textContent = 'Bir hata oluştu!';
        });
    });
    </script>
</body>
</html> 