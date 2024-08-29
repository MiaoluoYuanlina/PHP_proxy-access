<?php
// 定义白名单域名
$whitelistedDomains = [
    "xiaomiao-ica.top",
    'github.com',
    "githubusercontent.com",
    'bepinex.dev',
    "fanbox.cc",
    "pixiv.net",
    "pximg.net",
];

// 日志文件路径
$logFile = __DIR__ . '/access_log.txt';

// 默认 Referer
$defaultReferer = 'https://www.google.com';



// 函数：将字节数转换为合适的单位
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $size = $bytes;
    $unit = 0;
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    return number_format($size, 2) . ' ' . $units[$unit];
}

// 检查用户是否提供了文件的 URL
if (isset($_GET['fileUrl'])) {
    $fileUrl = $_GET['fileUrl'];

    // 获取用户自定义的 Referer，如果没有提供，则使用默认的 Referer
    $referer = isset($_GET['referer']) ? $_GET['referer'] : $defaultReferer;

    // 解析用户提供的 URL
    $parsedUrl = parse_url($fileUrl);
    $host = $parsedUrl['host'] ?? '';
    // 解码 URL
    $decodedUrl = rawurldecode($fileUrl);

    // 检查域名是否在白名单中
    $isWhitelisted = false;
    foreach ($whitelistedDomains as $domain) {
        if (stripos($host, $domain) !== false) {
            $isWhitelisted = true;
            break;
        }
    }

    if ($isWhitelisted) {
        // 设置 HTTP 请求头，包括 Referer
        $options = [
            "http" => [
                "header" => "Referer: " . $referer . "\r\n" // 使用用户自定义的 Referer 或默认的 Referer
            ]
        ];
        $context = stream_context_create($options);
        
        // 使用 file_get_contents 获取文件内容
        $fileContent = file_get_contents($fileUrl, false, $context);

        if ($fileContent !== false) {
            $status = 'Success';

            // 获取文件名
            $fileName = basename($fileUrl);
            
            // 获取文件后缀名
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

            // 如果没有识别到后缀名，设置一个默认值（例如 txt）
            if (empty($fileExtension)) {
                $fileExtension = 'txt';
            }
            
            // 计算文件大小
            $fileSize = strlen($fileContent);
            
            // 设置 HTTP 头部信息，告诉浏览器这是一个文件下载
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . strlen($fileContent));

            // 输出文件内容
            echo $fileContent;
        } else {
            $status = 'Failed';
            echo '下载文件失败。';
            $fileSize = 0; // 下载失败时流量消耗为 0
        }
    } else {
        $status = 'Blocked';
        echo '你请求的URL不在白名单。';
        $fileSize = 0; // 被阻止的请求流量为 0
        
    }
    // 格式化文件大小
    $formattedSize = formatFileSize($fileSize);
    // 记录访问的链接和状态到日志文件
    $logEntry = date('Y-m-d H:i:s') . " - 请求访问的 URL: " . $fileUrl . " - 现状: " . $status . " - 消耗流量: " . $formattedSize . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
} else {
    echo "<span style='color: #FF5733;'> 请使用 “fileUrl ”参数提供文件 URL。 </span><br>"; 
    echo "<br><span style='color: #00FF7F;'> 如: </span>"; 
    echo "<br><span style='color: #00EE00;'> https://api.xiaomiao-ica.top/agent/text.php<span style='color: #00CD00;'>?fileUrl=<span style='color: #7B68EE;'>链接<span style='color: #00CD00;'>&referer=<span style='color: #7B68EE;'>referer链接头字段 </span></span></span></span></span>"; 
    echo "<br><span style='color: #FF1493;'> 如果我要访问Pixiv的图，不提供referer会拒绝访问。pixiv储存图片的地址是 i.pximg.net 我们直接请求他他会拒绝访问，pixiv的主域名是 www.pixiv.net </span>";  
    echo "<br><span style='color: #FF1493;'> 如果我们请求的链接是https://i.pximg.net/img-original/img/2023/10/19/00/00/22/112660921_p0.jpg i.pximg.net的域名就都可以设置 https://www.pixiv.net 为referer</span>";  
    echo "<br><span style='color: #00EE00;'> https://api.xiaomiao-ica.top/agent/text.php<span style='color: #00CD00;'>?fileUrl=<span style='color: #7B68EE;'>https://i.pximg.net/img-original/img/2023/10/19/00/00/22/112660921_p0.jpg<span style='color: #00CD00;'>&referer=<span style='color: #7B68EE;'>https://www.pixiv.net </span></span></span></span></span>"; 
}
?>
