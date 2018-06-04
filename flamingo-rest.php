<?php
/*
Plugin Name: Flamingo REST Endpoint
Description: Adds Flamingo Endpoints and Meta Details
Author: Brad Williams
Text Domain: brag-rest
Domain Path: /languages/
Version: 0.0.1
*/


/**
 * Add custom fields to Flamingo
 */
function brag_add_meta_field($args) {
   
    $args['fields']['referral'] = '';
    $args['fields']['notes'] = '';
    $args['fields']['type'] = '';
    $args['fields']['lead_status'] = '';

    return $args;
}
add_filter( 'flamingo_add_inbound', 'brag_add_meta_field', 10, 1 );


/**
 * Add REST API support to an already registered post type.
 */
add_action( 'init', 'brag_rest_post_type_rest_support', 25 );
function brag_rest_post_type_rest_support() {
  global $wp_post_types;
 
  //be sure to set this to the name of your post type!
  $post_type_name = 'flamingo_inbound';
  if( isset( $wp_post_types[ $post_type_name ] ) ) {
    // Optionally customize the rest_base or controller class
    $wp_post_types[$post_type_name]->rest_base = $post_type_name;
    $wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
    //$wp_post_types[$post_type_name]->show_in_rest = true;
    $wp_post_types[$post_type_name]->show_in_rest = current_user_can('edit_posts');
  }
}

/**
 * Add the field "details" to REST API responses for Contact 7 Flamingo Data
 */
add_action( 'rest_api_init', 'brag_rest_register_flamingo' );
function brag_rest_register_flamingo() {
    register_rest_field( 'flamingo_inbound',
        'details',
        array(
            'get_callback'    => 'brag_rest_get_flamingo',
            'update_callback' => 'brag_rest_update_flamingo',
            'schema'          => null,
        )
    );
}
/**
 * Handler for getting custom field data.
 *
 * @since 0.1.0
 *
 * @param array $object The object from the response
 * @param string $field_name Name of field
 * @param WP_REST_Request $request Current request
 *
 * @return mixed
 */
function brag_rest_get_flamingo( $object, $field_name, $request ) {

	return get_post_meta( $object[ 'id' ] );
}

/**
 * Handler for updating custom field data.
 *
 * @since 0.1.0
 *
 * @param mixed $value The value of the field
 * @param object $object The object from the response
 * @param string $field_name Name of field
 *
 * @return bool|int
 */
function brag_rest_update_flamingo( $value, $object, $field_name ) {
	//    $message = "Callback response received:  \n\n" . 
	//    print_r($_REQUEST, true) . "\n\n Value" 
	//    .print_r($value, true) . "\n\n Object" 
	//    . print_r($object, true) . "\n\n Field Name" 
	//    . print_r($field_name, true);
	// wp_mail('email@email.com', 'Subject', $message);

	foreach ($value as $field => $data) {
		update_post_meta( $object->ID, $field, $data  );
	}
}


/**
 * Add the field "meta" to REST API responses with Contact 7 Flamingo meta data
 */
add_action( 'rest_api_init', 'brag_rest_meta_register_flamingo' );
function brag_rest_meta_register_flamingo() {
    register_rest_field( 'flamingo_inbound',
        'meta',
        array(
            'get_callback'    => 'brag_rest_meta_get_flamingo',
            'schema'          => null,
        )
    );
}

/**
 * Gets and returns all the meta data to the rest endpoint
 */
function brag_rest_meta_get_flamingo( $object, $field_name, $request ) {

	$meta = get_post_meta( $object[ 'id' ], '_meta' );

	return $meta;
}