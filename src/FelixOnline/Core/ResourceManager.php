<?php
/*
 * Resources class
 * Handles all css and javascript resources
 */
namespace FelixOnline\Core;
use FelixOnline\Exceptions\InternalException;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Filter\LessphpFilter;
use Assetic\Filter\ScssphpFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\JSqueezeFilter;

class ResourceManager {
    private $css; // array of css files
    private $js; // array of js files
    private $theme;

    function __construct(Theme $theme, $css = false, $js = false) {
        $this->theme = $theme;

        if($css) {
            $this->addCSS($css);
        }
        if($js) {
            $this->addJS($js);
        }
        $this->css = array();
        $this->js = array();
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
                if($this->isLess($css)) {
                    $this->css[] = new FileAsset($css, array(LessphpFilter));
                } elseif($this->isScss($css)) {
                    $this->css[] = new FileAsset($css, array(ScssphpFilter));
                } else {
                    $this->css[] = new FileAsset($css);
                }
            }
            return $this;
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
                $this->js[] = new FileAsset($js);
            }
            return $this;
        } else {
            throw new InternalException("JS files to add is not an array");
        }
    }

    /*
     * Public: Replace css files
     */
    public function replaceCSS($css) {
        if(is_array($css)) {
            $this->css = array();
            return $this->addCSS($css);
        } else {
            throw new InternalException("CSS files to add is not an array");
        }
    }

    /*
     * Public: Replace js files
     */
    public function replaceJS($js) {
        if(is_array($js)) {
            $this->js = array();
            return $this->addJS($js);
        } else {
            throw new InternalException("JS files to add is not an array");
        }
    }

    /*
     * Public: Get built css files
     *
     * Returns css file path
     */
    public function getCSS() {
        return $this->build($this->css, 'css');
    }

    /*
     * Public: Get js files
     *
     * Returns array of js files paths
     */
    public function getJS() {
        // Strip out externals
        $js = array();
        $jsExt = array();

        foreach($this->js as $jsItem) {
            if($this->isExternal($jsItem)) {
                $jsExt[] = $jsItem;
            } else {
                $js[] = $jsItem;
            }
        }

        return array_merge($this->build($js, 'js'), $jsExt);
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
     * Build data
     */
    private function build($data, $type) {
        if($type != 'css' && $type != 'js') {
            throw new InternalException('Trying to build invalid type');
        }

        if($type == 'css') {
            $fileName = $this->getFilename('built.css', 'css', 'dir');
            $fileName2 = $this->getFilename('built.css', 'css');

            $filter = new CssMinFilter();
        } else {
            $fileName = $this->getFilename('built.css', 'js', 'dir');
            $fileName2 = $this->getFilename('built.css', 'js');

            $filter = new JSqueezeFilter();
        }

        if(PRODUCTION_FLAG == true) { // if in production
            $data = new AssetCollection($data, array($filter));
        } else {
            $data = new AssetCollection($data);
        }

        // Abstract out
        if(
            (
                PRODUCTION_FLAG == true &&
                !file_exists($fileName) &&
                // Age
                true
            ) || PRODUCTION_FLAG == false
        ) {
            $css = $css->dump();

            if(!is_writable($fileName)) {
                throw new InternalException('The file '.$fileName.', or the folder it is in, is not writable.');
            }

            file_put_contents($fileName, $content);
        }

        return $fileName2;
    }

    /*
     * Get path to file
     */
    private function getFilename($file, $type, $version = 'url') {
        if($version == 'url') {
            $root = $this->theme->getURL();
        }
        else if($version == 'dir') {
            $root = $this->theme->getDirectory();
        }
        switch($type) {
            case 'css':
                return $root.'/css'.$file;
                break;
            case 'js':
                return $root.'/js'.$file;
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
     * If file is a less file
     */
    private function isScss($file) {
        if(substr(strrchr($file,'.'),1) == 'scss') {
            return true;
        } else {
            return false;
        }
    }
}
