<?php
// ai-chat.php
session_start();
if ($_SESSION['role'] !== 'main_admin') {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('Africa/Nairobi');
ob_start();

require_once 'db.php';

// DeepSeek API configuration
define('DEEPSEEK_API_KEY', 'sk-e1b7ad523e2d49aea04e1b099ccdbb62');
define('DEEPSEEK_API_URL', "https://api.deepseek.com/chat/completions");

// System knowledge base
$systemKnowledge = [
    'corrections' => [
        'question_patterns' => ['how to correct', 'make correction', 'certificate error', 'fix mistake', 'edit certificate'],
        'response' => "To make certificate corrections:\n\n1. Go to the 'Corrections' tab in the admin panel\n2. Select the certificate needing correction\n3.  Specify the field needing change and provide correct information\n5. Submit for approval\n\nOnly admins can approve corrections."
    ],
    'data_entry' => [
        'question_patterns' => ['enter data', 'add certificate', 'new death record', 'create certificate', 'data entry'],
        'response' => "For data entry:\n\n1. Navigate to 'Data Entry' tab\n2. Fill all required fields (name, date of death, location, etc.)\n3. Upload supporting documents if needed\n4. Review and submit\n\nAll entries are logged for audit purposes."
    ],
    'user_roles' => [
        'question_patterns' => ['user roles', 'permissions', 'admin access', 'what can main_admin do', 'role differences'],
        'response' => "User roles in the system:\n\n- Main Admin: Full access including user management and system settings\n- Admin: Can manage certificates and corrections but not users\n- Data Entry: Can only add new certificates\n- Viewer: Read-only access to reports"
    ],
    'charts' => [
        'question_patterns' => ['charts explained', 'what do the graphs show', 'dashboard statistics', 'chart data', 'interpret graphs'],
        'response' => "Dashboard charts explained:\n\n1. Line Chart: Certificates filled per month in current year\n2. Line Chart: Death rates\n3. County Chart: Death distribution by county\n4.Number of Users\n5. Gender Chart: Male vs female death rates\n6. Top Filler: Most active data entry personnel\n\nData updates in real-time."
    ],
    'general_help' => [
        'question_patterns' => ['help with system', 'how to use', 'system guide', 'admin manual', 'what can this do'],
        'response' => "Death Registry System Help:\n\nAvailable admin functions:\n1. Certificate Corrections\n2. Data Entry\n3. User Management\n4. Dashboard Analytics\n5. AI Assistant\n\nFor specific help, ask about any of these functions."
    ]
];

// Initialize chat history with system context
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        [
            'role' => 'system', 
            'content' => 'You are an AI assistant for the Death Registry System. ' .
                         'First check if questions are about: ' .
                         '1. Certificate corrections ' .
                         '2. Data entry procedures ' .
                         '3. User roles and permissions ' .
                         '4. Dashboard statistics ' .
                         '5. General system help. ' .
                         'For these topics, provide specific answers. ' .
                         'For other topics, use general knowledge.'
        ]
    ];
}

function isSystemQuestion($message, $systemKnowledge) {
    $message = strtolower($message);
    foreach ($systemKnowledge as $topic) {
        foreach ($topic['question_patterns'] as $pattern) {
            if (strpos($message, strtolower($pattern)) !== false) {
                return $topic['response'];
            }
        }
    }
    return false;
}

