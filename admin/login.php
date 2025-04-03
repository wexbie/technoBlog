<?php
require_once 'config.php';

if(isset($_SESSION['kullanici_id'])) {
    header('Location: index.php');
    exit;
}

$hata = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = temizle($_POST['email']);
    $sifre = $_POST['sifre'];
    
    if(empty($email) || empty($sifre)) {
        $hata = 'Lütfen tüm alanları doldurun.';
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE email = ? AND durum = 1");
            $stmt->execute([$email]);
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($kullanici && password_verify($sifre, $kullanici['kullaniciparolasi'])) {
                $_SESSION['kullanici_id'] = $kullanici['id'];
                $_SESSION['kullanici_adi'] = $kullanici['adisoyadi'];
                $_SESSION['kullanici_rol'] = $kullanici['rol'];
                
                try {
                    $stmt = $db->prepare("UPDATE kullanicilar SET son_giris = NOW() WHERE id = ?");
                    $stmt->execute([$kullanici['id']]);
                } catch(PDOException $e) {
                    error_log("son_giris güncelleme hatası: " . $e->getMessage());
                }
                
                if(isset($_POST['beni_hatirla'])) {
                    $token = bin2hex(random_bytes(32));
                    $stmt = $db->prepare("UPDATE kullanicilar SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$token, $kullanici['id']]);
                    setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 gün
                }
                
                header('Location: index.php');
                exit;
            } else {
                $hata = 'Geçersiz e-posta veya şifre.';
            }
        } catch(PDOException $e) {
            error_log("Login hatası: " . $e->getMessage());
            $hata = 'Giriş yapılırken bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

if(!isset($_SESSION['kullanici_id']) && isset($_COOKIE['remember_token'])) {
    try {
        $token = $_COOKIE['remember_token'];
        $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE remember_token = ? AND durum = 1");
        $stmt->execute([$token]);
        $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($kullanici) {
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['kullanici_adi'] = $kullanici['adisoyadi'];
            $_SESSION['kullanici_rol'] = $kullanici['rol'];
            header('Location: index.php');
            exit;
        }
    } catch(PDOException $e) {
        error_log("Remember token hatası: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Giriş Yap - <?php echo $site_ayarlari['site_baslik']; ?></title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<style>
		:root {
			--primary-color: #4361ee;
			--secondary-color: #3f37c9;
			--accent-color: #4895ef;
			--text-color: #333;
			--light-text: #666;
			--background-color: #f8f9fa;
			--card-bg: #ffffff;
			--error-color: #e63946;
			--success-color: #2ecc71;
			--border-radius: 12px;
			--box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
			--transition: all 0.3s ease;
		}
		
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		}
		
		body {
			background-color: var(--background-color);
			color: var(--text-color);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
			background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
		}
		
		.login-container {
			width: 100%;
			max-width: 420px;
			margin: 0 auto;
		}
		
		.login-box {
			background-color: var(--card-bg);
			border-radius: var(--border-radius);
			box-shadow: var(--box-shadow);
			overflow: hidden;
			padding: 40px;
			position: relative;
		}
		
		.login-box::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 5px;
			background: linear-gradient(to right, var(--primary-color), var(--accent-color));
		}
		
		.login-header {
			text-align: center;
			margin-bottom: 30px;
		}
		
		.login-header i {
			font-size: 48px;
			color: var(--primary-color);
			margin-bottom: 15px;
		}
		
		.login-header h1 {
			font-size: 24px;
			font-weight: 600;
			color: var(--text-color);
		}
		
		.alert {
			padding: 12px 15px;
			border-radius: var(--border-radius);
			margin-bottom: 20px;
			display: flex;
			align-items: center;
			font-size: 14px;
		}
		
		.alert-danger {
			background-color: rgba(230, 57, 70, 0.1);
			color: var(--error-color);
			border-left: 4px solid var(--error-color);
		}
		
		.alert i {
			margin-right: 10px;
			font-size: 16px;
		}
		
		.form-group {
			margin-bottom: 20px;
		}
		
		.form-group label {
			display: block;
			margin-bottom: 8px;
			font-weight: 500;
			color: var(--light-text);
			font-size: 14px;
		}
		
		.form-group input[type="email"],
		.form-group input[type="password"] {
			width: 100%;
			padding: 12px 15px;
			border: 1px solid #e1e1e1;
			border-radius: var(--border-radius);
			font-size: 15px;
			transition: var(--transition);
			background-color: #f9f9f9;
		}
		
		.form-group input[type="email"]:focus,
		.form-group input[type="password"]:focus {
			outline: none;
			border-color: var(--primary-color);
			box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
			background-color: #fff;
		}
		
		.checkbox-label {
			display: flex;
			align-items: center;
			cursor: pointer;
			font-size: 14px;
			color: var(--light-text);
		}
		
		.checkbox-label input[type="checkbox"] {
			margin-right: 8px;
			width: 16px;
			height: 16px;
			accent-color: var(--primary-color);
		}
		
		.btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 12px 20px;
			border: none;
			border-radius: var(--border-radius);
			font-size: 16px;
			font-weight: 500;
			cursor: pointer;
			transition: var(--transition);
			width: 100%;
		}
		
		.btn-primary {
			background-color: var(--primary-color);
			color: white;
		}
		
		.btn-primary:hover {
			background-color: var(--secondary-color);
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
		}
		
		.btn i {
			margin-right: 8px;
		}
		
		.btn-block {
			display: block;
			width: 100%;
		}
		
		@media (max-width: 480px) {
			.login-box {
				padding: 30px 20px;
			}
		}
	</style>
</head>
<body>
	<div class="login-container">
		<div class="login-box">
			<div class="login-header">
				<i class="fas fa-user-circle"></i>
				<h1>Giriş Yap</h1>
			</div>
			
			<?php if($hata): ?>
				<div class="alert alert-danger">
					<i class="fas fa-exclamation-circle"></i>
					<?php echo $hata; ?>
				</div>
			<?php endif; ?>
			
			<form method="POST" action="">
				<div class="form-group">
					<label for="email">E-posta</label>
					<input type="email" id="email" name="email" required 
						   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
						   placeholder="E-posta adresinizi girin">
				</div>
				
				<div class="form-group">
					<label for="sifre">Şifre</label>
					<input type="password" id="sifre" name="sifre" required
						   placeholder="Şifrenizi girin">
				</div>
				
				<div class="form-group">
					<label class="checkbox-label">
						<input type="checkbox" name="beni_hatirla" value="1">
						<span>Beni Hatırla</span>
					</label>
				</div>
				
				<button type="submit" class="btn btn-primary btn-block">
					<i class="fas fa-sign-in-alt"></i>
					Giriş Yap
				</button>
			</form>
		</div>
	</div>
</body>
</html>