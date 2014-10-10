<?php
/**
 * @package Emailmanager
 * @version 1.0
 */
/*
Plugin Name: Emailmanager
Plugin URI: http://www.cracklecat.com/wordpress/emailmanager
Description: Uses the Emailmanager API to send email
Version: 1.0
Author: Crackle Cat Software
Author URI: http://www.cracklecat.com/
*/

include("Emailmanager.php");
$emailmanager_error = "";

add_action('admin_menu', 'emailmanager_plugin_menu');

function emailmanager_plugin_menu() {
    add_options_page('Emailmanager', 'Emailmanager', 'manage_options', 'emailmanager', 'emailmanager_plugin_options');
}

function emailmanager_plugin_options() {

    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }
  
    $emailmanager_apikey = get_option('emailmanager_apikey');
    $emailmanager_fromname = get_option('emailmanager_fromname');
    $emailmanager_fromaddress = get_option('emailmanager_fromaddress');

    echo '<div class="wrap">';
    ?>
    <form name="emailmanager_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <input type="hidden" name="emailmanager_hidden" value="yes">
      <?php echo "<h4>" . __( 'Emailmanager Settings', 'emailmanager_trdom' ) . "</h4>"; ?>
      <p><?php _e("You need a <a href=\"http://www.emailmanager.com\">Emailmanager</a> account to use this plugin." ); ?></p>
    
      <table style="width: 500px;">
        <tr>
          <td colspan="2">
            <p><?php _e("Enter your Emailmanager API key corresponding to your Emailmanager server that you want to use. You can retrieve your API key by logging in to your <a href=\"https://emailmanager.com/login\">Emailmanager account</a>" ); ?></p>
          </td>
        </tr>
        <tr>
          <td><strong><?php _e("API Key: " ); ?></strong></td>
          <td>
            <input type="text" name="emailmanager_apikey" style="width: 200px;" value="<?php echo $emailmanager_apikey; ?>" size="20">
          </td>
    </tr>
    <tr>
      <td colspan="2">
        <p><?php _e("Enter an email address that matches one of your confirmed sender signature addresses. You can add signatures to your account <a href=\"https://emailmanager.com/signatures\">here</a>" ); ?></p>
      </td>
    </tr>
    <tr>
      <td><strong><?php _e("From address: " ); ?></strong></td>
      <td>
        <input type="text" name="emailmanager_fromaddress" style="width: 200px;" value="<?php echo $emailmanager_fromaddress; ?>" size="20">
      </td>
    </tr>
    
    <tr>
      <td colspan="2">
      <p><?php _e("This should match the Sender Name of one of your confirmed sender signatures but anything will work here." ); ?></p>
      </td>
    </tr>
    <tr>
      <td><strong><?php _e("From name: " ); ?></strong></td>
      <td>
        <input type="text" name="emailmanager_fromname" style="width: 200px;" value="<?php echo $emailmanager_fromname; ?>" size="20">
      </td>
    </tr>
    
  </table>
    
    <p class="submit">
    <input type="submit" name="Submit" value="<?php _e('Save Settings', 'emailmanager_trdom' ) ?>" />
    </p>
</form>

<form name="emailmanager_form_test" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
  <input type="submit" name="emailmanager_test" value="<?php _e('Test', 'emailmanager_trdom' ) ?>" />
</form>
    <?php 
  
  echo '</div>';

}
if (!function_exists("wp_mail")) :
function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
    global $emailmanager_error; 
    
    define('EMAILMANAGERAPP_MAIL_FROM_NAME', get_option('emailmanager_fromname'));
    define('EMAILMANAGERAPP_MAIL_FROM_ADDRESS', get_option('emailmanager_fromaddress'));
    define('EMAILMANAGERAPP_API_KEY', get_option('emailmanager_apikey')); 
    
    $mail = new Mail_Emailmanager();
    $mail->messagePlain($message);
    
    $mail->to($to);
    $mail->subject($subject);
    
    try {
        $mail->send(); 
        
    } catch (Exception $e) {
        $emailmanager_error = $e->getMessage();
        //wp_die( __("An error occured trying to send mail through Emailmanager. Error: ".$e->getMessage()) );
        return false;
    }  
    $emailmanager_error = "";
    return true;
    
}
endif;

// handle options form
if($_POST['emailmanager_hidden'] == 'yes') {
    //Form data sent

    update_option('emailmanager_apikey', $_POST['emailmanager_apikey']);
    update_option('emailmanager_fromname', $_POST['emailmanager_fromname']);
    update_option('emailmanager_fromaddress', $_POST['emailmanager_fromaddress']);
        
    ?>
    <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
    <?php
} else {
    //Normal page display
    $emailmanager_apikey = get_option('emailmanager_apikey');
    $emailmanager_fromname = get_option('emailmanager_fromname');
    $emailmanager_fromaddress = get_option('emailmanager_fromaddress');
        
}

// handles test
if (isset($_POST['emailmanager_test'])) {
    if (wp_mail(get_option('emailmanager_fromaddress'),'Wordpress Emailmanager Plugin Test','If you are receiving this email then your Wordpress Emailmanager integration is working.') == false) {
        ?><div class="error"><p><strong><?php _e('Emailmanager Test: ' . $emailmanager_error  ); ?></strong></p></div><?php 
    } else {
        ?><div class="updated"><p><strong><?php _e('Test successful. A test message has been sent to: ' .get_option('emailmanager_fromaddress') ); ?></strong></p></div><?php

    }
}