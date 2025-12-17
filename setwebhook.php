<?php
// setwebhook.php - Встановлення webhook після деплою

$token = '8330860787:AAHsXGplrPT4W5Bj8p8RZhS69OlDVRd1j2s'; // Ваш токен
$webhook_url = 'https://tg-production-f342.up.railway.app'; // URL від Railway (наприклад: https://yourapp.up.railway.app)

echo "🔧 Налаштування Telegram Bot Webhook\n";
echo str_repeat("=", 50) . "\n\n";

// 1. Видалити старий webhook
echo "1️⃣ Видалення старого webhook...\n";
$delete_url = "https://api.telegram.org/bot$token/deleteWebhook?drop_pending_updates=true";
$result = json_decode(file_get_contents($delete_url), true);

if ($result['ok']) {
    echo "   ✅ Старий webhook видалено\n\n";
}

// 2. Встановити новий webhook
echo "2️⃣ Встановлення нового webhook...\n";
echo "   URL: $webhook_url\n";

$set_url = "https://api.telegram.org/bot$token/setWebhook?url=$webhook_url";
$result = json_decode(file_get_contents($set_url), true);

if ($result['ok']) {
    echo "   ✅ Webhook встановлено!\n\n";
} else {
    echo "   ❌ Помилка: " . $result['description'] . "\n\n";
    exit(1);
}

// 3. Перевірка
echo "3️⃣ Перевірка webhook...\n";
$info_url = "https://api.telegram.org/bot$token/getWebhookInfo";
$info = json_decode(file_get_contents($info_url), true);

if ($info['ok']) {
    $data = $info['result'];
    echo "   URL: " . $data['url'] . "\n";
    echo "   Pending: " . $data['pending_update_count'] . "\n";
    
    if (isset($data['last_error_message'])) {
        echo "   ⚠️  Помилка: " . $data['last_error_message'] . "\n";
    } else {
        echo "   ✅ Все працює!\n";
    }
}

echo "\n✅ Готово! Тепер напишіть боту /start\n";
?>
