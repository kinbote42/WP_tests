<?php
/*
Plugin Name: ReST API Oauth2 Tests
Description: Just a small plugin to test calling ReST API from Wordpress.
Version: 1.0
Author: Steve Moody
*/

if (!defined('ABSPATH')){
  exit;
}

function chft_register_settings() {
  register_setting('chft_settings_group', 'chft_position');
  register_setting('chft_settings_group', 'chft_authtoken');
  update_option( 'simple_links', '<a href="">Link 1</a>');
}
add_action('admin_init', 'chft_register_settings');

function chft_add_settings_page() {
  add_options_page(
    'ReST test Page Settings',  // text in the tab on the webpage (firefox)
    'ReST test', // controls the text on the settings list in wp_admin area
    'manage_options',  // controls who can see this page?
    'chft-settings',  // slug for this object (think unique name)
    'chft_render_settings_page' // callback to render the page (defined below)
  );
}
add_action('admin_menu', 'chft_add_settings_page');

function chft_render_settings_page() {
  ?>
  <div class="wrap">
    <h1>ReST test settings</h1>
    <form method="post" action="options.php">
      <?php settings_fields('chft_settings_group'); ?> <!-- adds the values for any setting registerd in this group (see chft_register_settings above) -->
      <?php do_settings_sections('chft_settings_group'); ?>

      <table class="form-table">
        <tr valign="top">
          <th scope="row">Position</th>
          <td>
            <?php $position = get_option('chft_position'); ?>
            
            <label><input type="radio" name="chft_position" value="header" <?php checked($position, 'header'); ?>> Header</label><br>
            <label><input type="radio" name="chft_position" value="footer" <?php checked($position, 'footer'); ?>> Footer</label>
          </td>
        </tr>
        
        <tr>
          <th><h2>Credentials</h2></th>
        </tr>
        
        <tr>
          <th scope="row">Token</th>
          <td><input type="text" name="chft_authtoken" value="<?php echo esc_attr(get_option('chft_authtoken', '')); ?>" size="250"></td>
        </tr>
      </table>
      
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

function chft_display_text_auth() {
  $api_url = 'http://localhost:8000/items/random';
  $response = "";
  
  // now make our call.
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//  $token = 'Authorization: Bearer ' . get_option('chft_authtoken');
  $token = get_option('chft_authtoken');
  $headers = [
    'Content-Type: application/json',
    'accept: application/json',
    'Authorization: Bearer ' . $token
  ];

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $response = curl_exec($ch);
   
  if (curl_errno($ch)) {
    //echo 'cURL error: ' . curl_error($ch);
  }
  
  // finally display the value.
  $jsres = json_decode($response, true);
  if (!is_null($jsres) && array_key_exists('Name', $jsres) && !is_null($jsres['Name'])) {
    echo $jsres['Name'];
  }
  curl_close($ch);
}

function chft_add_display_hooks() {
  $position = get_option('chft_position', 'header');

  if($position === 'header') {
    add_action('wp_head', 'chft_display_text_auth');
  } else {
    add_action('wp_footer', 'chft_display_text_auth');
  }
}
add_action('wp', 'chft_add_display_hooks');

?>
