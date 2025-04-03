<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - TechBlog</title>
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
            <h2><i class="fas fa-sign-in-alt"></i> Giriş Yap</h2>
            <div id="mesaj" class="alert" style="display: none;"></div>
            <form id="girisForm" class="auth-form">
                <div class="form-group">
                    <label for="kullanici_adi"><i class="fas fa-user"></i> Kullanıcı Adı</label>
                    <input type="text" id="kullanici_adi" name="kullanici_adi" required>
                </div>
                <div class="form-group">
                    <label for="sifre"><i class="fas fa-lock"></i> Şifre</label>
                    <input type="password" id="sifre" name="sifre" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="beni_hatirla" id="beni_hatirla"> Beni Hatırla
                    </label>
                </div>
                <button type="submit" class="buton buton-ana"><i class="fas fa-sign-in-alt"></i> Giriş Yap</button>
            </form>
            <p class="auth-link">Hesabınız yok mu? <a href="kayit.php">Kayıt Ol</a></p>
        </div>
    </main>

    <footer>
        <div class="altbilgi-alt">
            <p>&copy; <?php echo date('Y'); ?> TechBlog. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
    document.getElementById('girisForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('islem', 'giris');
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
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                mesajDiv.className = 'alert alert-danger';
            } mesajDiv.textContent = data.mesaj;
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