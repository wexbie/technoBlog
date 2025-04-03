<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çıkış Yapılıyor - TechBlog</title>
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
            <h2><i class="fas fa-sign-out-alt"></i> Çıkış Yapılıyor</h2>
            <div id="mesaj" class="alert" style="display: none;"></div>
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Çıkış yapılıyor, lütfen bekleyin...
            </div>
        </div>
    </main>
    <footer>
        <div class="altbilgi-alt">
            <p>&copy; <?php echo date('Y'); ?> TechBlog. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const formData = new FormData();
        formData.append('islem', 'cikis');
        
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