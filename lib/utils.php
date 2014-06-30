<?php
/**
 * MasterControl Class
 * @package Wordpress
 * @subpackage one-theme
 * @since 1.2
 * @author Bryan Haskin
 * @version 1.0
 */


class Singleton
{
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instances of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}

class StyleEnqueueWrapper {
 private static $ins = null;

 private $styles = array();

 public static function instance(){
   is_null(self::$ins) && self::$ins = new self;
   return self::$ins;
 }

 public static function init(){
   add_action('wp_enqueue_scripts', array(self::instance(), 'enqueue'));
 }

 public static function register($hndl, $src, $deps=array(), $ver=null, $media='all'){
   self::instance()->styles[$hndl] = array(
       'src'       => $src,
       'deps'      => $deps,
       'ver'       => $ver,
       'media'    => $media,
   );
 }
 public function enqueue()
 {
     foreach($this->styles as $key => $value)
     {;
         wp_register_style(
             $key,
             $value['src'],
             $value['deps'],
             $value['ver'],
             $value['media']
         );
         wp_enqueue_style($key);
     }
 }
}
class ScriptEnqueueWrapper {
 private static $ins = null;

 private $scripts = array();

 public static function instance(){
   is_null(self::$ins) && self::$ins = new self;
   return self::$ins;
 }

 public static function init(){
   add_action('wp_enqueue_scripts', array(self::instance(), 'enqueue'));
 }

 public static function register($hndl, $src, $deps=array(), $ver=null, $footer=true){
   self::instance()->scripts[$hndl] = array(
       'src'       => $src,
       'deps'      => $deps,
       'ver'       => $ver,
       'footer'    => $footer,
   );
 }
 public function enqueue()
 {
     foreach($this->scripts as $key => $value)
     {
         wp_register_script(
             $key,
             $value['src'],
             $value['deps'],
             $value['ver'],
             $value['footer']
         );
         wp_enqueue_script($key);
     }
 }
}

function parse_classname ($name)
{
  return array(
    'namespace' => array_slice(explode('\\', $name), 0, -1),
    'classname' => join('', array_slice(explode('\\', $name), -1)),
  );
}

function arrayFind($array, $key, $obj) {
    if (isset($array[$key]))
                return $array[$key];
      else
        return $obj;

}

trait registerObj {
    public function register($obj) {
        $objName = parse_classname(get_class($obj))['classname'];
        $this->$objName = $obj;
    }

    public function unregister($obj) {
        $objName = parse_classname(get_class($obj))['classname'];
        unset($this->$objName);
    }

    public function getRegister() {
        $array = Array();
        foreach($this as $key => $value) {
            if (is_object($value)){
                array_push($array, $key);
            }
        }
        return $array;
    }
}

trait assetManager {
    public $assets = array();
    public function getDirectory() {
      $reflection = new ReflectionClass($this);
      $directory = dirname($reflection->getFileName()) . '/';

      return $directory;
    }

    public function getUrl() {
        $masterControl = MasterControl::getInstance();
        if ($masterControl->isParent()) {
            return get_template_directory_uri() . '/lib/modules/' . strtolower(parse_classname(get_class($this))['classname']) . '/';
        } else {
            return get_stylesheet_directory_uri() . '/lib/modules/' . strtolower(parse_classname(get_class($this))['classname']) . '/';
          }
    }

    public function getAssets($manifestLoad=True) {

        if ($manifestLoad && file_exists($this->getDirectory() . "manifest.json")){
            $this->assets = json_decode(file_get_contents($this->getDirectory() . "manifest.json"), true);
        } else {
            if (file_exists($this->getDirectory() . 'css/')){
                $directory = $this->getDirectory() . 'css/';
                $scanned_directory = array_diff(scandir($directory), array('..', '.'));
                $this->assets['css']=array();
                foreach($scanned_directory as $key => $value) {
                    if (pathinfo($value, PATHINFO_EXTENSION) == "css")
                        array_push($this->assets['css'], $value);
                }

            }
            if (file_exists($this->getDirectory() . 'js/')) {
                $directory = $this->getDirectory() . 'js/';
                $scanned_directory = array_diff(scandir($directory), array('..', '.'));
                $this->assets['js']=array();
                foreach($scanned_directory as $key => $value) {
                    if (pathinfo($value, PATHINFO_EXTENSION) == "js")
                        array_push($this->assets['js'], $value);
                }
            }
        }
    }

    public function loadAssets($manifestLoad=True) {
        $this->getAssets($manifestLoad=True);
        foreach($this->assets as $key => $values) {
            if ($key == 'css') {
                foreach($values as $key => $value) {
                    StyleEnqueueWrapper::init();
                    StyleEnqueueWrapper::register( get_class($this) . "-" . basename($value['file'], ".css"),  $this->getUrl() . 'css/' . $value['file'], arrayFind($value, 'deps', array()), arrayFind($value, 'ver', null), arrayFind($value, 'media', 'all'));
                }

            }
            if ($key == 'js') {
                foreach($values as $key => $value) {
                    ScriptEnqueueWrapper::init();
                    ScriptEnqueueWrapper::register( get_class($this) . "-" . basename($value['file'], ".js"),  $this->getUrl() . 'js/' . $value['file'], arrayFind($value, 'ver', null), arrayFind($value,'footer', false));
                }
            }
        }
    }
}
