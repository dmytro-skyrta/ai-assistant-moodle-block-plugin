<?php
// Determine if the script is running from command line
$isCLI = (php_sapi_name() === 'cli');

// If running from Moodle web environment, load necessary Moodle files
if (!$isCLI) {
    require_once(__DIR__.'/../../config.php');
    require_login();
    header('Content-Type: application/json');
    
    // Get user and course information from Moodle
    global $USER, $COURSE;
    $username = $USER->firstname;
    $coursename = $COURSE->fullname ?? 'Moodle-Kurs';
    
    // Get input data from request
    $input = json_decode(file_get_contents('php://input'), true);
    $prompt = $input['prompt'] ?? '';
    
    // Log input data for debugging
    error_log('API input: ' . json_encode($input));
} else {
    // If running from command line, use test data or command line parameters
    echo "Running in CLI mode...\n";
    
    // Get question from command line arguments or use test question
    $prompt = isset($argv[1]) ? $argv[1] : "Erkläre den Unterschied zwischen Photosynthese und Zellatmung in 3 Sätzen.";
    $username = isset($argv[2]) ? $argv[2] : 'TestUser';
    $coursename = isset($argv[3]) ? $argv[3] : 'Biologie 101';
    
    echo "Using prompt: $prompt\n";
    echo "User: $username\n";
    echo "Course: $coursename\n\n";
}

// API Configuration
// Changed to Qwen model, which was used in the test script and worked successfully
$MODEL = "qwen/qwen2.5-vl-3b-instruct:free";
$API_KEY = "sk-or-v1-...";
$BASE_URL = "https://openrouter.ai/api/v1/chat/completions";

// Add context to the request for better answers
$contextualPrompt = "Du bist ein virtueller Lehrer in einem Moodle-Kurs namens '$coursename'. 
Du hilfst dem Schüler '$username' bei Fragen zum Kursinhalt. 
Sei hilfsbereit, freundlich und pädagogisch wertvoll. Hier ist die Frage des Schülers: $prompt";

// Create request without specifying route, which might cause problems
$data = [
    "model" => $MODEL,
    "messages" => [["role" => "user", "content" => $contextualPrompt]],
    "max_tokens" => 500
    // Removed "route" field
];

// In CLI mode, output debug information
if ($isCLI) {
    echo "Sending request to OpenRouter API...\n";
    echo "Request data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
}

try {
    $ch = curl_init($BASE_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    // Check for certificate file in current directory
    if (file_exists(__DIR__ . '/cacert.pem')) {
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $API_KEY",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds timeout
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Log response for debugging
    if (!$isCLI) {
        error_log('API response: ' . $response);
    } else {
        echo "HTTP Status Code: $httpCode\n";
        
        if ($error) {
            echo "CURL Error: $error\n";
        }
        
        echo "Raw API Response: $response\n\n";
    }
    
    if ($error) {
        throw new Exception("CURL Error: $error");
    }
    
    if ($httpCode !== 200) {
        if (!$isCLI) {
            error_log('HTTP Status Code: ' . $httpCode);
            error_log('Raw API Response: ' . $response);
        }
        throw new Exception("API responded with status code: $httpCode");
    }
    
    // Parse response
    $decodedResponse = json_decode($response, true);
    
    if (is_null($decodedResponse)) {
        if (!$isCLI) {
            error_log('JSON decode error: ' . json_last_error_msg());
        } else {
            echo "JSON decode error: " . json_last_error_msg() . "\n";
        }
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }
    
    // In CLI mode show full response structure
    if ($isCLI) {
        echo "Response structure: " . print_r($decodedResponse, true) . "\n\n";
    } else {
        error_log('Full response structure: ' . print_r($decodedResponse, true));
    }
    
    // Extract AI response with more robust error handling
    $aiResponse = null;
    
    if (isset($decodedResponse['choices']) && is_array($decodedResponse['choices'])) {
        foreach ($decodedResponse['choices'] as $choice) {
            if (isset($choice['message']) && isset($choice['message']['content'])) {
                $aiResponse = $choice['message']['content'];
                break;
            } elseif (isset($choice['text'])) {
                // Some models might return text directly
                $aiResponse = $choice['text'];
                break;
            }
        }
    }
    
    // If still no response, try other possible response formats
    if (!$aiResponse && isset($decodedResponse['output'])) {
        $aiResponse = $decodedResponse['output'];
    }
    
    if (!$aiResponse && isset($decodedResponse['response'])) {
        $aiResponse = $decodedResponse['response'];
    }
    
    // Check for error in OpenRouter response
    if (!$aiResponse && isset($decodedResponse['error'])) {
        $errorMessage = "OpenRouter API Error: ";
        if (isset($decodedResponse['error']['message'])) {
            $errorMessage .= $decodedResponse['error']['message'];
            if (isset($decodedResponse['error']['metadata']) && isset($decodedResponse['error']['metadata']['raw'])) {
                // Try to extract more detailed error information
                $rawError = json_decode($decodedResponse['error']['metadata']['raw'], true);
                if (isset($rawError['error']['message'])) {
                    $errorMessage .= " - " . $rawError['error']['message'];
                }
            }
        } else {
            $errorMessage .= json_encode($decodedResponse['error']);
        }
        throw new Exception($errorMessage);
    }
    
    if (!$aiResponse) {
        // Log entire response for debugging
        if (!$isCLI) {
            error_log('Could not extract answer from response: ' . $response);
        } else {
            echo "Error: Could not extract answer from response.\n";
        }
        throw new Exception("Konnte keine Antwort aus der API-Antwort extrahieren");
    }
    
    // Output response depending on execution mode
    if ($isCLI) {
        echo "AI response received: $aiResponse\n";
    }
    
    // Format final response
    $result = ['response' => $aiResponse];
    
    // Output JSON response
    if ($isCLI) {
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    // Log error for administrator
    if (!$isCLI) {
        error_log('Virtual Teacher API Error: ' . $e->getMessage());
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    // Send user a friendly error message
    $errorResponse = [
        'response' => "Entschuldigung, ich kann im Moment nicht antworten. Bitte versuchen Sie es später noch einmal.",
        'error' => true,
        'debug' => $e->getMessage() // Only for development, remove in production
    ];
    
    if ($isCLI) {
        echo json_encode($errorResponse, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo json_encode($errorResponse);
    }
}