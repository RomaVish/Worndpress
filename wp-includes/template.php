<?php
/**
 * Template loading functions.
 *
 * @package Worndpress
 * @subpackage Template
 */

/**
 * Retrieve path to a template
 *
 * Used to quickly retrieve the path of a template without including the file
 * extension. It will also check the parent theme, if the file exists, with
 * the use of locate_template(). Allows for more generic template location
 * without the use of the other get_*_template() functions.
 *
 * @since 1.5.0
 *
 * @param string $type      Filename without extension.
 * @param array  $templates An optional list of template candidates
 * @return string Full path to template file.
 */
function get_query_template( $type, $templates = array() ) {
	$type = preg_replace( '|[^a-z0-9-]+|', '', $type );

	if ( empty( $templates ) )
		$templates = array("{$type}.php");

	/**
	 * Filter the list of template filenames that are searched for when retrieving a template to use.
	 *
	 * The last element in the array should always be the fallback template for this query type.
	 *
	 * Possible values for `$type` include: 'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date',
	 * 'embed', home', 'frontpage', 'page', 'paged', 'search', 'single', 'singular', and 'attachment'.
	 *
	 * @since 4.7.0
	 *
	 * @param array $templates A list of template candidates, in descending order of priority.
	 */
	$templates = apply_filters( "{$type}_template_hierarchy", $templates );

	$template = locate_template( $templates );

	/**
	 * Filters the path of the queried template by type.
	 *
	 * The dynamic portion of the hook name, `$type`, refers to the filename -- minus the file
	 * extension and any non-alphanumeric characters delimiting words -- of the file to load.
	 * This hook also applies to various types of files loaded as part of the Template Hierarchy.
	 *
	 * Possible values for `$type` include: 'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date',
	 * 'embed', home', 'frontpage', 'page', 'paged', 'search', 'single', 'singular', and 'attachment'.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template Path to the template. See locate_template().
	 */
	return apply_filters( "{$type}_template", $template );
}

/**
 * Retrieve path of index template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'index_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'index_template'} hook.
 *
 * @since 3.0.0
 *
 * @see get_query_template()
 *
 * @return string Full path to index template file.
 */
function get_index_template() {
	return get_query_template('index');
}

/**
 * Retrieve path of 404 template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see '404_template_hierarchy'} hook.
 * The template path is filterable via the {@see '404_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to 404 template file.
 */
function get_404_template() {
	return get_query_template('404');
}

/**
 * Retrieve path of archive template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'archive_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'archive_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to archive template file.
 */
function get_archive_template() {
	$post_types = array_filter( (array) get_query_var( 'post_type' ) );

	$templates = array();

	if ( count( $post_types ) == 1 ) {
		$post_type = reset( $post_types );
		$templates[] = "archive-{$post_type}.php";
	}
	$templates[] = 'archive.php';

	return get_query_template( 'archive', $templates );
}

/**
 * Retrieve path of post type archive template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'archive_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'archive_template'} hook.
 *
 * @since 3.7.0
 *
 * @see get_archive_template()
 *
 * @return string Full path to archive template file.
 */
function get_post_type_archive_template() {
	$post_type = get_query_var( 'post_type' );
	if ( is_array( $post_type ) )
		$post_type = reset( $post_type );

	$obj = get_post_type_object( $post_type );
	if ( ! $obj->has_archive )
		return '';

	return get_archive_template();
}

/**
 * Retrieve path of author template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'author_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'author_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to author template file.
 */
function get_author_template() {
	$author = get_queried_object();

	$templates = array();

	if ( $author instanceof WP_User ) {
		$templates[] = "author-{$author->user_nicename}.php";
		$templates[] = "author-{$author->ID}.php";
	}
	$templates[] = 'author.php';

	return get_query_template( 'author', $templates );
}

/**
 * Retrieve path of category template in current or parent template.
 *
 * Works by first retrieving the current slug, for example 'category-default.php',
 * and then trying category ID, for example 'category-1.php', and will finally fall
 * back to category.php template, if those files don't exist.
 *
 * The template hierarchy is filterable via the {@see 'category_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'category_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to category template file.
 */
function get_category_template() {
	$category = get_queried_object();

	$templates = array();

	if ( ! empty( $category->slug ) ) {

		$slug_decoded = urldecode( $category->slug );
		if ( $slug_decoded !== $category->slug ) {
			$templates[] = "category-{$slug_decoded}.php";
		}

		$templates[] = "category-{$category->slug}.php";
		$templates[] = "category-{$category->term_id}.php";
	}
	$templates[] = 'category.php';

	return get_query_template( 'category', $templates );
}

