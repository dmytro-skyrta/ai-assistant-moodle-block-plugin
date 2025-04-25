<?php
defined('MOODLE_INTERNAL') || die();

class block_virtualteacher_renderer extends plugin_renderer_base {

    public function render_block($instance) {
        // Start HTML for block
        $output = html_writer::start_tag('div', array('class' => 'block_virtualteacher'));

        // Add virtual teacher image
        $output .= html_writer::tag('img', '', array(
            'src' => $this->image_url('teacher', 'block_virtualteacher'),
            'alt' => 'Virtual Teacher',
            'class' => 'virtual-teacher-image'
        ));

        // Action buttons
        $output .= html_writer::start_tag('div', array('class' => 'actions'));
        $output .= html_writer::tag('button', 'Chat', array('id' => 'chat-btn', 'class' => 'chat-btn'));
        $output .= html_writer::tag('button', 'Voice', array('id' => 'voice-btn', 'class' => 'voice-btn'));
        $output .= html_writer::end_tag('div');

        // Chat container (hidden by default)
        $output .= html_writer::start_tag('div', array('id' => 'chat-container', 'class' => 'chat-container', 'style' => 'display:none;'));
        
        // Message history area
        $output .= html_writer::start_tag('div', array('id' => 'chat-response', 'class' => 'chat-response'));
        $output .= html_writer::end_tag('div');

        // Text input field
        $output .= html_writer::tag('textarea', '', array('id' => 'chat-input', 'placeholder' => 'Ihre Frage...'));

        // Send button
        $output .= html_writer::tag('button', 'Senden', array('id' => 'send-msg'));

        $output .= html_writer::end_tag('div'); // Close chat container
        $output .= html_writer::end_tag('div'); // Close main block div

        // Add embedded JavaScript with AJAX functionality
        $output .= html_writer::script('
            document.addEventListener("DOMContentLoaded", function() {
                console.log("DOM loaded with embedded script");
                
                // Chat button functionality
                document.getElementById("chat-btn").addEventListener("click", function() {
                    console.log("Chat button pressed through embedded script");
                    var chatContainer = document.getElementById("chat-container");
                    if (chatContainer.style.display === "none" || chatContainer.style.display === "") {
                        chatContainer.style.display = "block";
                    } else {
                        chatContainer.style.display = "none";
                    }
                });
                
                // Voice button functionality
                document.getElementById("voice-btn").addEventListener("click", function() {
                    alert("Voice functionality not yet implemented.");
                });
                
                // Send message functionality
                document.getElementById("send-msg").addEventListener("click", sendMessage);
                
                // Enter key functionality
                document.getElementById("chat-input").addEventListener("keypress", function(e) {
                    if (e.key === "Enter" && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });
                
                function sendMessage() {
                    var message = document.getElementById("chat-input").value.trim();
                    if (!message) return;
                    
                    // Display user message
                    var chatResponse = document.getElementById("chat-response");
                    var userMsg = document.createElement("div");
                    userMsg.className = "user-message";
                    userMsg.innerHTML = "<strong>Sie:</strong> " + message;
                    chatResponse.appendChild(userMsg);
                    
                    // Clear input field
                    document.getElementById("chat-input").value = "";
                    
                    // Show loading indicator
                    var loadingMsg = document.createElement("div");
                    loadingMsg.id = "loading-message";
                    loadingMsg.className = "ai-message";
                    loadingMsg.innerHTML = "<em>Nachricht wird gesendet...</em>";
                    chatResponse.appendChild(loadingMsg);
                    
                    // Scroll down
                    chatResponse.scrollTop = chatResponse.scrollHeight;
                    
                    // AJAX request to api.php
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "' . $instance->page->url->out(false) . '/../../blocks/virtualteacher/api.php", true);
                    xhr.setRequestHeader("Content-Type", "application/json");
                    
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            // Remove loading message
                            document.getElementById("loading-message").remove();
                            
                            if (xhr.status === 200) {
                                try {
                                    var response = JSON.parse(xhr.responseText);
                                    
                                    var aiMsg = document.createElement("div");
                                    
                                    if (response.error) {
                                        aiMsg.className = "error-message";
                                        aiMsg.innerHTML = "<strong>Fehler:</strong> " + response.response;
                                    } else {
                                        aiMsg.className = "ai-message";
                                        aiMsg.innerHTML = "<strong>Virtueller Lehrer:</strong> " + response.response;
                                    }
                                    
                                    chatResponse.appendChild(aiMsg);
                                } catch (e) {
                                    // JSON parsing error
                                    var errorMsg = document.createElement("div");
                                    errorMsg.className = "error-message";
                                    errorMsg.innerHTML = "<strong>Fehler:</strong> Ungültige Antwort vom Server.";
                                    chatResponse.appendChild(errorMsg);
                                    console.error("JSON parsing error:", e);
                                }
                            } else {
                                // HTTP error
                                var errorMsg = document.createElement("div");
                                errorMsg.className = "error-message";
                                errorMsg.innerHTML = "<strong>Fehler:</strong> Kommunikationsproblem (HTTP " + xhr.status + ")";
                                chatResponse.appendChild(errorMsg);
                                console.error("HTTP error:", xhr.status);
                            }
                            
                            // Scroll down
                            chatResponse.scrollTop = chatResponse.scrollHeight;
                        }
                    };
                    
                    xhr.onerror = function() {
                        // Remove loading message
                        document.getElementById("loading-message").remove();
                        
                        // Network error
                        var errorMsg = document.createElement("div");
                        errorMsg.className = "error-message";
                        errorMsg.innerHTML = "<strong>Fehler:</strong> Netzwerkproblem bei der Kommunikation mit dem Server.";
                        chatResponse.appendChild(errorMsg);
                        
                        // Scroll down
                        chatResponse.scrollTop = chatResponse.scrollHeight;
                        console.error("Network error");
                    };
                    
                    // Send the request
                    xhr.send(JSON.stringify({
                        prompt: message
                    }));
                }
            });
        ');

        return $output;
    }
}