// Process user input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $userMessage = htmlspecialchars(trim($_POST['message']));
    
    // Check for duplicate messages
    if (isset($_SESSION['last_message']) && $_SESSION['last_message'] === $userMessage) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Duplicate message detected']);
            exit;
        }
        return;
    }
    
    $_SESSION['last_message'] = $userMessage;
    $_SESSION['chat_history'][] = [
        'role' => 'user', 
        'content' => $userMessage,
        'timestamp' => time()
    ];
    
    // First check if this is a system question
    $systemResponse = isSystemQuestion($userMessage, $systemKnowledge);
    
    if ($systemResponse !== false) {
        //  system knowledge response
        $aiMessage = $systemResponse;
        $_SESSION['chat_history'][] = [
            'role' => 'assistant',
            'content' => $aiMessage,
            'timestamp' => time()
        ];
    } else {
        //  call DeepSeek API
        try {
            $data = [
                'model' => 'deepseek-chat',
                'messages' => $_SESSION['chat_history'],
                'temperature' => 0.7,
                'max_tokens' => 1000
            ];
            
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
            error_log('AI Chat Error: ' . $e->getMessage());
            $aiMessage = "Sorry, I encountered an error. Please try again later.";
            $_SESSION['chat_history'][] = [
                'role' => 'assistant', 
                'content' => $aiMessage,
                'timestamp' => time()
            ];
        }
    }
    
    // AJAX response handling
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        ob_end_clean();
        header('Content-Type: application/json');
        http_response_code(200);
        
        echo json_encode([
            'user_message' => $userMessage,
            'ai_message' => $aiMessage,
            'is_system_response' => ($systemResponse !== false)
        ]);
        exit;
    }
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
    <title>Death Registry AI Assistant</title>
    <style>
        /* Custom Scrollbar */
        #chat-messages::-webkit-scrollbar {
            width: 8px;
            background: #1a202c;
        }
        #chat-messages::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 4px;
        }

        /* Message Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-fade {
            animation: fadeIn 0.2s ease-out;
        }

        /* Gothic Elements */
        .gothic-border {
            border: 2px solid #4a5568;
            border-image: linear-gradient(45deg, #4a5568, #2d3748) 1;
            box-shadow: 0 0 15px rgba(74, 85, 104, 0.3);
        }
        
        .skull-divider {
            position: relative;
            height: 2px;
            background: linear-gradient(90deg, transparent, #4a5568, transparent);
            margin: 1rem 0;
        }
        .skull-divider::before {
            content: "☠";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: #1a202c;
            padding: 0 0.5rem;
            color: #e53e3e;
        }
        
        /* Quick Action Buttons */
        .quick-action {
            transition: all 0.2s;
            border: 1px solid #4a5568;
        }
        .quick-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'tomb': '#1a202c',
                        'blood': '#e53e3e',
                        'parchment': '#f0e6d2',
                        'vampire': '#2d3748',
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full p-0 bg-gradient-to-br from-gray-900 to-gray-800 flex flex-col">
    <div class="container mx-auto max-w-5xl flex flex-col p-0 flex-1">
        <div class="bg-gray-800 rounded-lg shadow-2xl flex flex-col gothic-border h-full">
            
            <!-- Enhanced Chat Header -->
            <div class="bg-gradient-to-r from-gray-900 to-gray-800 p-4 sticky top-0 z-50 rounded-t-lg border-b border-gray-700 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-robot text-2xl text-red-500"></i>
                    <div>
                        <h1 class="text-xl font-bold text-gray-200 font-cinzel">Death Registry AI Assistant</h1>
                        <p class="text-sm text-gray-400 font-medieval">Admin support for the mortuary system</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button id="clear-chat" class="px-3 py-1 text-xs bg-gray-700 hover:bg-gray-600 rounded flex items-center">
                        <i class="fas fa-broom mr-1"></i> Clear
                    </button>
                    <button id="help-btn" class="px-3 py-1 text-xs bg-red-800 hover:bg-red-700 rounded flex items-center">
                        <i class="fas fa-question mr-1"></i> Help
                    </button>
                </div>
            </div>
            
            <!-- Quick Action Buttons -->
            <div class="bg-gray-900 p-2 border-b border-gray-700 flex overflow-x-auto space-x-2 px-3">
                <button class="quick-action px-3 py-1 text-xs bg-gray-800 text-gray-300 rounded-full flex items-center whitespace-nowrap">
                    <i class="fas fa-edit mr-1 text-blue-400"></i> How to correct certificates
                </button>
                <button class="quick-action px-3 py-1 text-xs bg-gray-800 text-gray-300 rounded-full flex items-center whitespace-nowrap">
                    <i class="fas fa-user-shield mr-1 text-purple-400"></i> User roles explained
                </button>
                <button class="quick-action px-3 py-1 text-xs bg-gray-800 text-gray-300 rounded-full flex items-center whitespace-nowrap">
                    <i class="fas fa-chart-line mr-1 text-green-400"></i> Understanding charts
                </button>
                <button class="quick-action px-3 py-1 text-xs bg-gray-800 text-gray-300 rounded-full flex items-center whitespace-nowrap">
                    <i class="fas fa-database mr-1 text-yellow-400"></i> Data entry process
                </button>
            </div>
            
            <!-- System Alert Banner -->
            <div id="system-alert" class="bg-yellow-900 text-yellow-100 text-sm p-2 px-4 hidden">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>System maintenance scheduled tonight at 11PM. Expected downtime: 30 minutes.</span>
                <button class="float-right text-yellow-300 hover:text-yellow-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Chat Messages (Scrollable) -->
            <div class="flex-1 p-4 overflow-y-auto" id="chat-messages">
                <!-- System welcome message -->
                <div class="flex mb-4 justify-start">
                    <div class="bg-gray-900 text-gray-300 rounded-lg p-4 max-w-[85%] relative border border-gray-600">
                        <i class="fas fa-robot absolute -right-4 top-2 text-gray-500"></i>
                        <div class="font-bold text-red-400 mb-2">Death Registry Assistant</div>
                        <p>Welcome, Administrator. I can help with:</p>
                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>Certificate corrections and edits</li>
                            <li>Data entry procedures</li>
                            <li>User management questions</li>
                            <li>Dashboard statistics</li>
                            <li>General mortuary system operations</li>
                        </ul>
                        <div class="skull-divider my-3"></div>
                        <p class="text-sm text-gray-400">Ask me anything or use the quick actions above.</p>
                        <div class="absolute bottom-1 right-2 text-xs text-gray-500">
                            <?= date('H:i') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Dynamic messages will appear here -->
                <?php foreach ($_SESSION['chat_history'] as $index => $message): ?>
                    <?php if ($message['role'] !== 'system'): ?>
                        <div class="flex mb-4 message-fade <?= $message['role'] === 'user' ? 'justify-end' : 'justify-start' ?>">
                            <div class="<?= $message['role'] === 'user' ? 'bg-gray-700 text-gray-100' : 'bg-gray-900 text-gray-300' ?> rounded-lg p-4 max-w-[85%] relative border border-gray-600 hover:border-gray-500 transition-all">
                                <?php if ($message['role'] === 'user'): ?>
                                    <i class="fas fa-user-crown absolute -left-4 top-2 text-red-500"></i>
                                <?php else: ?>
                                    <i class="fas fa-robot absolute -right-4 top-2 text-blue-500"></i>
                                <?php endif; ?>
                                <?= nl2br(htmlspecialchars($message['content'])) ?>
                                <div class="absolute bottom-1 right-2 text-xs text-gray-500">
                                    <?= date('H:i', $message['timestamp'] ?? time()) ?>
                                </div>
                                <?php if ($message['role'] === 'assistant'): ?>
                                    <div class="mt-2 pt-2 border-t border-gray-700 flex justify-end space-x-2">
                                        <button class="text-xs text-gray-400 hover:text-gray-200">
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>
                                        <button class="text-xs text-gray-400 hover:text-gray-200">
                                            <i class="fas fa-thumbs-down"></i>
                                        </button>
                                        <button class="text-xs text-gray-400 hover:text-gray-200">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Enhanced Chat Input -->
            <form method="post" class="p-4 border-t border-gray-700 bg-gray-800">
                <div class="flex gap-2 relative">
                    <div class="absolute left-3 top-3 flex space-x-1 text-gray-500">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <input type="text" name="message" id="message-input"
                           class="flex-1 border border-gray-700 bg-gray-900 text-gray-300 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                           placeholder="Ask about certificates, users, or reports..." required
                           autocomplete="off">
                    <div class="absolute right-16 top-3 flex space-x-1 text-gray-500">
                        <button type="button" class="hover:text-gray-400">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button type="button" class="hover:text-gray-400">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                    <button type="submit" 
                            class="bg-gradient-to-r from-red-800 to-red-900 text-gray-200 px-6 py-3 rounded-lg hover:from-red-700 hover:to-red-800 transition-all flex items-center space-x-2 border border-red-900 hover:border-red-800">
                        <i class="fas fa-paper-plane"></i>
                        <span class="hidden md:inline">Send</span>
                    </button>
                </div>
                <div class="mt-2 text-xs text-gray-500 flex justify-between">
                    <div>Press Enter to send, Shift+Enter for new line</div>
                    <div id="character-count">0/1000</div>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Scroll to bottom function
    function scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Character counter
    document.getElementById('message-input').addEventListener('input', function() {
        const count = this.value.length;
        document.getElementById('character-count').textContent = `${count}/1000`;
        if (count > 900) {
            document.getElementById('character-count').classList.add('text-red-400');
        } else {
            document.getElementById('character-count').classList.remove('text-red-400');
        }
    });

    // Quick action buttons
    document.querySelectorAll('.quick-action').forEach(button => {
        button.addEventListener('click', function() {
            const text = this.textContent.trim();
            document.getElementById('message-input').value = text;
            document.getElementById('message-input').focus();
        });
    });

    // Clear chat button
    document.getElementById('clear-chat').addEventListener('click', function() {
        if (confirm('Clear all chat history? This cannot be undone.')) {
            fetch('clear-chat.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('chat-messages').innerHTML = `
                        <div class="flex mb-4 justify-start">
                            <div class="bg-gray-900 text-gray-300 rounded-lg p-4 max-w-[85%] relative border border-gray-600">
                                <i class="fas fa-robot absolute -right-4 top-2 text-blue-500"></i>
                                <p>Chat history has been cleared. How can I assist you?</p>
                                <div class="absolute bottom-1 right-2 text-xs text-gray-500">
                                    ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                </div>
                            </div>
                        </div>`;
                }
            });
        }
    });

    // Help button
    document.getElementById('help-btn').addEventListener('click', function() {
        document.getElementById('message-input').value = "I need help with the admin system";
        document.getElementById('message-input').focus();
    });

    // Handle form submission with AJAX
    document.querySelector('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (!message) return;

        const chatMessages = document.getElementById('chat-messages');
        
        // Add user message
        const userMsgHtml = `
            <div class="flex mb-4 message-fade justify-end">
                <div class="bg-gray-700 text-gray-100 rounded-lg p-4 max-w-[85%] relative border border-gray-600 hover:border-gray-500 transition-all">
                    <i class="fas fa-user-crown absolute -left-4 top-2 text-red-500"></i>
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
                <div class="bg-gray-900 text-gray-300 rounded-lg p-3 max-w-[85%] relative border border-gray-600">
                    <i class="fas fa-robot absolute -right-4 top-2 text-blue-500"></i>
                    <div class="flex items-center space-x-2">
                        <div class="animate-spin h-4 w-4 border-2 border-red-500 border-t-transparent rounded-full"></div>
                        <span>Processing your request...</span>
                    </div>
                </div>
            </div>`;
        chatMessages.insertAdjacentHTML('beforeend', loadingHtml);
        
        scrollToBottom();
        input.value = '';
        document.getElementById('character-count').textContent = '0/1000';

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
                    <div class="bg-gray-900 text-gray-300 rounded-lg p-4 max-w-[85%] relative border border-gray-600 hover:border-gray-500 transition-all">
                        <i class="fas fa-robot absolute -right-4 top-2 text-blue-500"></i>
                        ${data.ai_message}
                        <div class="absolute bottom-1 right-2 text-xs text-gray-500">
                            ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </div>
                        <div class="mt-2 pt-2 border-t border-gray-700 flex justify-end space-x-2">
                            <button class="text-xs text-gray-400 hover:text-gray-200">
                                <i class="fas fa-thumbs-up"></i>
                            </button>
                            <button class="text-xs text-gray-400 hover:text-gray-200">
                                <i class="fas fa-thumbs-down"></i>
                            </button>
                            <button class="text-xs text-gray-400 hover:text-gray-200">
                                <i class="fas fa-copy"></i>
                            </button>
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
                    <div class="bg-red-900 text-gray-300 rounded-lg p-3 max-w-[85%] relative border border-red-600">
                        <i class="fas fa-exclamation-triangle absolute -right-4 top-2 text-red-500"></i>
                        Error: ${error.message}
                    </div>
                </div>`;
            chatMessages.insertAdjacentHTML('beforeend', errorHtml);
            scrollToBottom();
        }
    });

    // Initial scroll to bottom
    document.addEventListener('DOMContentLoaded', scrollToBottom);
    </script>
</body>
</html>