/**
 * Retrieve path of tag template in current or parent template.
 *
 * Works by first retrieving the current tag name, for example 'tag-wordpress.php',
 * and then trying tag ID, for example 'tag-1.php', and will finally fall back to
 * tag.php template, if those files don't exist.
 *
 * The template hierarchy is filterable via the {@see 'tag_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'tag_template'} hook.
 *
 * @since 2.3.0
 *
 * @see get_query_template()
 *
 * @return string Full path to tag template file.
 */
function get_tag_template() {
	$tag = get_queried_object();

	$templates = array();

	if ( ! empty( $tag->slug ) ) {

		$slug_decoded = urldecode( $tag->slug );
		if ( $slug_decoded !== $tag->slug ) {
			$templates[] = "tag-{$slug_decoded}.php";
		}

		$templates[] = "tag-{$tag->slug}.php";
		$templates[] = "tag-{$tag->term_id}.php";
	}
	$templates[] = 'tag.php';

	return get_query_template( 'tag', $templates );
}

/**
 * Retrieve path of taxonomy template in current or parent template.
 *
 * Retrieves the taxonomy and term, if term is available. The template is
 * prepended with 'taxonomy-' and followed by both the taxonomy string and
 * the taxonomy string followed by a dash and then followed by the term.
 *
 * The taxonomy and term template is checked and used first, if it exists.
 * Second, just the taxonomy template is checked, and then finally, taxonomy.php
 * template is used. If none of the files exist, then it will fall back on to
 * index.php.
 *
 * The template hierarchy is filterable via the {@see 'taxonomy_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'taxonomy_template'} hook.
 *
 * @since 2.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to taxonomy template file.
 */
function get_taxonomy_template() {
	$term = get_queried_object();

	$templates = array();

	if ( ! empty( $term->slug ) ) {
		$taxonomy = $term->taxonomy;

		$slug_decoded = urldecode( $term->slug );
		if ( $slug_decoded !== $term->slug ) {
			$templates[] = "taxonomy-$taxonomy-{$slug_decoded}.php";
		}

		$templates[] = "taxonomy-$taxonomy-{$term->slug}.php";
		$templates[] = "taxonomy-$taxonomy.php";
	}
	$templates[] = 'taxonomy.php';

	return get_query_template( 'taxonomy', $templates );
}

/**
 * Retrieve path of date template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'date_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'date_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to date template file.
 */
function get_date_template() {
	return get_query_template('date');
}

/**
 * Retrieve path of home template in current or parent template.
 *
 * This is the template used for the page containing the blog posts.
 * Attempts to locate 'home.php' first before falling back to 'index.php'.
 *
 * The template hierarchy is filterable via the {@see 'home_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'home_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to home template file.
 */
function get_home_template() {
	$templates = array( 'home.php', 'index.php' );

	return get_query_template( 'home', $templates );
}

/**
 * Retrieve path of front page template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'frontpage_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'frontpage_template'} hook.
 *
 * @since 3.0.0
 *
 * @see get_query_template()
 *
 * @return string Full path to front page template file.
 */
function get_front_page_template() {
	$templates = array('front-page.php');

	return get_query_template( 'front_page', $templates );
}

/**
 * Retrieve path of page template in current or parent template.
 *
 * Will first look for the specifically assigned page template.
 * Then will search for 'page-{slug}.php', followed by 'page-{id}.php',
 * and finally 'page.php'.
 *
 * The template hierarchy is filterable via the {@see 'page_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'page_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to page template file.
 */
function get_page_template() {
	$id = get_queried_object_id();
	$template = get_page_template_slug();
	$pagename = get_query_var('pagename');

	if ( ! $pagename && $id ) {
		// If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
		$post = get_queried_object();
		if ( $post )
			$pagename = $post->post_name;
	}

	$templates = array();
	if ( $template && 0 === validate_file( $template ) )
		$templates[] = $template;
	if ( $pagename ) {
		$pagename_decoded = urldecode( $pagename );
		if ( $pagename_decoded !== $pagename ) {
			$templates[] = "page-{$pagename_decoded}.php";
		}
		$templates[] = "page-$pagename.php";
	}
	if ( $id )
		$templates[] = "page-$id.php";
	$templates[] = 'page.php';

	return get_query_template( 'page', $templates );
}

/**
 * Retrieve path of paged template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'paged_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'paged_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to paged template file.
 */
function get_paged_template() {
	return get_query_template('paged');
}

/**
 * Retrieve path of search template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'search_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'search_template'} hook.
 *
 * @since 1.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to search template file.
 */
function get_search_template() {
	return get_query_template('search');
}

/**
 * Retrieve path of single template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'single_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'single_template'} hook.
 *
 * @since 1.5.0
 * @since 4.4.0 `single-{post_type}-{post_name}.php` was added to the top of the template hierarchy.
 *
 * @see get_query_template()
 *
 * @return string Full path to single template file.
 */
