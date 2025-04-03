# TechnoBlog - Basit Blog Sitesi

## Proje Hakkında
TechnoBlog, PHP ve MySQL kullanılarak geliştirilmiş modern ve kullanıcı dostu bir blog sistemidir. Kullanıcıların yazı paylaşabileceği, kategoriler oluşturabileceği ve etkileşimde bulunabileceği kapsamlı bir platformdur.

## Özellikler
- 🔐 Kullanıcı Yönetimi (Kayıt, Giriş, Çıkış)
- 📝 Blog Yazıları Yönetimi
- 📁 Kategori Sistemi
- 🔍 Gelişmiş Arama Özellikleri
- 📱 Responsive Tasarım
- 🖼️ Medya Yükleme Desteği
- ⚡ AJAX ile Dinamik İçerik Yükleme

## Kurulum
1. Projeyi XAMPP'ın htdocs klasörüne klonlayın
2. MySQL veritabanında `myblog.sql` dosyasını import edin
3. `config.php` dosyasındaki veritabanı bilgilerini kendi ayarlarınıza göre düzenleyin
4. Tarayıcınızdan `http://localhost/` adresine giderek projeyi görüntüleyin

## Sistem Gereksinimleri
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache Web Sunucusu
- XAMPP (önerilen)

## Dosya Yapısı
```
myblog/
├── admin/           # Yönetici paneli dosyaları
├── js/             # JavaScript dosyaları
├── uploads/        # Yüklenen medya dosyaları
├── ajax.php        # AJAX işlemleri
├── config.php      # Veritabanı yapılandırması
├── giris.php       # Kullanıcı girişi
├── kayit.php       # Kullanıcı kaydı
├── yazilar.php     # Blog yazıları listesi
├── kategori.php    # Kategori görüntüleme
├── arama.php       # Arama fonksiyonları
└── style.css       # Ana stil dosyası
```

## Güvenlik
- SQL Injection koruması
- XSS (Cross-Site Scripting) koruması
- CSRF (Cross-Site Request Forgery) koruması
- Güvenli şifre hashleme

## Katkıda Bulunma
1. Bu depoyu fork edin
2. Yeni bir branch oluşturun (`git checkout -b feature/yeniOzellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik: Açıklama'`)
4. Branch'inizi push edin (`git push origin feature/yeniOzellik`)
5. Pull Request oluşturun

## Lisans
Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakınız.

## İletişim
Sorularınız ve önerileriniz için issue açabilir veya pull request gönderebilirsiniz.