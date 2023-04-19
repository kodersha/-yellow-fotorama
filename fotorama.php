<?php
// Fotorama extension, https://github.com/kodersha/yellow-fotorama

class YellowFotorama {
    const VERSION = "0.8.22";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }
    
    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name=="fotorama" && ($type=="block" || $type=="inline")) {
            list($pattern, $size) = $this->yellow->toolbox->getTextArguments($text);
            if (is_string_empty($size)) $size = "100%";
            if (is_string_empty($pattern)) {
                $pattern = "unknown";
                $files = $this->yellow->media->clean();
            } else {
                $images = $this->yellow->system->get("coreImageLocation");
                $files = $this->yellow->media->index()->match("#$images$pattern#");
            }
            if ($this->yellow->extension->isExisting("image")) {
                if (!is_array_empty($files)) {
                    $page->setLastModified($files->getModified());
                    $output = '<div class="fotorama-images">';
                        $output .= '<div class="fotorama" data-allowfullscreen="true" data-fit="cover">';
                        foreach ($files as $file) {
                            list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($file->fileName, $size, $size);
                            list($widthInput, $heightInput) = $this->yellow->toolbox->detectImageInformation($file->fileName);
                            if (!$widthInput || !$heightInput) $widthInput = $heightInput = "500";
                            $output .= '<img src="'.htmlspecialchars($src).'"';
                            if ($width && $height) $output .= ' width="'.htmlspecialchars($width).'" height="'.htmlspecialchars($height).'"';
                            $output .= '/>';
                        }
                        $output .= '</div>';
                    $output .= '</div>';
                } else {
                    $page->error(500, "Fotorama '$pattern' does not exist!");
                }
            } else {
                $page->error(500, "Fotorama requires 'image' extension!");
            }
        }
        return $output;
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
            $output = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$extensionLocation}fotorama.css\" />\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}fotorama.js\"></script>\n";
        }
        return $output;
    }
}
