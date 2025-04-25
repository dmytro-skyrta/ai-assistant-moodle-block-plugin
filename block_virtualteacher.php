<?php
class block_virtualteacher extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_virtualteacher');
    }

    public function get_content() {
        if ($this->content !== null) return $this->content;

        $this->content = new stdClass;
        $this->content->text = $this->render_content();
        $this->content->footer = '';
        
        return $this->content;
    }

    private function render_content() {
    	global $OUTPUT, $PAGE;
      	$renderer = $PAGE->get_renderer('block_virtualteacher');
    	return $renderer->render_block($this);
    }

    public function applicable_formats() {
        return [
            'all' => true,
            'course' => true,
            'site' => true,
            'my' => true,
        ];
    }

    public function has_config() {
        return false;
    }
}