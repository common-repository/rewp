<?php
/**
 * Performs all sanitization functions required to save the option values to
 * the database.
 *
 * @package Re:WP
 */
 
/**
 * Performs all sanitization functions required to save the option values to
 * the database.
 *
 * This will also check the specified nonce and verify that the current user has
 * permission to save the data.
 *
 * @package Re:WP
 */
class rewp_Serializer
{
 
    /**
     * Initializes the function by registering the save function with the
     * admin_post hook so that we can save our options to the database.
     */
    public function init()
    {
        add_action('admin_post', array( $this, 'save' ));
    }
 
    /**
     * Validates the incoming nonce value, verifies the current user has
     * permission to save the value from the options page and saves the
     * option to the database.
     */
    public function save()
    {
 
        // First, validate the nonce and verify the user as permission to save.
        if (! ($this->has_valid_nonce() && current_user_can('manage_options'))) {
            $this->redirect();
        }
 
        if (sanitize_hex_color_no_hash($_POST['chrome-bar-color']) != null) {
            update_option('rewp_chrome-bar-color', sanitize_hex_color_no_hash($_POST['chrome-bar-color']));
        } elseif ($_POST['chrome-bar-color'] == "") {
            update_option('rewp_chrome-bar-color', '');
        }

        if (sanitize_text_field($_POST['g-analytics-id']) != null) {
            update_option('rewp_g-analytics-id', sanitize_text_field($_POST['g-analytics-id']));
        } elseif ($_POST['g-analytics-id'] == "") {
            update_option('rewp_g-analytics-id', '');
        }

        update_option('rewp_add-font-awesome', isset($_POST['add-font-awesome']));
        update_option('rewp_allow-svg', isset($_POST['allow-svg']));
        update_option('rewp_jquery', isset($_POST['jquery']));
 
        $this->redirect();
    }
 
    /**
     * Determines if the nonce variable associated with the options page is set
     * and is valid.
     *
     * @access private
     *
     * @return boolean False if the field isn't set or the nonce value is invalid;
     *                 otherwise, true.
     */
    private function has_valid_nonce()
    {
 
        // If the field isn't even in the $_POST, then it's invalid.
        if (! isset($_POST['re-wp-nonce-check'])) { // Input var okay.
            return false;
        }
 
        $field  = wp_unslash($_POST['re-wp-nonce-check']);
        $action = 're-wp-settings-save';
 
        return wp_verify_nonce($field, $action);
    }
 
    /**
     * Redirect to the page from which we came (which should always be the
     * admin page. If the referred isn't set, then we redirect the user to
     * the login page.
     *
     * @access private
     */
    private function redirect()
    {
 
        // To make the Coding Standards happy, we have to initialize this.
        if (! isset($_POST['_wp_http_referer'])) { // Input var okay.
            $_POST['_wp_http_referer'] = wp_login_url();
        }
 
        // Sanitize the value of the $_POST collection for the Coding Standards.
        $url = sanitize_text_field(
            wp_unslash($_POST['_wp_http_referer']) // Input var okay.
        );
 
        // Finally, redirect back to the admin page.
        wp_safe_redirect(urldecode($url));
        exit;
    }
}
