<?php
// ai-chat.php
session_start();
if ($_SESSION['role'] !== 'main_admin') {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('Africa/Nairobi');
ob_start(); // Start output buffering


require_once 'db.php';

// DeepSeek API configuration
define('DEEPSEEK_API_KEY', 'sk-e1b7ad523e2d49aea04e1b099ccdbb62'); // Must start with 'sk-'
define('DEEPSEEK_API_URL', "https://api.deepseek.com/chat/completions"); // Updated endpoint

// Prevent form resubmission on refresh
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SESSION['last_message'])) {
    unset($_SESSION['last_message']);
}
// Initialize chat history
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        ['role' => 'system', 'content' => 'You are a helpful AI assistant.']
    ];
}

// Process user input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $userMessage = htmlspecialchars(trim($_POST['message']));
    
    if (isset($_SESSION['last_message']) && $_SESSION['last_message'] === $userMessage) {
        // Don't process duplicate messages
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Duplicate message detected']);
            exit;
        }
        return;
    }
    
    $_SESSION['last_message'] = $userMessage;
    // Add user message to history
    $_SESSION['chat_history'][] = [
    'role' => 'user', 
    'content' => $userMessage,
    'timestamp' => time() // Store Unix timestamp
];
    
    try {
        // Prepare API request
        $data = [
            'model' => 'deepseek-chat',
            'messages' => $_SESSION['chat_history'],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];
        
        // Call DeepSeek API with better error handling
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => DEEPSEEK_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . DEEPSEEK_API_KEY,
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('CURL Error: ' . curl_error($ch));
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response');
            }
            
            $aiMessage = $responseData['choices'][0]['message']['content'];
            $_SESSION['chat_history'][] = [
            'role' => 'assistant',
            'content' => $aiMessage,
            'timestamp' => time()
        ];
        } else {
            throw new Exception("API request failed with HTTP $httpCode. Response: " . $response);
        }
    } catch (Exception $e) {
        // More detailed error handling
        error_log('AI Chat Error: ' . $e->getMessage());
        $_SESSION['chat_history'][] = [
            'role' => 'assistant', 
            'content' => "Sorry, I encountered an error. Please try again later. " . 
                         "(Technical details: " . $e->getMessage() . ")"
        ];
    }
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Clear any accidental output
    ob_end_clean();
    
    header('Content-Type: application/json');
    http_response_code(200);
    
    $response = [
        'user_message' => $userMessage ?? '',
        'ai_message' => $aiMessage ?? '',
        'error' => $error ?? null,
        'http_code' => $httpCode ?? null
    ];
    
    echo json_encode($response);
    exit;
}
ob_end_flush();
?>



