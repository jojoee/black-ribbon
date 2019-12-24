<?php
/*
Plugin Name: Black Ribbon
Plugin URI: https://wordpress.org/plugins/black-ribbon/
Description: Automatically add black ribbon into sites corner
Version: 1.1.2
Author: Nathachai Thongniran
Author URI: http://jojoee.com/
Text Domain: brb
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

define( 'BRB_BASE_FILE', plugin_basename( __FILE__ ) );

class Black_Ribbon {

  public function __construct() {
    $this->is_debug = false;
    $this->menu_page = 'black-ribbon';
    $this->option_group_name = 'brb_option_group';
    $this->option_field_name = 'brb_option_field';
    $this->options = get_option( $this->option_field_name );

    // set default prop
    // for only
    // - first time or
    // - no summiting form
    $this->brb_set_default_prop();

    add_action( 'admin_menu', array( $this, 'brb_add_menu' ) );
    add_action( 'admin_init', array( $this, 'brb_page_init' ) );

    // add plugin link
    add_filter( 'plugin_action_links', array( $this, 'brb_plugin_action_links' ), 10, 4 );

    // hook
    add_action( 'wp_enqueue_scripts', array( $this, 'brb_enqueue_scripts' ) );
    add_action( 'wp_footer', array( $this, 'brb_foot' ) );
  }

  /*================================================================ Util
   */

  /**
   * Check is null or empty string
   *
   * @see http://stackoverflow.com/questions/381265/better-way-to-check-variable-for-null-or-empty-string
   * 
   * @param  string  $str
   * @return boolean
   */
  private function is_null_or_empty_string( $str ) {
    return ( ! isset( $str ) || trim( $str ) === '' );
  }

  /*================================================================ Debug
   */

  private function dd( $var = null, $is_die = true ) {
    echo '<pre>';
    print_r( $var );
    echo '</pre>';

    if ( $is_die ) die();
  }

  // debug purpose
  private function da( $var = null ) {
    $this->dd( $var, false );
  }

  // debug purpose
  private function dhead( $head, $var, $is_die = false ) {
    echo '<div class="debug-box">';
    echo '================';
    echo ' ' . $head . ' ';
    echo '================';
    echo '<br>';
    $this->dd( $var, $is_die );
    echo '</div>';
  }

  // debug purpose
  private function dump( $is_die = false ) {
    $this->da( $this->options, $is_die );
  }

  // debug purpose
  private function reset() {
    update_option( $this->option_field_name, array() );
  }

  // for this plugin only
  private function show_all_ribbon_position() { ?>
    <div class="brb-ribbon brb-pos-top-left">&nbsp;</div>
    <div class="brb-ribbon brb-pos-top-right">&nbsp;</div>
    <div class="brb-ribbon brb-pos-bottom-left">&nbsp;</div>
    <div class="brb-ribbon brb-pos-bottom-right">&nbsp;</div>
    <?php
  }

  /*================================================================ Public
   */

  public function brb_foot() {
    $options = $this->options;
    $is_enabled = $options['brb_field_is_enabled'];
    $is_enabled_on_mobile = $options['brb_field_is_enabled_on_mobile'];
    $position = $options['brb_field_ribbon_position'];
    $ribbon_url = $options['brb_field_ribbon_url'];
    $is_open_new_tab = $options['brb_field_is_open_new_tab'];

    $has_url = $this->is_null_or_empty_string( $ribbon_url ) ? false : true;
    $custom_attr = ( $is_open_new_tab ) ? 'target="_blank"' : '';
    $custom_css = 'brb-pos-' . $position . ' ';
    if ( ! $is_enabled_on_mobile ) { $custom_css .= 'brb-no-mobile'; }

    if ( $is_enabled ) {
      if ( $has_url ) {
        printf( '<a class="brb-ribbon %s" href="%s" %s></a>',
          $custom_css,
          $ribbon_url,
          $custom_attr
        );

      } else {
        printf( '<div class="brb-ribbon %s"></div>',
          $custom_css
        );
      }
    }
  }

  public function brb_enqueue_scripts() {
    $is_enabled = $this->options['brb_field_is_enabled'];

    if ( $is_enabled ) {
      wp_enqueue_style( 'brb-main-style', plugins_url( 'css/main.css', __FILE__ ) );
    }
  }

  /*================================================================ Callback
   */

  public function brb_field_is_enabled_callback() {
    $field_id = 'brb_field_is_enabled';
    $field_name = $this->option_field_name . "[$field_id]";
    $field_value = 1;
    $check_attr = checked( 1, $this->options[ $field_id ], false );

    printf(
      '<input type="checkbox" id="%s" name="%s" value="%s" %s />',
      $field_id,
      $field_name,
      $field_value,
      $check_attr
    );
  }

  public function brb_field_is_enabled_on_mobile_callback() {
    $field_id = 'brb_field_is_enabled_on_mobile';
    $field_name = $this->option_field_name . "[$field_id]";
    $field_value = 1;
    $check_attr = checked( 1, $this->options[ $field_id ], false );

    printf(
      '<input type="checkbox" id="%s" name="%s" value="%s" %s />',
      $field_id,
      $field_name,
      $field_value,
      $check_attr
    );
  }

  public function brb_field_ribbon_position_callback() {
    $field_id = 'brb_field_ribbon_position';
    $field_name = $this->option_field_name . "[$field_id]";
    $positions = array(
      array(
        'value'     => 'top-left',
        'name'      => 'Top left'
      ),
      array(
        'value'     => 'top-right',
        'name'      => 'Top right'
      ),
      array(
        'value'     => 'bottom-left',
        'name'      => 'Bottom left'
      ),
      array(
        'value'     => 'bottom-right',
        'name'      => 'Bottom right'
      )
    );

    printf( '<select id="%s" name="%s">', $field_id, $field_name );
    foreach ( $positions as $position ) {
      $value = $position['value'];
      $name = $position['name'];
      $select_attr = selected( $this->options[ $field_id ], $value, false );

      printf( '<option value="%s" %s>%s</option>',
        $value,
        $select_attr,
        $name
      );
    }
    echo '</select>';
  }

  public function brb_field_ribbon_url_callback() {
    $field_id = 'brb_field_ribbon_url';
    $field_name = $this->option_field_name . "[$field_id]";
    $field_value = $this->options[ $field_id ];

    printf(
      '<input type="text" id="%s" placeholder="Enter ribbon url" name="%s" value="%s" />',
      $field_id,
      $field_name,
      $field_value
    );
  }

  public function brb_field_is_open_new_tab_callback() {
    $field_id = 'brb_field_is_open_new_tab';
    $field_name = $this->option_field_name . "[$field_id]";
    $field_value = 1;
    $check_attr = checked( 1, $this->options[ $field_id ], false );

    printf(
      '<input type="checkbox" id="%s" name="%s" value="%s" %s />',
      $field_id,
      $field_name,
      $field_value,
      $check_attr
    );
  }

  /*================================================================ Option
   */

  public function brb_set_default_prop() {
    // default
    // 
    // [
    //   'brb_field_is_enabled'             => 1
    //   'brb_field_is_enabled_on_mobile'   => 1
    //   'brb_field_ribbon_position'        => 'top-right'
    //   'brb_field_ribbon_url'             => ''
    //   'brb_field_is_open_new_tab'        => 1
    // ]

    $options = $this->options;

    if ( ! isset( $options['brb_field_is_enabled'] ) ) $options['brb_field_is_enabled'] = 1;
    if ( ! isset( $options['brb_field_is_enabled_on_mobile'] ) ) $options['brb_field_is_enabled_on_mobile'] = 1;

    if ( ! isset( $options['brb_field_ribbon_position'] ) || ( $options['brb_field_ribbon_position'] === '' ) ) {
      $options['brb_field_ribbon_position'] = 'top-right';
    }

    if ( ! isset( $options['brb_field_ribbon_url'] ) ) $options['brb_field_ribbon_url'] = '';
    if ( ! isset( $options['brb_field_is_open_new_tab'] ) ) $options['brb_field_is_open_new_tab'] = 1;

    $this->options = $options;
  }

  public function brb_add_menu() {
    // args
    // - page title
    // - menu title
    // - capability
    // - menu slug (menu page)
    // - function
    add_options_page(
      'Black Ribbon',
      'Black Ribbon',
      'manage_options',
      $this->menu_page,
      array( $this, 'brb_admin_page' )
    );
  }

  /**
   * Options page callback
   * 
   * TODO: relocate style
   */
  public function brb_admin_page() { ?>
    <?php if ( $this->is_debug ) $this->dump(); ?>
    <div class="wrap">
      <h1>Black Ribbon</h1>
      <form method="post" action="options.php">
        <?php
          settings_fields( $this->option_group_name );
          do_settings_sections( $this->menu_page );
          submit_button();
        ?>
      </form>
    </div>
    <style>
    .debug-box {
      padding: 12px 0;
    }
    .form-table th,
    .form-table td {
      padding: 0;
      padding-bottom: 6px;
      line-height: 30px;
      height: 30px;
    }
    </style>
    <?php
  }

  public function brb_page_init() {
    $section_id = 'brb_setting_section_id';

    register_setting(
      $this->option_group_name,
      $this->option_field_name,
      array( $this, 'sanitize' )
    );

    // section
    add_settings_section(
      $section_id,
      'Settings',
      array( $this, 'print_section_info' ),
      $this->menu_page
    );

    // option field(s)
    // - is_enabled
    // - is_enabled_on_mobile
    // - ribbon_position
    // - ribbon_url
    // - is_open_new_tab
    add_settings_field(
      'brb_field_is_enabled',
      'Enable',
      array( $this, 'brb_field_is_enabled_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'brb_field_is_enabled_on_mobile',
      'Enable on mobile',
      array( $this, 'brb_field_is_enabled_on_mobile_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'brb_field_ribbon_position',
      'Ribbon position',
      array( $this, 'brb_field_ribbon_position_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'brb_field_ribbon_url',
      'Ribbon URL (with http / https)',
      array( $this, 'brb_field_ribbon_url_callback' ),
      $this->menu_page,
      $section_id
    );

    add_settings_field(
      'brb_field_is_open_new_tab',
      'Ribbon URL (Open new tab)',
      array( $this, 'brb_field_is_open_new_tab_callback' ),
      $this->menu_page,
      $section_id
    );
  }

  public function print_section_info() {
    print 'Enter your settings below:';
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function sanitize( $input ) {
    $result = array();

    // text
    $text_input_ids = array(
      'brb_field_ribbon_position',
      'brb_field_ribbon_url'
    );
    foreach ( $text_input_ids as $text_input_id ) {
      $result[ $text_input_id ] = isset( $input[ $text_input_id ] )
        ? sanitize_text_field( $input[ $text_input_id ] )
        : '';
    }

    // number
    $number_input_ids = array(
      'brb_field_is_enabled',
      'brb_field_is_enabled_on_mobile',
      'brb_field_is_open_new_tab'
    );
    foreach ( $number_input_ids as $number_input_id ) {
      $result[ $number_input_id ] = isset( $input[ $number_input_id ] )
        ? sanitize_text_field( $input[ $number_input_id ] )
        : 0;
    }

    return $result;
  }

  public function brb_plugin_action_links( $links, $plugin_file ) {
    $plugin_link = array();

    if ( $plugin_file == BRB_BASE_FILE ) {
      $plugin_link[] = '<a href="' . admin_url( 'options-general.php?page=' . $this->menu_page ) . '">Settings</a>';
    }

    return array_merge( $links, $plugin_link );
  }
}

$black_ribbon = new Black_Ribbon();
