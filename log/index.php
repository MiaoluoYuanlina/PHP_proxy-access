<?php
// 日志文件路径 (相对路径)
$logFile = __DIR__ . '/../access_log.json';

// 检查日志文件是否存在
if (file_exists($logFile)) {
    // 读取日志文件内容
    $logData = file_get_contents($logFile);

    // 将日志内容解码为数组
    $logs = json_decode($logData, true);

    // 检查是否有日志内容
    if (!empty($logs)) {
        echo "<h2 style='color: #333;'>日志记录：</h2>";
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-color: #ddd;'>";
        echo "<tr style='background-color: #f4f4f4; color: #333;'>
                <th>时间戳</th>
                <th>IP 地址</th>
                <th>位置</th>
                <th>请求的 URL</th>
                <th>状态</th>
                <th>消耗流量</th>
              </tr>";

        // 遍历日志并显示每条记录
        foreach ($logs as $log) {
            // 根据状态改变文字颜色
            $statusColor = $log['status'] == 'Success' ? 'green' : ($log['status'] == 'Failed' ? 'red' : 'orange');

            echo "<tr>";
            echo "<td style='color: #555;'>" . htmlspecialchars($log['timestamp']) . "</td>";
            echo "<td style='color: #555;'>" . htmlspecialchars($log['ip_address']) . "</td>";
            echo "<td style='color: #555;'>" . htmlspecialchars($log['location']) . "</td>";
            echo "<td style='color: #0066cc;'>" . htmlspecialchars($log['url_requested']) . "</td>";
            echo "<td style='color: " . $statusColor . ";'>" . htmlspecialchars($log['status']) . "</td>";
            echo "<td style='color: #555;'>" . htmlspecialchars($log['data_transferred']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<span style='color: red;'>日志文件为空。</span>";
    }
} else {
    echo "<span style='color: red;'>日志文件不存在。</span>";
}
?>
