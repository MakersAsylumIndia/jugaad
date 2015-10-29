<?php
/**
* Enqueuing Stylesheets
**/
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');
function theme_enqueue_styles()
{
  $parent_style = 'parent-style';

  wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
  wp_enqueue_style(
    'normalize',
    get_stylesheet_directory_uri() . '/css/normalize.css',
    array($parent_style)
  );
  wp_enqueue_style(
    'child-style',
    get_stylesheet_directory_uri() . '/style.css',
    array($parent_style)
  );
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

/**
* Loading Google Fonts
**/
function load_fonts() {
  wp_register_style('pt-sans', 'http://fonts.googleapis.com/css?family=PT+Sans:400,400italic,700,700italic');
  wp_enqueue_style('pt-sans');
}
add_action('wp_enqueue_scripts', 'load_fonts');

/**
* Child Theme Setup
**/
function child_theme_setup()
{
  remove_filter('canard_get_featured_posts', 'canard_get_featured_posts');
  add_filter( 'canard_get_featured_posts', function( $posts ){
    // Modify this to your needs:
    $posts = get_posts( array(
      'post_type'       => array( 'post', 'jugaad_tutorials' ),
      'posts_per_page'  => 5,
      'tag' => 'featured'
    ) );
    return $posts;
  }, PHP_INT_MAX );
}
add_action('after_setup_theme', 'child_theme_setup');

/**
* Custom lenght of the excerpt.
**/
function canard_child_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'canard_child_excerpt_length', 1000 );

/**
* Page Slug Body Class
**/
function add_slug_body_class($classes) {
  global $post;
  if (isset($post)) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  return $classes;
}
add_filter('body_class', 'add_slug_body_class');

/**
* Highlight active custom post page in Navigation
**/
// Mark (highlight) custom post type parent as active item in Wordpress Navigation
function add_current_nav_class($classes, $item) {
  // Getting the current post details
  global $post;
  // Get post ID, if nothing found set to NULL
  $id = ( isset( $post->ID ) ? get_the_ID() : NULL );
  // Checking if post ID exist...
  if (isset( $id )){
    // Getting the post type of the current post
    $current_post_type = get_post_type_object(get_post_type($post->ID));
    $current_post_type_slug = $current_post_type->rewrite['slug'];
    // Getting the URL of the menu item
    $menu_slug = strtolower(trim($item->url));
    // If the menu item URL contains the current post types slug add the current-menu-item class
    if (strpos($menu_slug,$current_post_type_slug) !== false) {
       $classes[] = 'current-menu-item';
    }
  }
  // Return the corrected set of classes to be added to the menu item
  return $classes;
}
add_action('nav_menu_css_class', 'add_current_nav_class', 10, 2 );

/**
* Creating Tutorials and EVents Post Type
**/
function create_post_type() {
  register_post_type( 'jugaad_tutorials',
    array(
      'labels' => array(
        'name' => _x( 'Tutorials', 'post type general name' ),
        'singular_name' => _x( 'Tutorial' , 'post type singular name' ),
        'add_new' => _x('Add New', 'tutorial'),
        'add_new_item' => __('Add New Tutorial'),
        'edit_item' => __('Edit Tutorial'),
        'new_item' => __('New Tutorial'),
        'all_items' => __('All Tutorials'),
        'view_item' => __('View Tutorial'),
        'search_items' => __('Search Tutorials'),
        'not_found'          => __( 'No tutorials found' ),
        'not_found_in_trash' => __( 'No tutorials found in the Trash' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Tutorials'
      ),
      'description' => 'Holds all the Tutorials for the DO section',
      'public' => true,
      'menu_position' => 4,
      'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'post-formats', 'comments', 'author'),
      'taxonomies' => array('post_tag', 'category'),
      'has_archive' => true,
      'rewrite' => array('slug' => 'do')
    )
  );
  register_post_type( 'jugaad_events',
    array(
      'labels' => array(
        'name' => _x( 'Events', 'post type general name' ),
        'singular_name' => _x( 'Event' , 'post type singular name' ),
        'add_new' => _x('Add New', 'event'),
        'add_new_item' => __('Add New Event'),
        'edit_item' => __('Edit Event'),
        'new_item' => __('New Event'),
        'all_items' => __('All Events'),
        'view_item' => __('View Event'),
        'search_items' => __('Search Events'),
        'not_found'          => __( 'No events found' ),
        'not_found_in_trash' => __( 'No events found in the Trash' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Events'
      ),
      'description' => 'Holds all the Events for the GO section',
      'public' => true,
      'menu_position' => 5,
      'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'post-formats', 'comments'),
      'taxonomies' => array('post_tag', 'category'),
      'has_archive' => true,
      'rewrite' => array('slug' => 'go')
    )
  );
}
add_action( 'init', 'create_post_type' );

/**
* Adding the Open Graph
**/
function add_opengraph_doctype( $output ) {
    return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
  }
add_filter('language_attributes', 'add_opengraph_doctype');

function insert_fb_in_head() {
  global $post;
  if ( !is_singular()) //if it is not a post or a page
    return;
        echo '<meta property="fb:admins" content="1157500422"/>';
        echo '<meta property="og:title" content="' . get_the_title() . '"/>';
        echo '<meta property="og:type" content="article"/>';
        echo '<meta property="og:url" content="' . get_permalink() . '"/>';
        echo '<meta property="og:site_name" content="Jugaad Magazine"/>';
        echo '<meta property="article:author" content="' . get_the_author() . '"/>';
        echo '<meta property="article:publisher" content="https://www.facebook.com/jugaadmagazine" />';
  if(!has_post_thumbnail( $post->ID )) { //the post does not have featured image, use a default image
    $default_image="http://www.jugaadmagazine.com/wp-content/uploads/2015/08/symbol-facebook1.jpg"; //replace this with a default image on your server or an image in your media library
    echo '<meta property="og:image" content="' . $default_image . '"/>';
  }
  else{
    $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
    echo '<meta property="og:image" content="' . esc_attr( $thumbnail_src[0] ) . '"/>';
  }
  echo "";
}
add_action( 'wp_head', 'insert_fb_in_head', 5 );

/**
 * Prints HTML with meta information for the categories.
 */
function canard_entry_categories() {
	if ( 'post' == get_post_type() || 'jugaad_tutorials' == get_post_type() || 'jugaad_events' == get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( __( ', ', 'canard' ) );
		if ( $categories_list && canard_categorized_blog() ) {
			printf( '<div class="entry-meta"><span class="cat-links">%1$s</span></div>', $categories_list );
		}
	}
}
