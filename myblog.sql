-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 03 Nis 2025, 14:48:29
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `myblog`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ayarlar`
--

CREATE TABLE `ayarlar` (
  `id` int(11) NOT NULL,
  `site_baslik` varchar(100) NOT NULL,
  `site_aciklama` text DEFAULT NULL,
  `site_logo` varchar(255) DEFAULT NULL,
  `site_email` varchar(100) NOT NULL,
  `guncelleme_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `ayarlar`
--

INSERT INTO `ayarlar` (`id`, `site_baslik`, `site_aciklama`, `site_logo`, `site_email`, `guncelleme_tarihi`) VALUES
(1, 'TechBlog', 'Teknoloji ve bilim dünyasından en güncel haberler', NULL, 'info@techblog.com', '2025-04-03 10:27:10');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kategoriler`
--

CREATE TABLE `kategoriler` (
  `id` int(11) NOT NULL,
  `kategori_adi` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `durum` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `kategoriler`
--

INSERT INTO `kategoriler` (`id`, `kategori_adi`, `slug`, `aciklama`, `durum`) VALUES
(1, 'Kripto', 'kripto', 'Kripto ile ilgili yazılar', 1),
(2, 'Web', 'web', 'Web ile ilgili yazılar', 1),
(3, 'Oyun', 'oyun', 'Oyun ile ilgili yazılar', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `kullaniciadi` varchar(50) NOT NULL,
  `kullaniciparolasi` varchar(255) NOT NULL,
  `adisoyadi` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` enum('admin','editor','yazar') NOT NULL DEFAULT 'yazar',
  `durum` tinyint(1) NOT NULL DEFAULT 1,
  `kayit_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(64) DEFAULT NULL,
  `son_giris` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `kullaniciadi`, `kullaniciparolasi`, `adisoyadi`, `email`, `rol`, `durum`, `kayit_tarihi`, `remember_token`, `son_giris`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Kullanıcı', 'admin@example.com', 'admin', 1, '2025-04-03 10:27:10', NULL, '2025-04-03 15:38:31'),
(2, 'editor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Editör Kullanıcı', 'editor@example.com', 'editor', 1, '2025-04-03 10:27:10', NULL, NULL),
(3, 'yazar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Yazar Kullanıcı', 'yazar@example.com', 'yazar', 1, '2025-04-03 10:27:10', NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sifre_sifirlama`
--

CREATE TABLE `sifre_sifirlama` (
  `id` int(11) NOT NULL,
  `kullanici_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `son_kullanim` datetime NOT NULL,
  `kullanildi` tinyint(1) NOT NULL DEFAULT 0,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yazilar`
--

CREATE TABLE `yazilar` (
  `id` int(11) NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icerik` text NOT NULL,
  `ozet` text DEFAULT NULL,
  `kategori_id` int(11) NOT NULL,
  `yazar_id` int(11) NOT NULL,
  `kapak_resmi` varchar(255) DEFAULT NULL,
  `durum` enum('taslak','yayinda','arsiv') NOT NULL DEFAULT 'taslak',
  `yayin_tarihi` datetime DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `guncelleme_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `yazilar`
--

INSERT INTO `yazilar` (`id`, `baslik`, `slug`, `icerik`, `ozet`, `kategori_id`, `yazar_id`, `kapak_resmi`, `durum`, `yayin_tarihi`, `olusturma_tarihi`, `guncelleme_tarihi`) VALUES
(1, 'Deneme', 'deneme', ' Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme Deneme', 'Deneme Deneme Deneme Deneme Deneme Deneme', 1, 1, '67ee62f0822c1.png', 'yayinda', '2025-04-03 13:29:00', '2025-04-03 10:29:04', '2025-04-03 10:34:13'),
(2, 'Bilim', 'bilim', ' Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim', 'Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim Bilim', 2, 1, '67ee63f56bd7c.png', 'yayinda', '2025-04-03 13:33:00', '2025-04-03 10:33:25', '2025-04-03 10:33:25');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yazi_okunmalar`
--

CREATE TABLE `yazi_okunmalar` (
  `id` int(11) NOT NULL,
  `yazi_id` int(11) NOT NULL,
  `ip_adresi` varchar(45) NOT NULL,
  `okunma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `yazi_okunmalar`
--

INSERT INTO `yazi_okunmalar` (`id`, `yazi_id`, `ip_adresi`, `okunma_tarihi`) VALUES
(1, 2, '::1', '2025-04-03 10:33:40');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yorumlar`
--

CREATE TABLE `yorumlar` (
  `id` int(11) NOT NULL,
  `yazi_id` int(11) NOT NULL,
  `kullanici_id` int(11) NOT NULL,
  `yorum` text NOT NULL,
  `durum` enum('beklemede','onaylandi','reddedildi') NOT NULL DEFAULT 'beklemede',
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `yorumlar`
--

INSERT INTO `yorumlar` (`id`, `yazi_id`, `kullanici_id`, `yorum`, `durum`, `olusturma_tarihi`) VALUES
(1, 2, 1, 'Deneme bilim deneme', 'onaylandi', '2025-04-03 10:40:51');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `ayarlar`
--
ALTER TABLE `ayarlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kategoriler`
--
ALTER TABLE `kategoriler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullaniciadi` (`kullaniciadi`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `sifre_sifirlama`
--
ALTER TABLE `sifre_sifirlama`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kullanici_id` (`kullanici_id`);

--
-- Tablo için indeksler `yazilar`
--
ALTER TABLE `yazilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `yazar_id` (`yazar_id`);

--
-- Tablo için indeksler `yazi_okunmalar`
--
ALTER TABLE `yazi_okunmalar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `yazi_id` (`yazi_id`);

--
-- Tablo için indeksler `yorumlar`
--
ALTER TABLE `yorumlar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `yazi_id` (`yazi_id`),
  ADD KEY `kullanici_id` (`kullanici_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `ayarlar`
--
ALTER TABLE `ayarlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `kategoriler`
--
ALTER TABLE `kategoriler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `sifre_sifirlama`
--
ALTER TABLE `sifre_sifirlama`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `yazilar`
--
ALTER TABLE `yazilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `yazi_okunmalar`
--
ALTER TABLE `yazi_okunmalar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `yorumlar`
--
ALTER TABLE `yorumlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `sifre_sifirlama`
--
ALTER TABLE `sifre_sifirlama`
  ADD CONSTRAINT `sifre_sifirlama_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `yazilar`
--
ALTER TABLE `yazilar`
  ADD CONSTRAINT `yazilar_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategoriler` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `yazilar_ibfk_2` FOREIGN KEY (`yazar_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `yazi_okunmalar`
--
ALTER TABLE `yazi_okunmalar`
  ADD CONSTRAINT `yazi_okunmalar_ibfk_1` FOREIGN KEY (`yazi_id`) REFERENCES `yazilar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `yorumlar`
--
ALTER TABLE `yorumlar`
  ADD CONSTRAINT `yorumlar_ibfk_1` FOREIGN KEY (`yazi_id`) REFERENCES `yazilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `yorumlar_ibfk_2` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