function get_single_template() {
	$object = get_queried_object();

	$templates = array();

	if ( ! empty( $object->post_type ) ) {

		$name_decoded = urldecode( $object->post_name );
		if ( $name_decoded !== $object->post_name ) {
			$templates[] = "single-{$object->post_type}-{$name_decoded}.php";
		}

		$templates[] = "single-{$object->post_type}-{$object->post_name}.php";
		$templates[] = "single-{$object->post_type}.php";
	}

	$templates[] = "single.php";

	return get_query_template( 'single', $templates );
}

/**
 * Retrieves an embed template path in the current or parent template.
 *
 * By default the Worndpress-template is returned.
 *
 * The template hierarchy is filterable via the {@see 'embed_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'embed_template'} hook.
 *
 * @since 4.5.0
 *
 * @see get_query_template()
 *
 * @return string Full path to embed template file.
 */
function get_embed_template() {
	$object = get_queried_object();

	$templates = array();

	if ( ! empty( $object->post_type ) ) {
		$post_format = get_post_format( $object );
		if ( $post_format ) {
			$templates[] = "embed-{$object->post_type}-{$post_format}.php";
		}
		$templates[] = "embed-{$object->post_type}.php";
	}

	$templates[] = "embed.php";

	return get_query_template( 'embed', $templates );
}

/**
 * Retrieves the path of the singular template in current or parent template.
 *
 * The template hierarchy is filterable via the {@see 'singular_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'singular_template'} hook.
 *
 * @since 4.3.0
 *
 * @see get_query_template()
 *
 * @return string Full path to singular template file
 */
function get_singular_template() {
	return get_query_template( 'singular' );
}

/**
 * Retrieve path of attachment template in current or parent template.
 *
 * The attachment path first checks if the first part of the mime type exists.
 * The second check is for the second part of the mime type. The last check is
 * for both types separated by an underscore. If neither are found then the file
 * 'attachment.php' is checked and returned.
 *
 * Some examples for the 'text/plain' mime type are 'text.php', 'plain.php', and
 * finally 'text-plain.php'.
 *
 * The template hierarchy is filterable via the {@see 'attachment_template_hierarchy'} hook.
 * The template path is filterable via the {@see 'attachment_template'} hook.
 *
 * @since 2.0.0
 *
 * @see get_query_template()
 *
 * @global array $posts
 *
 * @return string Full path to attachment template file.
 */
function get_attachment_template() {
	$attachment = get_queried_object();

	$templates = array();

	if ( $attachment ) {
		if ( false !== strpos( $attachment->post_mime_type, '/' ) ) {
			list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
		} else {
			list( $type, $subtype ) = array( $attachment->post_mime_type, '' );
		}

		if ( ! empty( $subtype ) ) {
			$templates[] = "{$type}-{$subtype}.php";
			$templates[] = "{$subtype}.php";
		}
		$templates[] = "{$type}.php";
	}
	$templates[] = 'attachment.php';

	return get_query_template( 'attachment', $templates );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH and wp-includes/theme-compat
 * so that themes which inherit from a parent theme can just overload one file.
 *
 * @since 2.7.0
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool         $load           If true the template file will be loaded if it is found.
 * @param bool         $require_once   Whether to require_once or require. Default true. Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function locate_template($template_names, $load = false, $require_once = true ) {
	$located = '';
	foreach ( (array) $template_names as $template_name ) {
		if ( !$template_name )
			continue;
		if ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
			$located = STYLESHEETPATH . '/' . $template_name;
			break;
		} elseif ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
			$located = TEMPLATEPATH . '/' . $template_name;
			break;
		} elseif ( file_exists( ABSPATH . WPINC . '/theme-compat/' . $template_name ) ) {
			$located = ABSPATH . WPINC . '/theme-compat/' . $template_name;
			break;
		}
	}

	if ( $load && '' != $located )
		load_template( $located, $require_once );

	return $located;
}

/**
 * Require the template file with Worndpress environment.
 *
 * The globals are set up for the template file to ensure that the Worndpress
 * environment is available from within the function. The query variables are
 * also available.
 *
 * @since 1.5.0
 *
 * @global array      $posts
 * @global WP_Post    $post
 * @global bool       $wp_did_header
 * @global WP_Query   $wp_query
 * @global WP_Rewrite $wp_rewrite
 * @global wpdb       $wpdb
 * @global string     $wp_version
 * @global WP         $wp
 * @global int        $id
 * @global WP_Comment $comment
 * @global int        $user_ID
 *
 * @param string $_template_file Path to template file.
 * @param bool   $require_once   Whether to require_once or require. Default true.
 */
function load_template( $_template_file, $require_once = true ) {
	global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

	if ( is_array( $wp_query->query_vars ) ) {
		extract( $wp_query->query_vars, EXTR_SKIP );
	}

	if ( isset( $s ) ) {
		$s = esc_attr( $s );
	}

	if ( $require_once ) {
		require_once( $_template_file );
	} else {
		require( $_template_file );
	}
}