<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative&family=MedievalSharp&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Soul Companion</title>
    <style>
        /* Custom Scrollbar */
        #chat-messages::-webkit-scrollbar {
            width: 8px;
            background: #2d3748;
        }
        #chat-messages::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 4px;
        }

        /* Message Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-fade {
            animation: fadeIn 0.3s ease-out;
        }

        /* Gothic Border */
        .gothic-border {
            border: 2px solid #4a5568;
            border-image: linear-gradient(45deg, #4a5568, #2d3748) 1;
            box-shadow: 0 0 15px rgba(74, 85, 104, 0.3);
        }
        form {
        position: sticky;
        bottom: 0;
        z-index: 10; /* Ensures it appears above the messages */
        background: inherit; /* Keep the same background as the container */
    }
    </style>
</head>
<body class="h-full p-0 bg-gradient-to-br from-gray-900 to-gray-800 flex flex-col">
    <div class="container mx-auto max-w-5xl flex flex-col p-0 flex-1">
        <div class="bg-gray-800 rounded-lg shadow-2xl flex flex-col gothic-border h-full">
            
            <!-- Chat Header -->
            <div class="bg-gradient-to-r from-gray-900 to-gray-800 p-4  sticky top-0 z-50  rounded-t-lg border-b border-gray-700">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-skull text-2xl text-red-500"></i>
                    <div>
                        <h1 class="text-xl font-bold text-gray-200 font-cinzel">AI Support - Death & Grief</h1>
                        <p class="text-sm text-gray-400 font-medieval">Whispers from the veil...</p>
                    </div>
                </div>
            </div>
            
            <!-- Chat Messages (Scrollable) -->
            <div class="flex-1 p-4 overflow-y-auto" id="chat-messages">
                <?php foreach ($_SESSION['chat_history'] as $index => $message): ?>
                    <?php if ($message['role'] !== 'system'): ?>
                        <div class="flex mb-4 message-fade <?= $message['role'] === 'user' ? 'justify-end' : 'justify-start' ?>">
                            <div class="<?= $message['role'] === 'user' ? 'bg-gray-700 text-gray-100' : 'bg-gray-900 text-gray-300' ?> rounded-lg p-3 max-w-[80%] relative border border-gray-600 hover:border-gray-500 transition-all">
                                <?php if ($message['role'] === 'user'): ?>
                                    <i class="fas fa-skull-crossbones absolute -left-4 top-2 text-gray-500"></i>
                                <?php else: ?>
                                    <i class="fas fa-spider absolute -right-4 top-2 text-gray-500"></i>
                                <?php endif; ?>
                                <?= nl2br(htmlspecialchars($message['content'])) ?>
                                <div class="absolute bottom-1 right-2 text-xs pt-4 text-gray-500">
                                    <?= date('H:i', $message['timestamp'] ?? time()) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Chat Input (Always at Bottom) -->
            <form method="post" class="p-4 border-t border-gray-700 bg-gray-800">
                <div class="flex gap-2 relative">
                    <i class="fas fa-quote-left absolute left-3 top-3 text-gray-500"></i>
                    <input type="text" name="message" 
                           class="flex-1 border border-gray-700 bg-gray-900 text-gray-300 rounded-lg pl-8 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                           placeholder="Your thoughts..." required>
                    <button type="submit" 
                            class="bg-gradient-to-r from-red-800 to-red-900 text-gray-200 px-6 py-2 rounded-lg hover:from-red-700 hover:to-red-800 transition-all flex items-center space-x-2 border border-red-900 hover:border-red-800">
                        <i class="fas fa-ghost"></i>
                        <span>Send</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
    function scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    document.addEventListener('DOMContentLoaded', scrollToBottom);
    document.querySelector('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.querySelector('input[name="message"]');
        const message = input.value.trim();
        if (!message) return;

        const chatMessages = document.getElementById('chat-messages');
        
        // Add user message
        const userMsgHtml = `
            <div class="flex mb-4 message-fade justify-end">
                <div class="bg-gray-700 text-gray-100 rounded-lg p-3 max-w-[80%] relative border border-gray-600 hover:border-gray-500 transition-all">
                    <i class="fas fa-skull-crossbones absolute -left-4 top-2 text-gray-500"></i>
                    ${message}
                    <div class="absolute bottom-1 right-2 text-xs text-gray-500">
                        ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </div>
                </div>
            </div>`;
        chatMessages.insertAdjacentHTML('beforeend', userMsgHtml);

        // Add loading indicator
        const loadingHtml = `
            <div class="flex mb-4 justify-start" id="loading-indicator">
                <div class="bg-gray-900 text-gray-300 rounded-lg p-3 max-w-[80%] relative border border-gray-600">
                    <i class="fas fa-spider absolute -right-4 top-2 text-gray-500"></i>
                    <div class="flex items-center space-x-2">
                        <div class="animate-spin h-4 w-4 border-2 border-red-500 border-t-transparent rounded-full"></div>
                        <span>Loading...</span>
                    </div>
                </div>
            </div>`;
        chatMessages.insertAdjacentHTML('beforeend', loadingHtml);
        
        scrollToBottom();
        input.value = '';
        window.history.replaceState({}, document.title, window.location.pathname);

        try {
            const response = await fetch('ai-chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `message=${encodeURIComponent(message)}`
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Remove loading indicator
            document.getElementById('loading-indicator')?.remove();

            if (data.error) {
                throw new Error(data.error);
            }

            // Add AI response
            const aiMsgHtml = `
                <div class="flex mb-4 message-fade justify-start">
                    <div class="bg-gray-900 text-gray-300 rounded-lg p-8 max-w-[80%] relative border border-gray-600 hover:border-gray-500 transition-all">
                        <i class="fas fa-spider absolute -right-4 top-2 text-gray-500"></i>
                        ${data.ai_message}
                        <div class="absolute bottom-1 right-2 text-xs text-gray-500">
                            ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </div>
                    </div>
                </div>`;
            chatMessages.insertAdjacentHTML('beforeend', aiMsgHtml);
            
            scrollToBottom();

        } catch (error) {
            console.error('Fetch error:', error);
            document.getElementById('loading-indicator')?.remove();

            const errorHtml = `
                <div class="flex mb-4 justify-start">
                    <div class="bg-red-900 text-gray-300 rounded-lg p-3 max-w-[80%] relative border border-red-600">
                        <i class="fas fa-skull-crossbones absolute -right-4 top-2 text-red-500"></i>
                        Error: ${error.message}
                    </div>
                </div>`;
            chatMessages.insertAdjacentHTML('beforeend', errorHtml);
            scrollToBottom();
        }
    });
    </script>
</body>
</html>

