<?php
$slideshow_dir = FCPATH . 'images/slideshow';
$config_file = $slideshow_dir . '/config.json';
$slides = [];

if (file_exists($config_file)) {
    $slides = json_decode(file_get_contents($config_file), true);
    if (!is_array($slides)) {
        $slides = [];
    }
} else {
    // Fallback: directory scan if config.json does not exist
    if (is_dir($slideshow_dir)) {
        $files = scandir($slideshow_dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'])) {
                $slides[] = [
                    'url' => base_url('images/slideshow/' . $file),
                    'type' => in_array($ext, ['mp4', 'webm']) ? 'video' : 'image'
                ];
            }
        }
    }
}

// Fallback if empty
if (empty($slides)) {
    $fallback_urls = [
        'https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=1200&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?q=80&w=1200&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=1200&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1498837167922-ddd27525d352?q=80&w=1200&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1200&auto=format&fit=crop'
    ];
    foreach ($fallback_urls as $url) {
        $slides[] = [
            'url' => $url,
            'type' => 'image'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Display - Daddy's Place</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-dark: #05060a;
            --panel-bg: rgba(12, 14, 21, 0.6);
            --border-glow: rgba(249, 115, 22, 0.12);
            --border-light: rgba(255, 255, 255, 0.05);
            --accent-gradient: linear-gradient(135deg, #ff7b00 0%, #ffaa00 100%);
            --emerald-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #475569;
            --accent-color: #f97316;
            --success-color: #34d399;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            user-select: none;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }

        .container {
            display: flex;
            height: 100%;
            width: 100%;
        }

        /* --- LEFT SIDE: SLIDESHOW --- */
        .slideshow-panel {
            position: relative;
            flex: 1.2;
            height: 100%;
            overflow: hidden;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.7);
            z-index: 10;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            background-size: cover;
            background-position: center;
        }

        .slide.active {
            opacity: 1;
        }

        .slide video, .slide iframe {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: none;
        }

        .slideshow-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(5, 6, 10, 0.15) 0%, rgba(5, 6, 10, 0.85) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 5% 7%;
            z-index: 20;
        }

        .brand-logo-panel {
            position: absolute;
            top: 40px;
            left: 40px;
            z-index: 30;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(5, 6, 10, 0.75);
            padding: 10px 22px;
            border-radius: 50px;
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .brand-logo-text {
            font-size: 24px;
            letter-spacing: 0.5px;
        }

        .brand-logo-text .brand-title-first {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-style: italic;
            color: #ffffff;
        }

        .brand-logo-text .brand-title-second {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: var(--accent-color);
            letter-spacing: 1px;
            margin-left: 5px;
        }

        .brand-logo-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
            filter: drop-shadow(0 0 8px rgba(249, 115, 22, 0.6));
        }

        .welcome-title {
            font-size: 54px;
            line-height: 1.2;
            margin-bottom: 12px;
            text-shadow: 0 2px 12px rgba(0,0,0,0.6);
        }

        .welcome-title .welcome-first {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-style: italic;
            color: #ffffff;
        }

        .welcome-title .welcome-second {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: var(--accent-color);
        }

        .welcome-subtitle {
            font-size: 20px;
            font-weight: 300;
            color: var(--text-secondary);
            letter-spacing: 0.5px;
        }

        /* --- RIGHT SIDE: CART PANEL --- */
        .cart-panel {
            flex: 0.8;
            height: 100%;
            background: radial-gradient(circle at 80% 20%, rgba(249, 115, 22, 0.05) 0%, rgba(5, 6, 10, 0) 60%), var(--bg-dark);
            border-left: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            padding: 40px;
            position: relative;
            z-index: 5;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 20px;
        }

        .cart-header-title {
            font-size: 26px;
            font-weight: 600;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cart-header-badge {
            background: rgba(249, 115, 22, 0.1);
            color: var(--accent-color);
            border: 1px solid rgba(249, 115, 22, 0.25);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .cart-items-container {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 35px;
            padding-right: 10px;
        }

        /* Custom Scrollbar */
        .cart-items-container::-webkit-scrollbar {
            width: 6px;
        }
        .cart-items-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.01);
            border-radius: 10px;
        }
        .cart-items-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            transition: background 0.3s;
        }
        .cart-items-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        /* Cart Item Design */
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--panel-bg);
            border: 1px solid var(--border-light);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .cart-item:hover {
            border-color: rgba(249, 115, 22, 0.25);
            box-shadow: 0 4px 20px rgba(249, 115, 22, 0.03);
            transform: translateY(-2px);
        }

        .item-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-width: 70%;
        }

        .item-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-meta {
            font-size: 14px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .item-qty {
            font-weight: 600;
            color: var(--accent-color);
            background: rgba(249, 115, 22, 0.1);
            padding: 2px 8px;
            border-radius: 6px;
        }

        .item-total {
            font-size: 19px;
            font-weight: 700;
            color: #ffffff;
            text-align: right;
        }

        /* Empty State */
        .empty-cart-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 80%;
            color: var(--text-muted);
            text-align: center;
            animation: fadeIn 0.6s ease;
        }

        .empty-cart-icon {
            margin-bottom: 20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.01);
            border: 1px dashed var(--border-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }

        .empty-cart-title {
            font-size: 20px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .empty-cart-desc {
            font-size: 14px;
            max-width: 250px;
        }

        /* Cart Footer Summary */
        .cart-footer {
            border-top: 1px solid var(--border-light);
            padding-top: 25px;
            background: transparent;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 16px;
            color: var(--text-secondary);
        }

        .summary-row.discount {
            color: var(--success-color);
        }

        .grand-total-card {
            background: var(--accent-gradient);
            border-radius: 20px;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.3);
            position: relative;
            overflow: hidden;
            animation: pulseBg 3s infinite ease-in-out;
        }

        .grand-total-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .total-label {
            font-size: 18px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.95);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .total-amount {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
        }

        /* --- FULLSCREEN CHECKOUT OVERLAY --- */
        .checkout-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(4, 5, 8, 0.96);
            backdrop-filter: blur(25px);
            z-index: 100;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            pointer-events: none;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .checkout-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .checkout-card {
            background: linear-gradient(180deg, rgba(20, 24, 36, 0.6) 0%, rgba(10, 12, 18, 0.6) 100%);
            border: 1px solid var(--border-light);
            border-radius: 36px;
            width: 90%;
            max-width: 800px;
            padding: 50px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 40px rgba(249, 115, 22, 0.05);
            text-align: center;
            transform: scale(0.9);
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .checkout-overlay.active .checkout-card {
            transform: scale(1);
        }

        .checkout-status-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: var(--success-color);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.15);
            animation: popCheck 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .checkout-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 40px;
            color: #ffffff;
        }

        .payment-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 40px;
            text-align: left;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 24px;
        }

        .stat-label {
            font-size: 15px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
        }

        .change-box {
            grid-column: span 2;
            background: var(--emerald-gradient);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.25);
            border: none;
            text-align: center;
            animation: pulseChange 2.5s infinite ease-in-out;
        }

        .change-box .stat-label {
            color: rgba(255, 255, 255, 0.85);
        }

        .change-box .stat-value {
            font-size: 46px;
            font-weight: 800;
            color: #ffffff;
        }

        .thank-you-msg {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-style: italic;
            color: var(--text-secondary);
            margin-top: 10px;
        }

        /* --- ANIMATIONS --- */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulseBg {
            0%, 100% { box-shadow: 0 10px 30px rgba(249, 115, 22, 0.3); }
            50% { box-shadow: 0 10px 45px rgba(249, 115, 22, 0.5); }
        }

        @keyframes pulseChange {
            0%, 100% { box-shadow: 0 10px 30px rgba(16, 185, 129, 0.25); }
            50% { box-shadow: 0 10px 40px rgba(16, 185, 129, 0.45); }
        }

        /* --- FULLSCREEN TOGGLE BUTTON --- */
        .fullscreen-btn {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999;
            background: rgba(12, 14, 21, 0.65);
            border: 1px solid var(--border-light);
            border-radius: 50%;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            padding: 0;
        }

        .fullscreen-btn:hover {
            color: #ffffff;
            border-color: var(--accent-color);
            background: var(--accent-gradient);
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.4);
            transform: translateX(-50%) scale(1.08);
        }
        
        .fullscreen-btn:active {
            transform: translateX(-50%) scale(0.92);
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- LEFT SIDE: PROMOTIONAL SLIDESHOW -->
        <div class="slideshow-panel">
            <div class="brand-logo-panel">
                <div class="brand-logo-badge">
                    <!-- Custom SVG Flame matching logo -->
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C11.5 4 10 5.5 10 8C10 10.5 11.5 12 12.5 12C13 12 13.5 11.5 13.5 11C13.5 9 12 8 13.5 6.5C15 5 17 6.5 17 9.5C17 14.5 12 19 12 21C12 21 6 17 6 12C6 7.5 9.5 4.5 12 2Z" fill="url(#flameGrad)" />
                        <defs>
                            <linearGradient id="flameGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffaa00" />
                                <stop offset="100%" stop-color="#ff3c00" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="brand-logo-text">
                    <span class="brand-title-first">Daddy's</span><span class="brand-title-second">PLACE</span>
                </div>
            </div>

            <?php
            if (!function_exists('get_embed_info')) {
                function get_embed_info($url, $default_type = 'image') {
                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|hosts/[^/]+/|watch\?v=|r/|shorts/)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match)) {
                        $video_id = $match[1];
                        return [
                            'type' => 'youtube',
                            'url' => "https://www.youtube.com/embed/{$video_id}?autoplay=1&mute=1&loop=1&playlist={$video_id}&controls=0&modestbranding=1&rel=0"
                        ];
                    }
                    
                    if (stripos($url, 'facebook.com') !== false || stripos($url, 'fb.watch') !== false || stripos($url, 'fb.gg') !== false) {
                        $encoded_url = urlencode($url);
                        return [
                            'type' => 'facebook',
                            'url' => "https://www.facebook.com/plugins/video.php?href={$encoded_url}&autoplay=true&mute=true&show_text=false&t=0"
                        ];
                    }

                    $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                    if (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                        return [
                            'type' => 'video',
                            'url' => $url
                        ];
                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        return [
                            'type' => 'image',
                            'url' => $url
                        ];
                    }

                    return [
                        'type' => $default_type,
                        'url' => $url
                    ];
                }
            }
            ?>

            <?php foreach ($slides as $index => $slide): 
                $embed = get_embed_info($slide['url'], $slide['type']);
            ?>
                <?php if ($embed['type'] === 'youtube' || $embed['type'] === 'facebook'): ?>
                    <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-type="embed" style="background: #000;">
                        <iframe src="<?php echo $index === 0 ? $embed['url'] : 'about:blank'; ?>" data-src="<?php echo $embed['url']; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                <?php elseif ($embed['type'] === 'video'): ?>
                    <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-type="video">
                        <video src="<?php echo $embed['url']; ?>" muted playsinline loop></video>
                    </div>
                <?php else: ?>
                    <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-type="image" style="background-image: url('<?php echo $embed['url']; ?>');"></div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="slideshow-overlay">
                <h1 class="welcome-title">
                    <span class="welcome-first">Welcome to Daddy's</span> <span class="welcome-second">Place</span>
                </h1>
                <p class="welcome-subtitle">Taste the tradition, enjoy the experience.</p>
            </div>
        </div>

        <!-- RIGHT SIDE: LIVE CART -->
        <div class="cart-panel">
            <div class="cart-header">
                <h2 class="cart-header-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-color);"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                    Your Order
                </h2>
                <div class="cart-header-badge" id="item-count-badge">0 items</div>
            </div>

            <!-- Scrollable Items Container -->
            <div class="cart-items-container" id="items-container">
                <!-- Dynamic cart items will be injected here -->
                <div class="empty-cart-state" id="empty-state">
                    <div class="empty-cart-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                    </div>
                    <h3 class="empty-cart-title">Ready to take order</h3>
                    <p class="empty-cart-desc">Items will appear here as they are added to the cart.</p>
                </div>
            </div>

            <!-- Cart Footer -->
            <div class="cart-footer">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="txt-subtotal">Rs. 0.00</span>
                </div>
                <div class="summary-row discount">
                    <span>Discount</span>
                    <span id="txt-discount">Rs. 0.00</span>
                </div>
                <div class="summary-row" style="display: none;">
                    <span>VAT</span>
                    <span id="txt-vat">Rs. 0.00</span>
                </div>
                <div class="grand-total-card">
                    <span class="total-label">Total Payable</span>
                    <span class="total-amount" id="txt-total">Rs. 0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- FULLSCREEN CHECKOUT OVERLAY -->
    <div class="checkout-overlay" id="checkout-modal">
        <div class="checkout-card">
            <div class="checkout-status-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <h2 class="checkout-title" id="checkout-msg-title">Processing Payment</h2>
            
            <div class="payment-stats">
                <div class="stat-box">
                    <p class="stat-label">Total Payable</p>
                    <p class="stat-value" id="modal-total">Rs. 0.00</p>
                </div>
                <div class="stat-box">
                    <p class="stat-label">Cash Received</p>
                    <p class="stat-value" id="modal-given">Rs. 0.00</p>
                </div>
                <div class="stat-box change-box">
                    <p class="stat-label">Change to Return</p>
                    <p class="stat-value" id="modal-change">Rs. 0.00</p>
                </div>
            </div>
            
            <p class="thank-you-msg">Thank you for dining with us!</p>
        </div>
    </div>

    <!-- JavaScript logic -->
    <script>
        // --- 1. SLIDESHOW SYSTEM (HANDLES IMAGES & VIDEOS) ---
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;
        let slideTimeout = null;

        function playActiveSlideVideo() {
            const activeSlide = slides[currentSlide];
            const video = activeSlide.querySelector('video');
            const iframe = activeSlide.querySelector('iframe');
            if (video) {
                video.currentTime = 0;
                video.play().catch(err => console.log("Video auto play prevented:", err));
                
                // If it's a video, let it play for its duration or at least 12 seconds
                video.onended = function() {
                    nextSlide();
                };
                
                // Safety transition after 20 seconds max in case video loop/errors
                clearTimeout(slideTimeout);
                slideTimeout = setTimeout(nextSlide, 20000);
            } else if (iframe) {
                // If it's an iframe (YouTube/Facebook), load the source
                const dataSrc = iframe.getAttribute('data-src');
                iframe.setAttribute('src', dataSrc);

                // Transition after 25 seconds for iframe embed videos
                clearTimeout(slideTimeout);
                slideTimeout = setTimeout(nextSlide, 25000);
            } else {
                // If image, transition in 6 seconds
                clearTimeout(slideTimeout);
                slideTimeout = setTimeout(nextSlide, 6000);
            }
        }

        function pauseInactiveSlideVideo() {
            slides.forEach((slide, idx) => {
                if (idx !== currentSlide) {
                    const video = slide.querySelector('video');
                    if (video) {
                        video.pause();
                    }
                    const iframe = slide.querySelector('iframe');
                    if (iframe) {
                        iframe.setAttribute('src', 'about:blank'); // Unload to stop playback/audio
                    }
                }
            });
        }

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
            
            pauseInactiveSlideVideo();
            playActiveSlideVideo();
        }

        // Initialize slideshow
        if (slides.length > 0) {
            playActiveSlideVideo();
        }

        // --- 2. SYNC REALTIME LOCALSTORAGE ---
        const itemsContainer = document.getElementById('items-container');
        const itemCountBadge = document.getElementById('item-count-badge');
        const emptyState = document.getElementById('empty-state');
        
        const txtSubtotal = document.getElementById('txt-subtotal');
        const txtDiscount = document.getElementById('txt-discount');
        const txtVat = document.getElementById('txt-vat');
        const txtTotal = document.getElementById('txt-total');

        // Modal Elements
        const checkoutModal = document.getElementById('checkout-modal');
        const modalTotal = document.getElementById('modal-total');
        const modalGiven = document.getElementById('modal-given');
        const modalChange = document.getElementById('modal-change');
        const checkoutMsgTitle = document.getElementById('checkout-msg-title');

        function formatCurrency(val) {
            const num = parseFloat(val) || 0;
            return 'Rs. ' + num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function updateUI(cartData) {
            if (!cartData || !cartData.items || cartData.items.length === 0) {
                // Empty Cart
                itemsContainer.innerHTML = '';
                itemsContainer.appendChild(emptyState);
                itemCountBadge.textContent = '0 items';
                
                txtSubtotal.textContent = 'Rs. 0.00';
                txtDiscount.textContent = 'Rs. 0.00';
                txtVat.textContent = 'Rs. 0.00';
                txtTotal.textContent = 'Rs. 0.00';
                
                // Hide payment modal
                checkoutModal.classList.remove('active');
                return;
            }

            // Clear empty state or old items
            itemsContainer.innerHTML = '';
            
            // Populate items
            let totalQty = 0;
            cartData.items.forEach(item => {
                totalQty += item.qty;
                
                const itemDiv = document.createElement('div');
                itemDiv.className = 'cart-item';
                
                itemDiv.innerHTML = `
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-meta">
                            <span class="item-qty">${item.qty}x</span>
                            <span>${formatCurrency(item.price)}</span>
                        </div>
                    </div>
                    <div class="item-total">${formatCurrency(item.total)}</div>
                `;
                
                itemsContainer.appendChild(itemDiv);
            });

            // Update badge
            itemCountBadge.textContent = totalQty + (totalQty === 1 ? ' item' : ' items');

            // Update footer totals
            txtSubtotal.textContent = formatCurrency(cartData.subtotal);
            txtDiscount.textContent = formatCurrency(cartData.discount);
            txtVat.textContent = formatCurrency(cartData.vat);
            txtTotal.textContent = formatCurrency(cartData.total_payable);

            // Update Payment Modal Overlay if active
            const payment = cartData.payment;
            if (payment && payment.show_payment) {
                modalTotal.textContent = formatCurrency(cartData.total_payable);
                modalGiven.textContent = formatCurrency(payment.given_amount);
                
                const changeVal = parseFloat(payment.change_amount) || 0;
                modalChange.textContent = formatCurrency(payment.change_amount);
                
                if (changeVal >= 0) {
                    checkoutMsgTitle.textContent = 'Payment Completed';
                } else {
                    checkoutMsgTitle.textContent = 'Processing Payment';
                }
                
                checkoutModal.classList.add('active');
            } else {
                checkoutModal.classList.remove('active');
            }
        }

        // Initialize state on load
        try {
            const initialCart = localStorage.getItem('cfd_cart');
            if (initialCart) {
                updateUI(JSON.parse(initialCart));
            }
        } catch (e) {
            console.error("Failed to parse initial cart", e);
        }

        // Listen for storage events
        window.addEventListener('storage', function(event) {
            if (event.key === 'cfd_cart') {
                try {
                    const cartData = JSON.parse(event.newValue || '{}');
                    updateUI(cartData);
                } catch (err) {
                    console.error("Failed to sync storage cart data", err);
                }
            }
        });

        // Fullscreen Toggle
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }

        document.addEventListener('fullscreenchange', () => {
            const btn = document.getElementById('btn-fullscreen');
            if (btn) {
                if (document.fullscreenElement) {
                    btn.innerHTML = `
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/>
                        </svg>
                    `;
                } else {
                    btn.innerHTML = `
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                        </svg>
                    `;
                }
            }
        });
    </script>

    <!-- Floating Fullscreen Button -->
    <button id="btn-fullscreen" class="fullscreen-btn" onclick="toggleFullscreen()" title="Toggle Fullscreen (F11)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
        </svg>
    </button>
</body>
</html>
