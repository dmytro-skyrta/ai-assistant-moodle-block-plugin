# ai-assistant-moodle-block-plugin
A Moodle block plugin that integrates an AI virtual assistant powered by OpenRouter.ai, allowing students to interact with an intelligent assistant through text chat to get help with course content.

# Virtual Assistant Block for Moodle

A Moodle block that integrates an AI virtual assistant into your course pages. This plugin enables students to interact with an AI assistant that can answer questions about course content through text chat.

## Features
- Adds a virtual assistant avatar to your Moodle courses
- Connects to OpenRouter.ai API for AI responses
- Provides contextual help based on course content and student information
- Clean, intuitive chat interface
- Prepared for future voice interaction capabilities

## Requirements
- Moodle 4.1+
- OpenRouter API key

## Installation
1. Download the ZIP file from this repository
2. Log in to your Moodle site as an administrator
3. Go to Site Administration > Plugins > Install plugins
4. Upload the ZIP file
5. Follow the on-screen instructions to complete the installation
6. Add the block to your courses or dashboard

## Configuration
Update the API configuration in `api.php` with your OpenRouter API key and preferred AI model.

## Security Note
For production environments, API credentials should be stored securely rather than directly in the code.

## License
This project is licensed under the MIT License with Attribution Copyright (c) [Dmytro Skyrta] [github.com/dmytro-skyrta].
