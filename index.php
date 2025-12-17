<?php
// index.php - Telegram бот для Railway.app

define('BOT_TOKEN', '8330860787:AAHsXGplrPT4W5Bj8p8RZhS69OlDVRd1j2s'); // Замініть на ваш токен від BotFather
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

// Якщо це GET запит - показати статус
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "✅ Бот працює!\n";
    echo "Час: " . date('Y-m-d H:i:s') . "\n";
    exit;
}

// Отримання даних від Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    http_response_code(200);
    exit;
}

// Логування (опціонально)
file_put_contents('bot.log', date('Y-m-d H:i:s') . " - " . $content . "\n", FILE_APPEND);

// Обробка повідомлень
if (isset($update['message'])) {
    handleMessage($update['message']);
} elseif (isset($update['callback_query'])) {
    handleCallbackQuery($update['callback_query']);
}

http_response_code(200);

// Обробка текстових повідомлень
function handleMessage($message) {
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $user_name = $message['from']['first_name'] ?? 'Друже';
    
    if ($text == '/start') {
        $response = "Привіт, $user_name! 👋\n\n";
        $response .= "Я бот на Railway.app!\n\n";
        $response .= "📋 Команди:\n";
        $response .= "/help - Допомога\n";
        $response .= "/time - Поточний час\n";
        $response .= "/joke - Випадковий жарт\n";
        $response .= "/buttons - Кнопки\n";
        $response .= "/info - Інформація про бота";
        
        sendMessage($chat_id, $response);
        
    } elseif ($text == '/help') {
        $response = "📋 *Список команд:*\n\n";
        $response .= "/start - Початок\n";
        $response .= "/time - Показати час\n";
        $response .= "/joke - Отримати жарт\n";
        $response .= "/buttons - Інтерактивні кнопки\n";
        $response .= "/info - Про бота\n\n";
        $response .= "Просто напишіть мені будь-що!";
        
        sendMessage($chat_id, $response, 'Markdown');
        
    } elseif ($text == '/time') {
        date_default_timezone_set('Europe/Kiev');
        $time = date('d.m.Y H:i:s');
        sendMessage($chat_id, "🕐 Поточний час:\n$time");
        
    } elseif ($text == '/joke') {
        $jokes = [
            "Чому програмісти плутають Хелловін і Різдво?\nБо OCT 31 = DEC 25! 🎃",
            "Як програміст виходить з душу?\nSoap.Wash().Rinse().Repeat() 🚿",
            "Скільки програмістів потрібно для лампочки?\nЖодного, це апаратна проблема! 💡",
            "Bug? Це не bug, це feature! 🐛",
            "Я не спав 3 дні.\nА потім знайшов крапку з комою... 😴"
        ];
        sendMessage($chat_id, $jokes[array_rand($jokes)]);
        
    } elseif ($text == '/buttons') {
        sendMessageWithButtons($chat_id);
        
    } elseif ($text == '/info') {
        $response = "ℹ️ *Інформація про бота*\n\n";
        $response .= "🖥️ Хостинг: Railway.app\n";
        $response .= "💻 Мова: PHP\n";
        $response .= "📅 Версія: 1.0\n";
        $response .= "⚡ Метод: Webhook\n\n";
        $response .= "Створено з ❤️";
        
        sendMessage($chat_id, $response, 'Markdown');
        
    } else {
        // Відповідь на звичайний текст
        if (strlen($text) > 0) {
            $length = mb_strlen($text);
            $words = str_word_count($text);
            
            $response = "📝 Ви написали:\n\"$text\"\n\n";
            $response .= "📊 Статистика:\n";
            $response .= "• Символів: $length\n";
            $response .= "• Слів: $words\n\n";
            $response .= "Спробуйте /help";
            
            sendMessage($chat_id, $response);
        }
    }
}

// Обробка callback query
function handleCallbackQuery($callback_query) {
    $chat_id = $callback_query['message']['chat']['id'];
    $data = $callback_query['data'];
    $callback_id = $callback_query['id'];
    
    switch ($data) {
        case 'btn_1':
            answerCallbackQuery($callback_id, "✅ Обрано варіант 1");
            sendMessage($chat_id, "Ви обрали: 🔵 Варіант 1");
            break;
            
        case 'btn_2':
            answerCallbackQuery($callback_id, "✅ Обрано варіант 2");
            sendMessage($chat_id, "Ви обрали: 🟢 Варіант 2");
            break;
            
        case 'btn_3':
            answerCallbackQuery($callback_id, "✅ Обрано варіант 3");
            sendMessage($chat_id, "Ви обрали: 🟡 Варіант 3");
            break;
            
        case 'btn_link':
            answerCallbackQuery($callback_id, "Відкриваю посилання...");
            break;
    }
}

// Відправка простого повідомлення
function sendMessage($chat_id, $text, $parse_mode = null) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text
    ];
    
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    
    return makeRequest('sendMessage', $data);
}

// Відправка з кнопками
function sendMessageWithButtons($chat_id) {
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🔵 Варіант 1', 'callback_data' => 'btn_1'],
                ['text' => '🟢 Варіант 2', 'callback_data' => 'btn_2']
            ],
            [
                ['text' => '🟡 Варіант 3', 'callback_data' => 'btn_3']
            ],
            [
                ['text' => '🔗 Telegram', 'url' => 'https://telegram.org']
            ]
        ]
    ];
    
    $data = [
        'chat_id' => $chat_id,
        'text' => '🎯 Оберіть опцію:',
        'reply_markup' => json_encode($keyboard)
    ];
    
    return makeRequest('sendMessage', $data);
}

// Відповідь на callback
function answerCallbackQuery($callback_id, $text) {
    $data = [
        'callback_query_id' => $callback_id,
        'text' => $text,
        'show_alert' => false
    ];
    
    return makeRequest('answerCallbackQuery', $data);
}

// Запит до Telegram API
function makeRequest($method, $data) {
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents(API_URL . $method, false, $context);
    
    return $result ? json_decode($result, true) : ['ok' => false];
}
?>