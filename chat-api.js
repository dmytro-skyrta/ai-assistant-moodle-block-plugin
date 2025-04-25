/**
 * Chat API module for Virtual Teacher block
 *
 * @module     block_virtualteacher/chat-api
 * @copyright  2025 Dmytro Skyrta
 */

define([], function() {
    
    /**
     * Chat API handler
     */
    var ChatAPI = {
        /**
         * Send a message to the API and return a promise with the response
         * 
         * @param {string} message - The message to send
         * @return {Promise} Promise object that resolves with the API response
         */
        sendMessage: function(message) {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', M.cfg.wwwroot + '/blocks/virtualteacher/api.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                resolve(response);
                            } catch (e) {
                                reject(new Error('Failed to parse API response: ' + e.message));
                            }
                        } else {
                            reject(new Error('API request failed with status: ' + xhr.status));
                        }
                    }
                };
                xhr.onerror = function() {
                    reject(new Error('Network error occurred'));
                };
                
                // Send the message
                xhr.send(JSON.stringify({
                    prompt: message
                }));
            });
        }
    };
    
    return ChatAPI;
});