<?php
/*
 * Resources class
 * Handles all css and javascript resources
 */
namespace FelixOnline\Core;
use FelixOnline\Exceptions\InternalException;

class ResourceManager {
    private $css = array(); // array of css files
    private $js = array(); // array of js files
    private $cssPath; // path for css files
    private $jsPath; // path for JavaScript files

    function __construct($css, $js) {
        if($css) {
            $this->addCSS($css);
        }
        if($js) {
            $this->addJS($js);
        }
        $this->cssPath = 'css/';
        $this->jsPath = 'js/';
    }

    /*
     * Public: Add css files
     *
     * $css - array of css files to load
     *
     * Returns css array
     */
    public function addCSS($css) {
        if(is_array($css)) {
            foreach($css as $key => $value) {
                array_push($this->css, $value);
            }
            return $this->css;
        } else {
            throw new InternalException("CSS files to add is not an array");
        }
    }

    /*
     * Public: Add js files
     *
     * $js - array of js files to load
     *
     * Returns js array
     */
    public function addJS($js) {
        if(is_array($js)) {
            foreach($js as $key => $value) {
                array_push($this->js, $value);
            }
            return $this->js;
        } else {
            throw new InternalException("JS files to add is not an array");
        }
    }

    /*
     * Public: Replace css files
     */
    public function replaceCSS($css) {
        if(is_array($css)) {
            $this->css = $css;
            return $this->css;
        } else {
            throw new InternalException("CSS files to add is not an array");
        }
    }

    /*
     * Public: Replace js files
     */
    public function replaceJS($js) {
        if(is_array($js)) {
            $this->js = $js;
            return $this->js;
        } else {
            throw new InternalException("JS files to add is not an array");
        }
    }

    /*
     * Public: Get css files
     *
     * Returns array of css files paths
     */
    public function getCSS() {
        $data = array();
        $min = array();
        foreach($this->css as $key => $value) {
            if($this->isExternal($value)) {
                $data[$key] = $value;
            } else {
                if($this->isLess($value)) {
                    $value = $this->processLess($value);
                }
                if(PRODUCTION_FLAG == true) {
                    $min[] = $this->minify($value, 'css');
                } else {
                    $data[$key] = $this->getFilename($value, 'css');
                }
            }
        }

        if(PRODUCTION_FLAG == true) { // if in production
            // concatenate minified files
            $content = '';
            $name = '';
            foreach($min as $key => $value) {
                $filename = strstr($value, '.', true);

                $fileContent = file_get_contents($this->getFilename($value, 'css', 'dir'));

                if($fileContent === false) {
                    throw new InternalException('A minified CSS file does not exist when it should');
                }

                $content .= $fileContent;

                if($key == 0) {
                    $name .= $filename;
                } else {
                    $name .= '-'.$filename;
                }
            }

            unset($fileContent);

            if(!is_writable($this->getFilename($name.'.min.css', 'css', 'dir'))) {
                throw new InternalException('The file '.$this->getFilename($name.'.min.css', 'css', 'dir').', or the folder it is in, is not writable.');
            }

            file_put_contents($this->getFilename($name.'.min.css', 'css', 'dir'), $content);

            $data['min'] = $this->getFilename($name.'.min.css', 'css');
        }
        return $data;
    }

    /*
     * Public: Get js files
     *
     * Returns array of js files paths
     */
    public function getJS() {
        $data = array();
        foreach($this->js as $key => $value) {
            if($this->isExternal($value)) {
                $data[$key] = $value;
            } else {
                $data[$key] = $this->getFilename($value, 'js');
            }
        }
        return $data;
    }

    /*
     * Check if file is external
     */
    private function isExternal($file) {
        if(strpos($file, 'http://') !== false
        || strpos($file, 'https://') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Get path to file
     */
    private function getFilename($file, $type, $version = 'url') {
        $theme = new Theme(Settings::get('current_theme'));

        if($version == 'url') {
            $root = $theme->getURL();
        }
        else if($version == 'dir') {
            $root = $theme->getDirectory();
        }
        switch($type) {
            case 'css':
                return $root.$this->cssPath.$file;
                break;
            case 'js':
                return $root.$this->jsPath.$file;
                break;
        }
    }

    /*
     * If file is a less file
     */
    private function isLess($file) {
        if(substr(strrchr($file,'.'),1) == 'less') {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Process less file
     *
     * Returns compiled css filename
     */
    private function processLess($lessfile) {
        $filename = strstr($lessfile, '.', true);
        $cssfile = $this->getFilename($filename.'.css', 'css', 'dir');

        if(PRODUCTION_FLAG && file_exists($cssfile)) {
            return $filename.'.css';
        }

        $parser = new \Less_Parser();
        $parser->parseFile(dirname($cssfile).'/'.$lessfile, STANDARD_URL);
        $css = $parser->getCss();

        if(!is_writable($cssfile)) {
            throw new InternalException('The file '.$cssfile.', or the folder it is in, is not writable.');
        }

        file_put_contents($cssfile, $css);
        return $filename.'.css';
    }

    /*
     * Minify files
     *
     * Returns minified file name
     */
    private function minify($file, $type) {
        switch($type) {
            case 'css':
                // get filename on its own
                $filename = strstr($file, '.', true);
                $minfilename = $filename.'.min.css';

                if(
                    !file_exists($this->getFilename($minfilename, 'css', 'dir'))
                    ||
                    filemtime($this->getFilename($minfilename, 'css', 'dir'))
                    <
                    filemtime($this->getFilename($file, 'css', 'dir'))
                ) {
                    $cssfile = $this->getFilename($file, 'css', 'dir'); // get file location
                    $min = \Minify_CSS_Compressor::process(file_get_contents($cssfile));

                    if(!is_writable($minfilename)) {
                        throw new InternalException('The file '.$minfilename.', or the folder it is in, is not writable.');
                    }

                    file_put_contents($this->getFilename($minfilename, 'css', 'dir'), $min);
                }
                return $minfilename;
                break;
            case 'js':
                return $file; // not supported yet
                break;
        }
    }
}
?>
