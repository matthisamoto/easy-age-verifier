<?php
/*
Plugin Name: Verify Age
Description: Easy Age Verifier makes it easy for websites to confirm their website visitors are of legal age.
Version:     2.04
Author:      Alex Standiford
Author URI:  http://www.fillyourtaproom.com
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: easyageverifier
*/

namespace eav;

use eav\app\verifier;
use eav\config\customizer;
use eav\config\upgrade;
use eav\config\menu;

if(!defined('ABSPATH')) exit;

if(!class_exists('eav')){

  class eav{

    private static $instance = null;

    private function __construct(){
    }

    /**
     * Fires up the plugin.
     * @return self
     */
    public static function getInstance(){
      if(!isset(self::$instance)){
        self::$instance = new self;
        self::$instance->_defineConstants();
        self::$instance->_includeFiles();
      }

      return self::$instance;
    }

    /**
     * Defines the constants related to Easy Age Verifier
     * @return void
     */
    private function _defineConstants(){
      define('EAV_URL', plugin_dir_url(__FILE__));
      define('EAV_PATH', plugin_dir_path(__FILE__));
      define('EAV_ASSETS_URL', EAV_URL.'lib/assets/');
      define('EAV_ASSETS_PATH', EAV_PATH.'lib/assets/');
      define('EAV_TEMPLATES_PATH', EAV_ASSETS_PATH.'templates/');
      define('EAV_TEXT_DOMAIN', 'easyageverifier');
      define('EAV_PREFIX', 'eav');
    }

    /**
     * Grabs the files to include, and requires them
     * @return void
     */
    private function _includeFiles(){
      $includes = array(

        //configuration classes
        'config/customizer.php',
        'config/option.php',
        'config/upgrade.php',
        'config/menu.php',

        //App classes
        'app/age.php',
        'app/verification.php',
        'app/verifier.php',

        //Extra classes
        'extras/wpApi.php',

      );

      foreach($includes as $include){
        require_once(EAV_PATH.'lib/'.$include);
      }
    }
  }
}

//Let's rock 'n roll
eav::getInstance();

/**
 * Initializes the verifier form
 * @return void
 */
function init(){
  if(!is_admin()){
    verifier::doFormActions();
  }
}

add_action('init', __NAMESPACE__.'\\init');
add_action('customize_preview_init', __NAMESPACE__.'\\init');

/**
 * Initializes the customizer on the admin
 * @return void
 */
function customize_init(){
  customizer::register();
}

add_action('customize_register', __NAMESPACE__.'\\customize_init');

/**
 * Initializes the menu item on the admin
 * @return void
 */
function menu_init(){
  menu::register();
}

add_action('admin_menu', __NAMESPACE__.'\\menu_init');


/**
 * Upgrades the legacy database to the new database format on plugin activation
 * @return void
 */
function upgrade_legacy_data(){
  upgrade::legacyDatabase();
}

register_activation_hook(__FILE__, __NAMESPACE__.'\\upgrade_legacy_data');

function eav_admin_styles_init(){
  $styles = array(
    'settings.css' => EAV_ASSETS_URL.'css/settings.css',
  );
  $styles = apply_filters(EAV_PREFIX.'_admin_styles', $styles);
  foreach($styles as $style => $path){
    wp_enqueue_style($style, $path);
  }
  $scripts = array();
  $scripts = apply_filters(EAV_PREFIX.'_admin_scripts', $scripts);
  foreach($scripts as $script => $parameters){
    wp_enqueue_script($script, $parameters['src'], $parameters['dependencies'], $parameters['version'], $parameters['in_footer']);
  }
}

add_action('admin_enqueue_scripts', __NAMESPACE__.'\\eav_admin_styles_init');

/**
 * Redirects to the customizer page when the eav-options page is opened
 * @return void
 */
function redirect_to_customizer(){
  global $_GET;
  if($_GET['page'] == 'eav-options'){
    wp_redirect(admin_url().'customize.php?autofocus[section]=eav_section');
    die;
  }
}

add_action('admin_init', __NAMESPACE__.'\\redirect_to_customizer');