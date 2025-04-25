/**
 * Main JavaScript module for Virtual Teacher block
 *
 * @module     block_virtualteacher/main
 * @copyright  2025 Dmytro Skyrta
 */

define(['jquery', 'core/notification', 'block_virtualteacher/chat-api'], function($, Notification, ChatAPI) {
    
    /**
     * Initialize the module
     */
    var init = function() {
        console.log('Virtual Teacher module initialized');
        
        // Chat button click event
        $('#chat-btn').on('click', function() {
            var chatContainer = $('#chat-container');
            if (chatContainer.is(':visible')) {
                chatContainer.hide();
            } else {
                chatContainer.show();
            }
        });
        
        // Voice button click event
        $('#voice-btn').on('click', function() {
            Notification.alert('Information', 'Voice functionality is not yet implemented');
        });
        
        // Send message button click event
        $('#send-msg').on('click', function() {
            sendMessage();
        });
        
        // Enter key press event
        $('#chat-input').on('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    };
    
    /**
     * Send a message to the API and display the response
     */
    var sendMessage = function() {
        var message = $('#chat-input').val().trim();
        if (!message) {
            return;
        }
        
        // Display user message
        var chatResponse = $('#chat-response');
        $('<div class="user-message">')
            .html('<strong>Sie:</strong> ' + message)
            .appendTo(chatResponse);
        
        // Clear input field
        $('#chat-input').val('');
        
        // Show loading indicator
        var loadingMsg = $('<div id="loading-message" class="ai-message">')
            .html('<em>Nachricht wird gesendet...</em>')
            .appendTo(chatResponse);
        
        // Scroll to bottom
        chatResponse.scrollTop(chatResponse[0].scrollHeight);
        
        // Send message to API
        ChatAPI.sendMessage(message)
            .then(function(response) {
                // Remove loading indicator
                $('#loading-message').remove();
                
                // Display AI response
                if (response.error) {
                    $('<div class="error-message">')
                        .html('<strong>Fehler:</strong> ' + response.response)
                        .appendTo(chatResponse);
                } else {
                    $('<div class="ai-message">')
                        .html('<strong>Virtueller Lehrer:</strong> ' + response.response)
                        .appendTo(chatResponse);
                }
                
                // Scroll to bottom
                chatResponse.scrollTop(chatResponse[0].scrollHeight);
            })
            .catch(function(error) {
                // Remove loading indicator
                $('#loading-message').remove();
                
                // Display error message
                $('<div class="error-message">')
                    .html('<strong>Fehler:</strong> ' + error.message)
                    .appendTo(chatResponse);
                
                // Scroll to bottom
                chatResponse.scrollTop(chatResponse[0].scrollHeight);
                
                // Log error to console
                console.error(error);
            });
    };
    
    return {
        init: init
    };
});