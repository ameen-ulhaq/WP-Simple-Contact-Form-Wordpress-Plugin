<?php 
/** 
* Plugin Name: Wp Simple Contact Form
* Description: Simple contact Form
* Author: Ameen
* Author URI: http://ameenulhaq.com
• Version: 1.0.0
* Text Domain:  wp-simple-contazct-form
*
**/

if( !defined('ABSPATH') ) {
    echo "I can see you!";
    exit;
}
class WpSimpleContactForm {

    public function __construct()
    {
        // crate custom post type
        add_action('init', array($this, 'create_custom_post_type'));
        // add scripts
        add_action('wp_enqueue_scripts', array($this, 'load_assets'));
        // shortcode
        add_shortcode('contact-form', array($this, 'loadShortcode'));
        // load javascript
        add_action('wp_footer', array($this, 'load_scripts'));
        // Register Rest API
        add_action('rest_api_init', array($this, 'register_rest_api'));


    }

    public function create_custom_post_type()
    {
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                   'name' => 'Contact Form',
                   'singular_name' => 'Contact Form Entry'
            ),
            'menu_icon' => 'dashicons-media-text',
      );
      register_post_type('simple_contact_form', $args);
    }

    public function load_assets()
    {
        wp_enqueue_style(
            'wp-simple-contact-form',
            plugin_dir_url( __FILE__ ) . 'css/wp-simple-contact-form.css',
            array(),
            1,
            'all'            
        );

        wp_enqueue_script(
            'wp-simple-contact-form',
            plugin_dir_url( __FILE__ ) . 'js/wp-simple-contact-form.js',
            array('jquery'),
            1,
            true   
        );
    }

    public function loadShortcode()
    { ?>
        <div class="simple-contact-form">
            <h1>Send us en Email</h1>
            <p> Please fille the form <p>
            <form id="simple-contact-form__form">
                <input type="text" name="name" id="name" placeholder="Nmae">
                <input type="email" name="email" id="email" placeholder="Email">
                <input type="tel" name="phone" id="phone" placeholder="Phone">
                <textarea name="message" id="message" cols="30" rows="10" placeholder="Type your message"></textarea>
    
                <button type="submit" class="btn btn-success">Send Message</button>
            </form>
        </div>
    <?php }

    public function load_scripts()
    { ?>
        <script>
            (function($){

                var nonce = '<?php echo wp_create_nonce('wp_rest') ?>'

                jQuery('#simple-contact-form__form').submit( function( event ){
                    event.preventDefault()
                    
                    var form = $(this).serialize()
                    console.log(form);

                    $.ajax({
                        method: 'post',
                        url: '<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email'); ?>',
                        headers: { 'X-WP-Nonce': nonce },
                        date: form
                    })
                })

            })(jQuery)
        </script>
    <?php }

    public function register_rest_api()
    {
        register_rest_route('simple-contact-form/v1', 'send-email', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_contact_form')
        ));
    }

    public function handle_contact_form($data)
    {
        $headers = $data->get_headers();
        $params = $data->get_params();
        $nonce = $headers['x_wp_nonce'][0];

        if(!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_REST_Response('Message not sent!!', 422);
        }

        $post_id = wp_insert_post([
            'post_type' => 'simple_contact_form',
            'post_title' => 'Contact Enquiry',
            'post_status' => 'publish'
        ]);

        if($post_id) {
            return new WP_REST_Response('Thank you for your email!', 200);
        }
    }


}

new WpSimpleContactForm;