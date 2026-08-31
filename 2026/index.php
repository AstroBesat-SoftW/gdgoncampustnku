<?php
// img içinde görseller olacak bir de
// bu NAMIK KEMAL ÜNİVERSİTESİ google kulubü için yapıldı!
// herkes istediği gibi kullanabilir

if (isset($_GET['action'])) {
    header("Content-Type: application/json; charset=UTF-8");
    $file = "sonuclar.json";
    
    if (!file_exists($file)) {
        file_put_contents($file, "[]");
    }
    
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) {
        $data = [];
    }
    
    $action = $_GET['action'];

    /* =========================================
       TELEFON NUMARASI KONTROLÜ
       ========================================= */
    if ($action === 'kontrol' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawPhone = $_POST["phone"] ?? "";
        $phone = preg_replace("/[^\d+]/", "", $rawPhone); // Sadece rakamlar ve + işaretine izin ver

        if (!preg_match("/^(0\d{10}|\+?\d{10,15}$)/", $phone)) {
            echo json_encode(["success" => false, "message" => "Geçerli bir telefon numarası giriniz."]);
            exit;
        }

        // Tüm numaralar için daha önce katılım kontrolü
        foreach ($data as $result) {
            if (isset($result["phone"]) && $result["phone"] === $phone) {
                echo json_encode(["success" => true, "exists" => true, "message" => "Bu telefon numarasıyla daha önce yarışmaya katıldınız."]);
                exit;
            }
        }
        
        echo json_encode(["success" => true, "exists" => false]);
        exit;
    }

    /* =========================================
       SONUÇ KAYDETME
       ========================================= */
    if ($action === 'kaydet' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST["name"] ?? "");
        $rawPhone = $_POST["phone"] ?? "";
        $phone = preg_replace("/[^\d+]/", "", $rawPhone);
        $department = trim($_POST["department"] ?? "");
        $personality = trim($_POST["personality"] ?? "");
        $correct = intval($_POST["correct"] ?? 0);
        $wrong = intval($_POST["wrong"] ?? 0);
        $score = intval($_POST["score"] ?? 0);
        $duration = intval($_POST["duration"] ?? 0);
        $milliseconds = intval($_POST["milliseconds"] ?? 0);

        if ($name === "") {
            echo json_encode(["success" => false, "message" => "Oyuncu adı boş olamaz."]);
            exit;
        }
        if (!preg_match("/^(0\d{10}|\+?\d{10,15}$)/", $phone)) {
            echo json_encode(["success" => false, "message" => "Telefon numarası geçersiz."]);
            exit;
        }

        // Dosyada kayıtlı mı kontrolü - Kayıtlıysa engelle
        foreach ($data as $result) {
            if (isset($result["phone"]) && $result["phone"] === $phone) {
                echo json_encode(["success" => false, "message" => "Bu telefon numarası zaten kayıtlı!"]);
                exit;
            }
        }

        $newResult = [
            "name" => $name,
            "phone" => $phone,
            "department" => $department,
            "personality" => $personality,
            "correct" => $correct,
            "wrong" => $wrong,
            "score" => $score,
            "duration" => $duration,
            "milliseconds" => $milliseconds,
            "created_at" => date("Y-m-d H:i:s")
        ];

        $data[] = $newResult;
        $result = file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

        if ($result === false) {
            echo json_encode(["success" => false, "message" => "Sonuç dosyaya kaydedilemedi."]);
            exit;
        }
        echo json_encode(["success" => true, "message" => "Sonuç başarıyla kaydedildi."]);
        exit;
    }

    /* =========================================
       LİDERLİK TABLOSU
       ========================================= */
    if ($action === 'liderlik' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        usort($data, function ($a, $b) {
            if ($a['score'] == $b['score']) {
                $timeA = ($a['duration'] * 1000) + $a['milliseconds'];
                $timeB = ($b['duration'] * 1000) + $b['milliseconds'];
                return $timeA <=> $timeB;
            }
            return $b['score'] <=> $a['score'];
        });

        $safeData = array_map(function ($item) {
            unset($item['phone']); 
            return $item;
        }, $data);

        echo json_encode(["success" => true, "data" => $safeData]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#ffffff">
    <title>GDG NKÜ Bilgi Yarışması</title>
    <link rel="icon" type="image/png" href="img/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --g-blue: #4285F4; --g-red: #EA4335; --g-yellow: #FBBC05; --g-green: #34A853;
            --bg-color: #f8fafc; --surface: #ffffff; --text-main: #0f172a; --text-dim: #64748b;
            --border-color: #e2e8f0; --shadow-sm: 0 2px 4px rgba(0,0,0,0.05); --shadow-md: 0 10px 20px rgba(0,0,0,0.08);
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; background-color: var(--bg-color); background-image: url('img/arkaplan.png'); background-size: cover; background-position: center; background-attachment: fixed; color: var(--text-main); display: flex; flex-direction: column; height: 100vh; height: 100dvh; overflow: hidden; overscroll-behavior-y: none; }
        .app-bar { height: 60px; background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); z-index: 100; flex-shrink: 0; }
        .google-brand { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; user-select: none; }
        .g-b { color: var(--g-blue); } .g-r { color: var(--g-red); } .g-y { color: var(--g-yellow); } .g-g { color: var(--g-green); }
        .app-main { flex: 1; position: relative; overflow: hidden; background-color: transparent; }
        .app-view { position: absolute; top: 0; left: 0; right: 0; bottom: 0; overflow-y: auto; padding: 15px 15px 35px 15px; display: flex; flex-direction: column; opacity: 0; visibility: hidden; transform: scale(0.95) translateY(10px); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); overscroll-behavior-y: contain; }
        .app-view.active-view { opacity: 1; visibility: visible; transform: scale(1) translateY(0); z-index: 10; }
        .screen[hidden] { display: none !important; }
        .quiz-container { flex: 1; display: flex; align-items: center; justify-content: center; min-height: max-content; }
        .quiz-card { width: 100%; max-width: 450px; background: var(--surface); border-radius: 40px; padding: 30px; box-shadow: var(--shadow-md); position: relative; border: 4px solid transparent; background-clip: padding-box; margin: auto; }
        .quiz-card::before { content: ""; position: absolute; top: -4px; right: -4px; bottom: -4px; left: -4px; z-index: -1; border-radius: 44px; background: linear-gradient(135deg, var(--g-blue), var(--g-red), var(--g-yellow), var(--g-green)); opacity: 0.8; }
        .bottom-nav { position: relative; height: 70px; background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); display: flex; justify-content: space-around; align-items: center; box-shadow: 0 -4px 20px rgba(0,0,0,0.06); border-radius: 30px 30px 0 0; z-index: 1000; flex-shrink: 0; }
        .nav-item { display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-dim); font-size: 12px; font-weight: 600; cursor: pointer; width: 70px; transition: all 0.2s; user-select: none; }
        .nav-item.active { color: var(--g-blue); }
        .nav-icon { font-size: 24px; margin-bottom: 2px; transition: transform 0.2s; }
        .nav-item.active .nav-icon { transform: translateY(-2px) scale(1.1); }
        .nav-item-center { position: relative; top: -25px; width: 70px; height: 70px; background: linear-gradient(135deg, var(--g-blue), var(--g-green)); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(66,133,244,0.4); color: white; font-size: 32px; border: 5px solid rgba(255,255,255,0.85); transition: transform 0.15s; user-select: none; cursor: pointer; overflow: hidden; }
        .nav-item-center img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .nav-item-center:active { transform: scale(0.9); }
        .logo { width: 90px; height: 90px; object-fit: contain; margin: 0 auto 10px; display: block; }
        h1 { margin: 5px 0 10px; text-align: center; font-size: 24px; color: var(--text-main); }
        h2 { color: var(--text-main); text-align: center; font-size: 20px; line-height: 1.4; margin-bottom: 15px; }
        .subtitle { text-align: center; color: var(--text-dim); margin-bottom: 20px; font-size: 15px; }
        .eyebrow { text-align: center; color: var(--g-blue); font-weight: bold; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }
        .highlight { background: linear-gradient(90deg, var(--g-blue), var(--g-green)); -webkit-background-clip: text; color: transparent; font-weight: 700; }
        form label { display: block; width: 100%; margin-bottom: 12px; }
        form label span { display: block; font-weight: 600; margin-bottom: 6px; color: var(--text-main); text-align: center; font-size: 14px; }
        form input { width: 100%; height: 48px; padding: 0 15px; border: 2px solid var(--border-color); border-radius: 15px; background: #f1f5f9; color: var(--text-main); font-size: 15px; outline: none; text-align: center; transition: 0.2s; }
        form input:focus { border-color: var(--g-blue); background: #fff; box-shadow: 0 0 0 4px rgba(66,133,244,0.15); }
        .privacy-note { color: var(--text-dim); font-size: 12px; text-align: center; margin: 5px 0 15px; }
        .primary-button { width: 100%; padding: 15px; border: 0; border-radius: 20px; background: linear-gradient(135deg, var(--g-blue), var(--g-green)); color: #fff; font-size: 17px; font-weight: 700; cursor: pointer; transition: 0.2s; box-shadow: 0 8px 20px rgba(66,133,244,0.3); }
        .primary-button:active { transform: scale(0.96); }
        .primary-button:disabled { opacity: 0.6; pointer-events: none; }
        #startButton, #nextButton { background: linear-gradient(135deg, var(--g-green), #239243); box-shadow: 0 8px 20px rgba(52,168,83,0.3); }
        #passButton { margin-top: 10px; background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 8px 20px rgba(139,92,246,0.3); }
        .quiz-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; width: 100%; }
        .user-info { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
        .user-badge { background: #f1f5f9; padding: 6px 12px; border-radius: 12px; font-size: 13px; font-weight: bold; color: var(--g-blue); }
        .total-time { font-size: 12px; color: var(--text-dim); font-weight: bold; }
        .timer-box { display: flex; flex-direction: column; align-items: center; background: #fff; border: 2px solid var(--border-color); padding: 6px 12px; border-radius: 16px; box-shadow: var(--shadow-sm); }
        .timer-label { font-size: 10px; font-weight: bold; color: var(--text-dim); }
        .timer { font-size: 20px; font-weight: 900; color: var(--g-blue); }
        .timer-warning { color: var(--g-red) !important; animation: heartbeat 1s infinite; }
        .timer-box:has(.timer-warning) { animation: heartbeat-box 1s infinite; border-color: var(--g-red); }
        @keyframes heartbeat { 0%, 28%, 70%, 100% { transform: scale(1); } 14% { transform: scale(1.3); } 42% { transform: scale(1.25); } }
        @keyframes heartbeat-box { 0%, 28%, 70% { box-shadow: 0 0 0 0 rgba(234,67,53,0); } 14% { box-shadow: 0 0 0 6px rgba(234,67,53,0); } 42% { box-shadow: 0 0 0 4px rgba(234,67,53,0.3); } }
        .progress-track { width: 100%; height: 8px; background: #e2e8f0; border-radius: 8px; margin-bottom: 15px; overflow: hidden; }
        #progressBar { height: 100%; width: 0; background: linear-gradient(90deg, var(--g-blue), var(--g-green)); transition: width 0.3s; }
        .question-number { text-align: center; font-weight: bold; color: var(--text-dim); margin-bottom: 10px; font-size: 13px; }
        .options { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; width: 100%; }
        .options button { padding: 14px; border: 2px solid var(--border-color); border-radius: 16px; background: #fff; font-size: 15px; font-weight: 500; text-align: left; transition: 0.2s; color: var(--text-main); cursor: pointer; }
        .options button:active { transform: scale(0.98); background: #f8fafc; }
        .options button.correct { background: rgba(52,168,83,0.15); border-color: var(--g-green); color: #1e7a37; }
        .options button.wrong { background: rgba(234,67,53,0.15); border-color: var(--g-red); color: #b32a20; }
        .option-letter { font-weight: bold; margin-right: 10px; color: var(--g-blue); }
        .score-card { display: flex; justify-content: center; align-items: center; gap: 20px; background: #f8fafc; padding: 15px; border-radius: 20px; border: 2px solid var(--border-color); margin: 15px 0; }
        .score-main strong, .score-detail strong { display: block; font-size: 24px; color: var(--g-green); }
        .score-label { font-size: 11px; font-weight: bold; color: var(--text-dim); }
        .score-divider { width: 2px; height: 35px; background: var(--border-color); }
        .leaderboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .leaderboard-header h3 { margin: 0; font-size: 20px; color: var(--text-main); }
        .leaderboard-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
        .leaderboard-list li { background: #fff; padding: 12px; border-radius: 16px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 12px; border: 1px solid var(--border-color); }
        .leaderboard-list .first-place { border-color: #FFD700; background: #fffdf0; }
        .leaderboard-list .second-place { border-color: #C0C0C0; background: #f8f9fa; }
        .leaderboard-list .third-place { border-color: #CD7F32; background: #fff8f0; }
        .leaderboard-list .rank { font-size: 18px; font-weight: 800; min-width: 25px; text-align: center; }
        .leaderboard-name { flex-grow: 1; font-weight: 600; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .leaderboard-score { text-align: right; font-size: 12px; color: var(--text-dim); }
        .leaderboard-score strong { font-size: 16px; color: var(--text-main); display: block; }
        .text-button { background: none; border: none; color: var(--g-blue); font-weight: bold; font-size: 14px; padding: 5px 10px; cursor: pointer; }
        .about-card { background: #fff; border-radius: 30px; padding: 25px; box-shadow: var(--shadow-md); text-align: center; margin: auto; max-width: 450px; border: 2px solid var(--border-color); }
        .about-card p { line-height: 1.5; color: var(--text-dim); font-size: 14px; margin-bottom: 15px; }
        .about-card strong { color: var(--text-main); }
        .status-message { background: rgba(234,67,53,0.1); border-radius: 12px; padding: 12px; color: var(--g-red); font-size: 14px; font-weight: 600; text-align: center; margin-top: 15px; border: 1px solid rgba(234,67,53,0.3); }
        #questionTransitionOverlay { position: fixed; inset: 0; background: rgba(255,255,255,0.95); z-index: 2000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        #questionTransitionOverlay.active { opacity: 1; pointer-events: all; }
        #questionTransitionOverlay img { width: 100px; animation: logoSpin 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes logoSpin { 0% { transform: scale(0) rotate(-180deg); opacity: 0; } 100% { transform: scale(1) rotate(0); opacity: 1; } }
        
        .confetti { position: fixed; width: 8px; height: 14px; background: #8b5cf6; left: 50%; top: 50%; z-index: 9999; pointer-events: none; animation: confettiBurst 1.2s ease-out forwards; }
        @keyframes confettiBurst { 0% { transform: translate(-50%, -50%) rotate(0deg) scale(1); opacity: 1; } 100% { transform: translate(calc(-50% + var(--x)), calc(-50% + var(--y))) rotate(720deg) scale(0.8); opacity: 0; } }
        
        /* GERÇEK TAM EKRAN GÖRSEL VE HAVALI ANİMASYON STİLLERİ */
        #couponFullscreenContainer {
            position: fixed; 
            inset: 0; 
            width: 100vw; 
            height: 100vh; 
            height: 100dvh; 
            background: rgba(0, 0, 0, 0.75); 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 9999; 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center;
            
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }
        
        #couponFullscreenContainer.active-coupon {
            opacity: 1;
            visibility: visible;
        }

        #couponImage {
            width: 90%;
            max-width: 500px;
            height: auto; 
            max-height: 80vh;
            object-fit: contain; 
            pointer-events: none; 
            user-select: none; 
            -webkit-user-select: none;
            
            opacity: 0;
            transform: scale(0.3) translateY(100px);
        }

        #couponFullscreenContainer.active-coupon #couponImage {
            animation: prizePop 0.9s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            animation-delay: 0.1s; 
        }

        @keyframes prizePop {
            0% { transform: scale(0.3) translateY(200px) rotate(-10deg); opacity: 0; filter: drop-shadow(0 0 0px rgba(251, 188, 5, 0)); }
            60% { transform: scale(1.05) translateY(-15px) rotate(3deg); opacity: 1; filter: drop-shadow(0 0 40px rgba(251, 188, 5, 1)); }
            100% { transform: scale(1) translateY(0) rotate(0); opacity: 1; filter: drop-shadow(0 0 25px rgba(251, 188, 5, 0.7)); }
        }

        #closeImageButton {
            position: absolute; 
            top: 25px; 
            right: 25px; 
            background: rgba(255, 255, 255, 0.15); 
            color: #ffffff; 
            border: 1px solid rgba(255, 255, 255, 0.4); 
            width: 44px; 
            height: 44px; 
            border-radius: 50%; 
            font-size: 22px; 
            cursor: pointer; 
            z-index: 10000; 
            display: flex;
            justify-content: center;
            align-items: center;
            
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.4s ease;
        }
        
        #couponFullscreenContainer.active-coupon #closeImageButton {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 1s; 
        }

        #closeImageButton:hover { background: rgba(255, 255, 255, 0.3); transform: scale(1.1); }
        #closeImageButton:active { transform: scale(0.9); }
        
        .coupon-title {
            color: white;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 4px 15px rgba(0,0,0,0.6);
            opacity: 0;
            transform: translateY(-20px);
        }
        
        #couponFullscreenContainer.active-coupon .coupon-title {
            animation: fadeInDown 0.6s ease forwards;
            animation-delay: 0.5s;
        }
        
        @keyframes fadeInDown {
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-height: 720px) { .quiz-card { padding: 20px 25px; } .logo { width: 65px; height: 65px; margin-bottom: 5px; } h1 { font-size: 20px; margin: 0 0 5px; } .subtitle { font-size: 13px; margin-bottom: 10px; } form input { height: 42px; font-size: 13px; } form label { margin-bottom: 10px; } .primary-button { padding: 12px; font-size: 15px; } .options button { padding: 12px; font-size: 14px; } h2 { font-size: 16px; margin-bottom: 10px; } .timer { font-size: 18px; } .timer-box { padding: 4px 10px; } .score-card { padding: 10px; margin: 10px 0; } .score-main strong, .score-detail strong { font-size: 20px; } .about-card { padding: 20px; } }
        @media (max-height: 600px) { .app-bar { height: 50px; } .google-brand { font-size: 20px; } .bottom-nav { height: 60px; } .nav-item-center { width: 60px; height: 60px; top: -20px; font-size: 26px; } .quiz-card { padding: 15px 20px; border-radius: 30px; } .logo { width: 50px; height: 50px; margin-bottom: 5px; } .eyebrow { font-size: 14px; margin-bottom: 2px; } .subtitle { margin-bottom: 5px; } .coupon-title { font-size: 22px; margin-bottom: 10px; } }
    </style>
</head>
<body>

<div id="couponFullscreenContainer">
    <div class="coupon-title">🏆 ÖDÜLÜNÜ KAZANDIN!</div>
    <img id="couponImage" src="img/kuponn.png" alt="Ödül Kuponu">
    <button id="closeImageButton" type="button" onclick="closeCouponAndGoBack()">✖</button>
</div>

<header class="app-bar">
    <div class="google-brand">
        <span class="g-b">G</span><span class="g-r">o</span><span class="g-y">o</span><span class="g-b">g</span><span class="g-g">l</span><span class="g-r">e</span>
    </div>
</header>

<main class="app-main">
    <div id="view-league" class="app-view">
        <div class="leaderboard-header">
            <h3>🏆 Liderlik Tablosu</h3>
            <button id="refreshLeaderboard" class="text-button">Yenile</button>
        </div>
        <p style="color: var(--text-dim); margin-top:-10px; margin-bottom:15px; font-size:13px;">En başarılı oyuncular burada listelenir.</p>
        <ol id="leaderboardList" class="leaderboard-list"></ol>
    </div>

    <div id="view-game" class="app-view active-view">
        <div class="quiz-container">
            <section class="quiz-card" aria-live="polite">
                
                <div id="welcomeScreen" class="screen">
                    <img src="img/logo.png" alt="GDG Logo" class="logo" onerror="this.src='https://developers.google.com/static/community/images/gdg-logo.svg'">
                    <p class="eyebrow">GDG on Campus<br>Tekirdağ Namık Kemal Üniversitesi</p>
                    <h1>Bilgi Yarışması</h1>
                    <p class="subtitle"><span class="highlight">Bilgini</span> Konuştur, <span class="highlight">Zirveye</span> Çık!</p>
                    
                    <form id="registrationForm">
                        <label>
                            <span>Oyuncu Adı</span>
                            <input type="text" id="usernameInput" maxlength="30" autocomplete="name" required placeholder="Adınızı giriniz">
                        </label>
                        <label>
                            <span>Telefon Numarası</span>
                            <input type="tel" id="phoneInput" autocomplete="tel" required placeholder="+90 5xx xxx xx xx veya 05xx...">
                        </label>
                        <p class="privacy-note">Telefon yalnızca ödül iletişimi için saklanır.</p>
                        <button type="submit" id="startButton" class="primary-button">Yarışmaya Başla</button>
                    </form>
                </div>

                <div id="quizScreen" class="screen" hidden>
                    <div class="quiz-header">
                        <div class="user-info">
                            <span class="user-badge" id="displayUsername">Oyuncu</span>
                            <div class="total-time">⏱️ <span id="totalTimer">00:00.000</span></div>
                        </div>
                        <div class="timer-box">
                            <span class="timer-label">SÜRE</span>
                            <span class="timer" id="timer">20</span>
                        </div>
                    </div>
                    <div class="progress-track" aria-hidden="true">
                        <div id="progressBar"></div>
                    </div>
                    <p class="question-number" id="questionNumber"></p>
                    <h2 id="question">Soru Yükleniyor...</h2>
                    <div id="options" class="options"></div>
                    <button id="nextButton" class="primary-button" type="button" disabled>Sonraki Soru</button>
                    <button id="passButton" class="primary-button" type="button">PAS</button>
                    <div id="passCounter" style="text-align:center; color:var(--text-dim); font-size:12px; font-weight:600; margin-top:8px;">3 pas hakkınız kaldı</div>
                </div>

                <div id="bonusScreen" class="screen" hidden>
                    <p class="eyebrow">Soru 9 / 10 • Bonus</p>
                    <h2 id="bonusQuestionText">Hangi bölümde okuyorsun?</h2>
                    <div id="bonusOptions" class="options"></div>
                </div>

                <div id="bonusScreen2" class="screen" hidden>
                    <p class="eyebrow">Soru 10 / 10 • Bonus</p>
                    <h2 id="bonusQuestionText2">Seni en iyi hangisi tanımlar?</h2>
                    <div id="bonusOptions2" class="options"></div>
                </div>

                <div id="resultScreen" class="screen" hidden>
                    <p class="eyebrow">Yarışma Tamamlandı</p>
                    <h2 id="resultTitle">Tebrikler! 🎉</h2>
                    <div class="score-card">
                        <div class="score-main">
                            <span class="score-label">DOĞRU</span>
                            <strong id="scoreValue">0 / 10</strong>
                        </div>
                        <div class="score-divider"></div>
                        <div class="score-detail">
                            <span class="score-label">TOPLAM SÜRE</span>
                            <strong id="durationValue">00:00.000</strong>
                        </div>
                    </div>
                    <p id="resultText" style="text-align:center; color:var(--text-dim); margin-bottom:15px; font-size:14px;"></p>
                    
                    <button id="showCouponBtn" class="primary-button" type="button" style="background: linear-gradient(135deg, var(--g-blue), #2b6cb0); margin-bottom:10px;">
                        🎁 Ödülümü Al!
                    </button>
                    
                    <button id="newParticipantButton" class="primary-button" type="button" style="background:var(--surface); color:var(--text-main); border:2px solid var(--border-color); box-shadow:none;">Yeni Katılımcı</button>
                </div>
                
                <p id="statusMessage" class="status-message" role="alert" hidden></p>
            </section>
        </div>
    </div>

    <div id="view-about" class="app-view">
        <div style="flex:1; display:flex; align-items:center; justify-content:center;">
            <div class="about-card">
                <img src="img/logo.png" alt="GDG NKÜ Logo" style="width:80px; height:80px; margin-bottom:10px;">
                <h2 style="font-size:20px; margin-bottom:5px;">Google Developer Groups<br><span style="font-size:14px; color:var(--text-dim);">on Campus</span></h2>
                <p><strong>Tekirdağ Namık Kemal Üniversitesi</strong></p>
                <p>Bu uygulama Tekirdağ Namık Kemal Üniversitesi <strong>GDG (Google Developer Groups)</strong> kulübü tarafından teknoloji tutkunları için hazırlanmıştır.</p>
                <p>Amacımız, üniversite öğrencilerini yazılım, tasarım ve Google teknolojileri konusunda bir araya getirmek, projeler üretmek ve ekosisteme katkı sağlamaktır.</p>
                <div style="margin-top:20px; color:var(--g-blue); font-weight:bold; display:flex; gap:10px; justify-content:center; font-size:13px;">
                    <span>&lt;/&gt;</span> Kodla <span>🚀</span> Geliştir <span>🌐</span> Paylaş
                </div>
            </div>
        </div>
    </div>
</main>

<nav class="bottom-nav">
    <div class="nav-item" onclick="switchView('view-league')" id="nav-league">
        <span class="nav-icon">🏆</span><span>Lig</span>
    </div>
    <div class="nav-item-center" onclick="switchView('view-game')" id="nav-game">
        <img src="img/logo.png" alt="Oyun İkonu">
    </div>
    <div class="nav-item" onclick="switchView('view-about')" id="nav-about">
        <span class="nav-icon">ℹ️</span><span>Hakkında</span>
    </div>
</nav>

<div id="questionTransitionOverlay" aria-hidden="true">
    <img src="img/logo.png" alt="" onerror="this.src='https://developers.google.com/static/community/images/gdg-logo.svg'">
</div>

<script>
    function switchView(viewId) {
        document.querySelectorAll('.app-view').forEach(view => view.classList.remove('active-view'));
        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
        const selectedView = document.getElementById(viewId);
        if (selectedView) selectedView.classList.add('active-view');
        
        if (viewId === 'view-league') {
            document.getElementById('nav-league').classList.add('active');
            fetchLeaderboard();
        }
        if (viewId === 'view-about') document.getElementById('nav-about').classList.add('active');
        if (viewId === 'view-game') document.getElementById('nav-game')?.classList.add('active');
    }

    async function fetchLeaderboard() {
        try {
            const response = await fetch("?action=liderlik");
            const result = await response.json();
            const list = document.getElementById("leaderboardList");
            list.innerHTML = "";

            if (result.success && result.data && result.data.length > 0) {
                result.data.forEach((item, index) => {
                    const li = document.createElement("li");
                    let medal;
                    if (index === 0) { medal = "🥇"; li.classList.add("first-place"); }
                    else if (index === 1) { medal = "🥈"; li.classList.add("second-place"); }
                    else if (index === 2) { medal = "🥉"; li.classList.add("third-place"); }
                    else { medal = `${index + 1}.`; }

                    li.innerHTML = `
                        <span class="rank">${medal}</span>
                        <span class="leaderboard-name">${escapeHTML(item.name || "")}</span>
                        <span class="leaderboard-score">
                            <strong>${item.score} D</strong>
                            <span>${formatTimeWithMilliseconds((item.duration * 1000) + item.milliseconds)}</span>
                        </span>
                    `;
                    list.appendChild(li);
                });
            } else {
                list.innerHTML = `<li style="justify-content:center; color:#64748b;">Henüz kayıt yok.</li>`;
            }
        } catch (error) {
            console.error("Liderlik tablosu çekilemedi:", error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchLeaderboard();
    });
    document.getElementById("refreshLeaderboard")?.addEventListener("click", fetchLeaderboard);

    let currentQuestion = 0, correctCount = 0, answered = false, username = "", phone = "", totalMilliseconds = 0;
    let questions = [], allQuestionsPool = [], usedQuestions = new Set(), timerInterval = null, totalTimerInterval = null;
    let quizStartTime = null, totalSeconds = 0, questionSeconds = 0, passCount = 0;

    const QUESTION_TIME = 20, TOTAL_QUESTIONS = 10, NORMAL_QUESTIONS = 8, MAX_PASS = 3;
    let selectedDepartment = "", selectedPersonality = "";

    const bonusQuestion = {
        soru: "Hangi bölümde okuyorsun?",
        secenekler: { A: "Bilgisayar Mühendisliği", B: "Makine Mühendisliği", C: "İnşaat Mühendisliği", D: "Biyomedikal Mühendisliği", E: "Çevre Mühendisliği", F: "Elektrik-Elektronik Mühendisliği", G: "Endüstri Mühendisliği", H: "Tekstil Mühendisliği", I: "Diğer" }
    };
    const bonusQuestion2 = {
        soru: "Kendini en iyi hangisi tanımlar?",
        secenekler: { A: "Meraklı", B: "Sosyal", C: "Matematikçi", D: "Maceracı", E: "Öğrenmeyi seven", F: "Gezmeyi seven", G: "Hiçbiri..." }
    };

    const welcomeScreen = document.getElementById("welcomeScreen");
    const quizScreen = document.getElementById("quizScreen");
    const resultScreen = document.getElementById("resultScreen");
    const bonusScreen = document.getElementById("bonusScreen");
    const bonusScreen2 = document.getElementById("bonusScreen2");
    
    const couponFullscreenContainer = document.getElementById("couponFullscreenContainer");
    
    const bonusOptionsElement = document.getElementById("bonusOptions");
    const bonusOptionsElement2 = document.getElementById("bonusOptions2");
    const registrationForm = document.getElementById("registrationForm");
    const usernameInput = document.getElementById("usernameInput");
    const phoneInput = document.getElementById("phoneInput");
    const displayUsername = document.getElementById("displayUsername");
    const questionNumber = document.getElementById("questionNumber");
    const questionElement = document.getElementById("question");
    const optionsElement = document.getElementById("options");
    const nextButton = document.getElementById("nextButton");
    const timerElement = document.getElementById("timer");
    const progressBar = document.getElementById("progressBar");
    const totalTimer = document.getElementById("totalTimer");
    const scoreValue = document.getElementById("scoreValue");
    const durationValue = document.getElementById("durationValue");
    const resultText = document.getElementById("resultText");
    const statusMessage = document.getElementById("statusMessage");
    const newParticipantButton = document.getElementById("newParticipantButton");
    const passButton = document.getElementById("passButton");
    const passCounter = document.getElementById("passCounter");
    const showCouponBtn = document.getElementById("showCouponBtn"); 

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    function escapeHTML(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }

    function showStatus(message) {
        statusMessage.textContent = message;
        statusMessage.hidden = false;
    }

    function hideStatus() {
        statusMessage.textContent = "";
        statusMessage.hidden = true;
    }

    async function loadQuestions() {
        try {
            const response = await fetch("sorular.json?random=" + Date.now());
            if (!response.ok) throw new Error("sorular.json bulunamadı.");
            const allQuestions = await response.json();
            if (!Array.isArray(allQuestions) || allQuestions.length === 0) throw new Error("sorular.json boş veya hatalı.");
            
            allQuestionsPool = [...allQuestions];
            const level0Questions = allQuestions.filter(q => Number(q.lwl) === 0);
            const level1Questions = allQuestions.filter(q => Number(q.lwl) === 1);
            
            shuffleArray(level0Questions);
            shuffleArray(level1Questions);
            
            const selectedLevel0 = level0Questions.slice(0, 5);
            const selectedLevel1 = level1Questions.slice(0, 3);
            questions = shuffleArray([...selectedLevel0, ...selectedLevel1]);
            
            return true;
        } catch (error) {
            console.error("Sorular yüklenemedi:", error);
            questions = [];
            return false;
        }
    }

    phoneInput.addEventListener("input", function () {
        this.value = this.value.replace(/[^\d+]/g, "");
    });

    registrationForm.addEventListener("submit", async function (event) {
        event.preventDefault();
        username = usernameInput.value.trim();
        phone = phoneInput.value.trim();
        hideStatus();

        if (username === "") return showStatus("Lütfen oyuncu adınızı girin.");
        
        const phoneRegex = /^(0\d{10}|\+?\d{10,15})$/;
        if (!phoneRegex.test(phone)) return showStatus("Lütfen geçerli bir telefon numarası girin (Örn: 05xx... veya +90...).");

        const startButton = document.getElementById("startButton");
        startButton.disabled = true;
        startButton.textContent = "Sorular hazırlanıyor...";

        try {
            const formData = new FormData();
            formData.append("phone", phone);
            const response = await fetch("index.php?action=kontrol", { method: "POST", body: formData });
            if (!response.ok) throw new Error("Bağlantı hatası.");
            
            const result = await response.json();
            if (!result.success) return showStatus(result.message);
            if (result.exists) return showStatus("Bu telefon numarasıyla daha önce katıldınız.");
            
            await startQuiz();
        } catch (error) {
            console.error(error);
            showStatus("Bir hata oluştu.");
        } finally {
            startButton.disabled = false;
            startButton.textContent = "Yarışmaya Başla";
        }
    });

    async function startQuiz() {
        const questionsLoaded = await loadQuestions();
        if (!questionsLoaded || questions.length < NORMAL_QUESTIONS) return showStatus("Sorular yüklenemedi veya yeterli soru bulunamadı.");

        currentQuestion = 0; correctCount = 0; answered = false; passCount = 0;
        totalMilliseconds = 0; totalSeconds = 0; questionSeconds = 0;
        selectedDepartment = ""; selectedPersonality = "";
        
        stopTimer(); stopTotalTimer();
        usedQuestions = new Set(questions);
        quizStartTime = Date.now();
        
        displayUsername.textContent = username;
        welcomeScreen.hidden = true; quizScreen.hidden = false; bonusScreen.hidden = true; bonusScreen2.hidden = true; resultScreen.hidden = true;
        
        questionNumber.textContent = "Soru 1 / 10";
        progressBar.style.width = "0%";
        if (passButton) passButton.style.display = "block";
        totalTimer.textContent = "00:00.000";
        
        startTotalTimer();
        showQuestion();
    }

    function startTotalTimer() {
        stopTotalTimer();
        totalTimerInterval = setInterval(() => {
            if (!quizStartTime) return;
            totalMilliseconds = Date.now() - quizStartTime;
            totalSeconds = Math.floor(totalMilliseconds / 1000);
            totalTimer.textContent = formatTimeWithMilliseconds(totalMilliseconds);
        }, 10);
    }

    function stopTotalTimer() {
        if (totalTimerInterval !== null) { clearInterval(totalTimerInterval); totalTimerInterval = null; }
    }

    function stopTimer() {
        if (timerInterval !== null) { clearInterval(timerInterval); timerInterval = null; }
    }

    function formatTimeWithMilliseconds(msTotal) {
        const minutes = Math.floor(msTotal / 60000);
        const seconds = Math.floor((msTotal % 60000) / 1000);
        const ms = msTotal % 1000;
        return String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0") + "." + String(ms).padStart(3, "0");
    }

    function showQuestion() {
        stopTimer();
        answered = false;
        nextButton.disabled = true;
        quizScreen.hidden = false; bonusScreen.hidden = true; bonusScreen2.hidden = true; resultScreen.hidden = true;

        if (currentQuestion >= NORMAL_QUESTIONS) {
            if (currentQuestion === NORMAL_QUESTIONS) return showBonusQuestion();
            if (currentQuestion === NORMAL_QUESTIONS + 1) return showBonusQuestion2();
            return showResult();
        }

        const current = questions[currentQuestion];
        if (!current) return showResult();

        questionSeconds = 0;
        questionNumber.textContent = `Soru ${currentQuestion + 1} / ${TOTAL_QUESTIONS}`;
        questionElement.textContent = current.soru;
        progressBar.style.width = `${((currentQuestion + 1) / TOTAL_QUESTIONS) * 100}%`;
        optionsElement.innerHTML = "";

        Object.entries(current.secenekler).forEach(([key, value]) => {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "option";
            button.innerHTML = `<span class="option-letter">${escapeHTML(key)}</span><span>${escapeHTML(value)}</span>`;
            button.addEventListener("click", () => selectAnswer(key, button));
            optionsElement.appendChild(button);
        });

        startQuestionTimer();
    }

    function updatePassCounter() {
        const remaining = MAX_PASS - passCount;
        if (passCounter) passCounter.textContent = `${remaining} pas hakkınız kaldı`;
        if (passButton && remaining <= 0) passButton.style.display = "none";
    }

    function passQuestion() {
        if (answered || passCount >= MAX_PASS) return;
        stopTimer();
        
        const replacementCandidates = allQuestionsPool.filter(question => !usedQuestions.has(question));
        if (replacementCandidates.length === 0) return startQuestionTimer();

        const replacement = replacementCandidates[Math.floor(Math.random() * replacementCandidates.length)];
        usedQuestions.add(replacement);
        questions[currentQuestion] = replacement;
        
        passCount++;
        updatePassCounter();
        showQuestion();
    }
    if (passButton) passButton.addEventListener("click", passQuestion);

    function startQuestionTimer() {
        stopTimer();
        questionSeconds = 0;
        updateTimerDisplay();

        timerInterval = setInterval(() => {
            questionSeconds++;
            updateTimerDisplay();
            if (questionSeconds >= QUESTION_TIME) { stopTimer(); timeOutQuestion(); }
        }, 1000);
    }

    function updateTimerDisplay() {
        const remaining = Math.max(0, QUESTION_TIME - questionSeconds);
        timerElement.textContent = String(remaining).padStart(2, "0");
        if (remaining <= 5) timerElement.classList.add("timer-warning");
        else timerElement.classList.remove("timer-warning");
    }

    function timeOutQuestion() {
        if (answered) return;
        answered = true;
        
        const allOptions = document.querySelectorAll(".option");
        allOptions.forEach(b => b.disabled = true);
        
        const current = questions[currentQuestion];
        allOptions.forEach(button => {
            const letter = button.querySelector(".option-letter").textContent.trim();
            if (letter === String(current.cevap).trim()) button.classList.add("correct");
        });
        nextButton.disabled = false;
    }

    function showPurpleConfetti() {
        for (let i = 0; i < 35; i++) {
            const confetti = document.createElement("div");
            confetti.className = "confetti";
            const angle = Math.random() * Math.PI * 2;
            const distance = 100 + Math.random() * 250;
            confetti.style.setProperty("--x", `${Math.cos(angle) * distance}px`);
            confetti.style.setProperty("--y", `${Math.sin(angle) * distance}px`);
            confetti.style.animationDelay = Math.random() * 0.15 + "s";
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 1500);
        }
    }
    
    function fireMegaRewardConfetti() {
        const colors = ['#4285F4', '#EA4335', '#FBBC05', '#34A853', '#8b5cf6', '#FFD700'];
        for (let i = 0; i < 150; i++) {
            const confetti = document.createElement("div");
            confetti.className = "confetti";
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            
            const angle = Math.random() * Math.PI * 2;
            const distance = 150 + Math.random() * 600; 
            const duration = 1 + Math.random() * 2; 
            
            confetti.style.setProperty("--x", `${Math.cos(angle) * distance}px`);
            confetti.style.setProperty("--y", `${Math.sin(angle) * distance}px`);
            confetti.style.animation = `confettiBurst ${duration}s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards`;
            
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), duration * 1000);
        }
    }

    function selectAnswer(selectedAnswer, selectedButton) {
        if (answered) return;
        answered = true;
        stopTimer();
        
        const current = questions[currentQuestion];
        const allOptions = document.querySelectorAll(".option");
        allOptions.forEach(b => b.disabled = true);

        if (String(selectedAnswer).trim() === String(current.cevap).trim()) {
            correctCount++;
            selectedButton.classList.add("correct");
            showPurpleConfetti();
        } else {
            selectedButton.classList.add("wrong");
            allOptions.forEach(button => {
                const letter = button.querySelector(".option-letter").textContent.trim();
                if (letter === String(current.cevap).trim()) button.classList.add("correct");
            });
        }
        nextButton.disabled = false;
    }

    const overlay = document.getElementById("questionTransitionOverlay");
    nextButton.addEventListener("click", () => {
        if (!answered) return;
        if (overlay) {
            overlay.classList.add("active");
            setTimeout(() => overlay.classList.remove("active"), 500);
        }
        setTimeout(goToNextQuestion, 200);
    });

    function goToNextQuestion() {
        stopTimer();
        currentQuestion++;
        if (currentQuestion < NORMAL_QUESTIONS) return showQuestion();
        if (currentQuestion === NORMAL_QUESTIONS) return showBonusQuestion();
        if (currentQuestion === NORMAL_QUESTIONS + 1) return showBonusQuestion2();
        showResult();
    }

    function showBonusQuestion() {
        stopTimer();
        quizScreen.hidden = true; bonusScreen.hidden = false; bonusScreen2.hidden = true; resultScreen.hidden = true;
        questionNumber.textContent = "Soru 9 / 10";
        progressBar.style.width = "90%";
        bonusOptionsElement.innerHTML = "";

        Object.entries(bonusQuestion.secenekler).forEach(([key, value]) => {
            const button = document.createElement("button");
            button.type = "button"; button.className = "option";
            button.innerHTML = `<span class="option-letter">${escapeHTML(key)}</span><span>${escapeHTML(value)}</span>`;
            button.addEventListener("click", () => {
                if (selectedDepartment !== "") return;
                selectedDepartment = value; correctCount++;
                bonusOptionsElement.querySelectorAll(".option").forEach(b => b.disabled = true);
                button.classList.add("correct");
                if (overlay) { overlay.classList.add("active"); setTimeout(() => overlay.classList.remove("active"), 500); }
                setTimeout(showBonusQuestion2, 400);
            });
            bonusOptionsElement.appendChild(button);
        });
    }

    function showBonusQuestion2() {
        bonusScreen.hidden = true; bonusScreen2.hidden = false; resultScreen.hidden = true; 
        questionNumber.textContent = "Soru 10 / 10";
        progressBar.style.width = "100%";
        bonusOptionsElement2.innerHTML = "";

        Object.entries(bonusQuestion2.secenekler).forEach(([key, value]) => {
            const button = document.createElement("button");
            button.type = "button"; button.className = "option";
            button.innerHTML = `<span class="option-letter">${escapeHTML(key)}</span><span>${escapeHTML(value)}</span>`;
            button.addEventListener("click", () => {
                if (selectedPersonality !== "") return;
                selectedPersonality = value; correctCount++;
                bonusOptionsElement2.querySelectorAll(".option").forEach(b => b.disabled = true);
                button.classList.add("correct");
                if (overlay) { overlay.classList.add("active"); setTimeout(() => overlay.classList.remove("active"), 500); }
                setTimeout(showResult, 400);
            });
            bonusOptionsElement2.appendChild(button);
        });
    }

    async function showResult() {
        stopTimer(); stopTotalTimer();
        if (quizStartTime) {
            totalMilliseconds = Date.now() - quizStartTime;
            totalSeconds = Math.floor(totalMilliseconds / 1000);
        }
        quizStartTime = null;
        
        quizScreen.hidden = true; bonusScreen.hidden = true; bonusScreen2.hidden = true; welcomeScreen.hidden = true; resultScreen.hidden = false;
        
        const wrongCount = TOTAL_QUESTIONS - correctCount;
        scoreValue.textContent = `${correctCount} / ${TOTAL_QUESTIONS}`;
        durationValue.textContent = formatTimeWithMilliseconds(totalMilliseconds);
        resultText.textContent = `${username}, yarışmayı tamamladın! 🎉 Skorun kaydediliyor...`;
        
        await saveResult(wrongCount);
    }

    async function saveResult(wrongCount) {
        try {
            const formData = new FormData();
            formData.append("name", username);
            formData.append("phone", phone);
            formData.append("department", selectedDepartment);
            formData.append("personality", selectedPersonality);
            formData.append("correct", correctCount);
            formData.append("wrong", wrongCount);
            formData.append("score", correctCount);
            formData.append("duration", totalSeconds);
            formData.append("milliseconds", totalMilliseconds % 1000);

            const response = await fetch("index.php?action=kaydet", { method: "POST", body: formData });
            const result = await response.json();

            if (!result.success) {
                console.error("Kayıt başarısız:", result.message);
                resultText.innerHTML = `<span style="color: var(--g-red); font-weight: bold; font-size: 16px;">⚠️ ${result.message}</span>`;
            } else {
                resultText.textContent = `${username}, yarışmayı tamamladın! 🎉 Sonucun başarıyla kaydedildi.`;
            }
            fetchLeaderboard();
        } catch (error) {
            console.error("Kayıt hatası:", error);
            resultText.innerHTML = `<span style="color: var(--g-red);">Kayıt sırasında bir hata oluştu!</span>`;
        }
    }

    if (showCouponBtn) {
        showCouponBtn.addEventListener("click", function() {
            couponFullscreenContainer.classList.add("active-coupon");
            fireMegaRewardConfetti();
        });
    }

    function closeCouponAndGoBack() {
        couponFullscreenContainer.classList.remove("active-coupon");
        setTimeout(() => {
            switchView('view-league');
        }, 400); 
    }

    if (newParticipantButton) {
        newParticipantButton.addEventListener("click", () => {
            stopTimer(); stopTotalTimer();
            currentQuestion = 0; correctCount = 0; answered = false; passCount = 0;
            updatePassCounter();
            username = ""; phone = ""; selectedDepartment = ""; selectedPersonality = "";
            totalMilliseconds = 0; totalSeconds = 0; questionSeconds = 0; quizStartTime = null;
            questions = []; allQuestionsPool = []; usedQuestions = new Set();
            
            resultScreen.hidden = true; 
            quizScreen.hidden = true; 
            bonusScreen.hidden = true; 
            bonusScreen2.hidden = true; 
            couponFullscreenContainer.classList.remove("active-coupon");
            welcomeScreen.hidden = false;
            
            usernameInput.value = ""; phoneInput.value = "";
            totalTimer.textContent = "00:00.000"; timerElement.textContent = "20";
            timerElement.classList.remove("timer-warning"); progressBar.style.width = "0%";
            if (passButton) passButton.style.display = "block";
            
            hideStatus();
            switchView("view-game");
        });
    }
</script>
</body>
</html>
