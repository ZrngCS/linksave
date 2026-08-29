<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $video_url = trim($_POST['video_url']);
    
    // کلیلی هاوبەشی RapidAPI کە بۆ هەردووکیان هەمان شتە
    $apiKey = "b772ab55e8msha877f03142fd9bbp1cbb83jsn0cdc2f75ebd6";
    
    $apiUrl = "";
    $apiHost = "";
    $platform = "";
    $download_link = null;
    $error_msg = null;
    $raw_response = null;

    // پشکنین: ئایا لینکەکە تیکتۆکە یان سناپچات؟
    if (strpos($video_url, 'tiktok.com') !== false) {
        $platform = "TikTok";
        $apiHost = "tiktok-video-no-watermark2.p.rapidapi.com";
        $apiUrl = "https://tiktok-video-no-watermark2.p.rapidapi.com/?url=" . urlencode($video_url) . "&hd=1";
        
    } elseif (strpos($video_url, 'snapchat.com') !== false) {
        $platform = "Snapchat";
        $apiHost = "download-snapchat-video-spotlight-online.p.rapidapi.com";
        
        // تێبینی: ئەم لینکەی خوارەوە ڕەنگە کەمێک گۆڕانکاری بوێت بەپێی بەشی (Download Snap Video)
        // زۆربەی کات بۆ سناپچات بەم شێوەیەیە:
        $apiUrl = "https://download-snapchat-video-spotlight-online.p.rapidapi.com/download?url=" . urlencode($video_url);
        
    } else {
        $error_msg = "تکایە تەنها لینکی تیکتۆک یان سناپچات دابنێ.";
    }

    // ئەگەر کێشە لە لینکەکە نەبوو، داواکارییەکە دەنێرین
    if (!$error_msg) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "x-rapidapi-host: " . $apiHost,
                "x-rapidapi-key: " . $apiKey
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $error_msg = "کێشە لە ڕاژە: " . $err;
        } else {
            $data = json_decode($response, true);
            $raw_response = $data; // هەڵگرتنی وەڵامەکە بۆ کاتی کێشە

            // دۆزینەوەی لینکەکە بەپێی جۆری پلاتفۆرمەکە
            if ($platform == "TikTok") {
                if (isset($data['data']['play'])) {
                    $download_link = $data['data']['play'];
                } elseif (isset($data['video']['noWatermark'])) {
                    $download_link = $data['video']['noWatermark'];
                }
            } elseif ($platform == "Snapchat") {
                // تێبینی: بەپێی API یەکەی سناپچات ئەم بەشە دەگۆڕێت
                // بە زۆری لەناو ['data']['video_url'] یان ['url'] دایە
                if (isset($data['video_url'])) {
                    $download_link = $data['video_url'];
                } elseif (isset($data['data']['video'])) {
                    $download_link = $data['data']['video'];
                } elseif (isset($data['url'])) {
                    $download_link = $data['url'];
                }
            }
        }
    }

    // --- بەشی پیشاندانی ئەنجامەکە بە دیزاینی شوشەیی ---
    echo "<!DOCTYPE html><html lang='ku' dir='rtl'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>ئەنجام | $platform</title><link href='https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap' rel='stylesheet'><link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'><style>body{margin:0;font-family:'Cairo',sans-serif;background:#0f172a;min-height:100vh;display:flex;justify-content:center;align-items:center;color:#fff;}.shape{position:absolute;filter:blur(80px);z-index:-1;border-radius:50%;}.shape1{width:300px;height:300px;background:#ff0050;top:-50px;left:-50px;opacity:0.5;}.shape2{width:400px;height:400px;background:#00f2fe;bottom:-100px;right:-50px;opacity:0.4;}.glass{background:rgba(255,255,255,0.05);backdrop-filter:blur(15px);border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:30px;text-align:center;width:90%;max-width:400px;box-shadow:0 8px 32px rgba(0,0,0,0.3);z-index:10;}video{width:100%;border-radius:10px;margin:20px 0;box-shadow:0 4px 15px rgba(0,0,0,0.5);}.btn{display:inline-block;width:100%;padding:12px;background:linear-gradient(45deg,#00f2fe,#4facfe);border-radius:10px;color:white;text-decoration:none;font-weight:700;box-sizing:border-box;margin-bottom:10px;transition:0.3s;}.btn:hover{transform:scale(1.02);}.btn-back{background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);}.error-box{background:rgba(255,0,0,0.2); border:1px solid rgba(255,0,0,0.4); padding:15px; border-radius:10px; margin-bottom:20px; font-size:14px;}</style></head><body>";
    echo "<div class='shape shape1'></div><div class='shape shape2'></div>";
    echo "<div class='glass'>";
    
    if ($download_link) {
        // سەرکەوتوو بوو
        echo "<h2 style='margin-top:0;'><i class='fa-solid fa-circle-check' style='color:#00f2fe;'></i> ڤیدیۆکە ئامادەیە</h2>";
        echo "<video controls><source src='" . $download_link . "' type='video/mp4'></video>";
        echo "<a href='" . $download_link . "' target='_blank' class='btn' download><i class='fa-solid fa-download'></i> داگرتنی ڤیدیۆکە</a>";
    } else {
        // کێشەیەک هەیە
        echo "<h2 style='margin-top:0;'><i class='fa-solid fa-circle-xmark' style='color:#ff0050;'></i> هەڵەیەک ڕوویدا</h2>";
        $display_error = $error_msg ? $error_msg : "نەتوانرا ڤیدیۆی $platform بدۆزرێتەوە.";
        echo "<div class='error-box'>$display_error</div>";
        
        // نیشاندانی کۆدی JSON کاتێک ڤیدیۆکە نادۆزرێتەوە (بۆ مەبەستی چارەسەر)
        if ($raw_response && !$error_msg) {
            echo "<div style='text-align:left; direction:ltr; font-size:11px; background:#1e293b; padding:10px; border-radius:8px; overflow-x:auto; margin-bottom:15px;'>";
            echo "<strong>API Response:</strong><br><pre>" . print_r($raw_response, true) . "</pre></div>";
        }
    }
    
    echo "<a href='index.html' class='btn btn-back'><i class='fa-solid fa-arrow-right'></i> گەڕانەوە بەشی سەرەکی</a>";
    echo "</div></body></html>";

} else {
    header("Location: index.html");
}
?>