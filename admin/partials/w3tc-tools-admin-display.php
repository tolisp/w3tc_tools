<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://intelpad.eu
 * @since      1.0.0
 *
 * @package    W3tc_Tools
 * @subpackage W3tc_Tools/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->


<?php


define ('max_archive_pages', 4);    // 0 == all archive page #s

const custom_post_types = array('news', 'short_news', 'special_report', 'linksdossier', 'interview', 'opinion', 'infographic', 'video', 'blog');

add_action('admin_menu', 'cache_tools_menu');




function cache_tools_menu()
{
    add_menu_page('Cache tools', 'Cache tools', 'edit_posts', 'cache-tools-page.php', 'cache_tools_page', 'dashicons-warning');
}
function cache_tools_page()
{ 

    //var_dump(custom_post_types);

    if (isset($_POST['submit']) && ($_POST['submit'] == 'purge_url')) {
        if (current_user_can('edit_posts')) {
            $url = $_POST['url'];
            // echo  $_SERVER['HTTP_HOST'];
            $domain = parse_url($url, PHP_URL_HOST);
            // var_dump($domain);
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                if ($domain == $_SERVER['HTTP_HOST']) {
                    w3tc_flush_url($url);
                    echo '<div id="message" class="updated"><p>URL <span style="color:green">' . $url . '</span> purged</p></div>';
                } else {
                    echo '<div id="message" class="updated"><p>URL <span style="color:red">' . $url . '</span> does not belong in the allowed domains ' . $_SERVER['SERVER_NAME'] . '</p></div>';
                }
            } else {
                echo '<div id="message" class="updated"><p>URL <span style="color:red">' . $url . '</span> is not a valid URL (http://<url_here>)</p></div>';
            }
        } else {
            echo '<div id="message" class="updated"><p>You are not allowed to do this!</p></div>';
        }
    }

    if (isset($_POST['submit']) && ($_POST['submit'] == 'purge_weekly')) {
        if (current_user_can('administrator')) {
            /* purge all home archive pages */
            $all = 0;
            $posts_per_page = get_option('posts_per_page');
           // $custom_post_types = get_post_types(array('public' => true, '_builtin' => false));
            foreach ($custom_post_types as $value => $name) {
                $all += wp_count_posts($value)->publish;
            }
            $posts_pages_number = @ceil($all / $posts_per_page);
            ea_purge_home_urls($posts_pages_number);

            
            foreach (custom_post_types as $value => $name) {
                ea_purge_cpt_cache($value, 0);
            }

            /* purge all section archive pages */
            $terms = get_terms(array(
                'taxonomy' => 'sections',
                'hide_empty' => false,
            ));
            $term_urls = \W3TC\Util_PageUrls::get_post_terms_urls($terms, 0);
            foreach ($term_urls as $term_url) {
                w3tc_flush_url($term_url);
                echo '<div id="message" class="notice notice-success"><p>' . $term_url . ' purged</p></div>';
            }

            /* purge all tag archive pages */
            $terms = get_terms(array(
                'taxonomy' => 'post_tag',
                'hide_empty' => false,
            ));
            $tag_urls = \W3TC\Util_PageUrls::get_post_terms_urls($terms, 0);
            foreach ($tag_urls as $tag_url) {
                w3tc_flush_url($tag_url);
                echo '<div id="message" class="notice notice-success"><p>' . $tag_url . ' purged</p></div>';
            }
        } else {
            echo '<div id="message" class="updated"><p>You are not allowed to do this!</p></div>';
        }
    }

    if (isset($_POST['submit']) && ($_POST['submit'] == 'purge_cpt')) {
        if (current_user_can('administrator')) {
           
            
            if (!in_array($_POST['cpt'], custom_post_types)) {
                echo '<div id="message" class="error"><p><span style="color:red">Please select one of the custom post types listed </p></div>';
                return;
            }
            $post_type = $_POST['cpt'];
            if (empty($_POST['pages'])) {
                $pages = max_archive_pages;
            } else {
                $pages = $_POST['pages'];
            }

            ea_purge_cpt_cache($post_type, $pages);
        } else {
            echo '<div id="message" class="updated"><p>You are not allowed to do this!<  /p></div>';
        }
    }

    if (isset($_POST['submit']) && ($_POST['submit'] == 'purge_main_section')) {
        if (current_user_can('administrator')) {
            var_dump($_POST);
            $main_section = $_POST['main_section'];
            ea_purge_main_section_cache($main_section);

        } else {
            echo '<div id="message" class="updated"><p>You are not allowed to do this!</p></div>';
        }
    }


    if (isset($_POST['submit']) && ($_POST['submit'] == 'purge_home')) {
        if (current_user_can('edit_posts')) {
            if (empty($_POST['home'])) {
                echo '<div id="message" class="error"><p><span style="color:red">Please select a number of home page archives to be purged</p></div>';
                return;
            }
            $number = $_POST['home'];
            ea_purge_home_urls($number);
        } else {
            echo '<div id="message" class="updated"><p>You are not allowed to do this!</p></div>';
        }

    }


    if (isset($_POST['submit']) && ($_POST['submit'] == 'purge_ids')) {
        if (current_user_can('administrator')) {


            if (!isset($_POST['from_id']) || !isset($_POST['to_id'])) {
                echo '<div id="message" class="error"><p><span style="color:red">Offset and number are empty</p></div>';
            }else {
                $offset = $_POST['from_id'];
                $number = $_POST['to_id'];
                ea_cache_get_last_posts($offset, $number);
            }
        } else {
            echo '<div id="message" class="updated"><p>You are not allowed to do this!</p></div>';
        }

    }

    ?>

    <div class="wrap">

        <h2>Purge URL</h2>

        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <td width="150px">
                        <label for="num_elements">
                            Purge cache URL
                        </label></td>
                    <td>
                        <input type="text" name="url" size="50"/> <input type="submit" name="submit" value="purge_url"
                                                                         class="button button-primary button-small">
                    </td>
                </tr>
            </table>

        </form>
    </div>
    <hr/>
    <div class="wrap">
        <h2>Purge articles by main section( Administrator access required)</h2>
        <span style="color:#f00;">ATTENTION: ALL ARTICLES WILL BE PURGED!!</span>
        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <td width="200px">
                        <label for="num_elements">
                            Purge posts by main section
                        </label>
                    </td>
                    <td>
                        <select name="main_section" placeholder="Which type?">
                            <option name="" value=""> --</option>
                            <?php

                            $terms = get_terms( array(
                                'taxonomy' => 'main_section',
                                'hide_empty' => false,
                            ) );
                            foreach ($terms as $value => $term) {
                                echo '<option name="' . $term->slug . '" value="' .  $term->slug  . '">' . $term->name . '</option>';
                            }
                            ?>
                        </select> <input
                                type="submit" name="submit" value="purge_main_section"
                                class="button button-primary button-small">
                    </td>

                </tr>
            </table>

        </form>


    </div>   <hr/>
    <div class="wrap">
        <h2>Purge custom post type archives( Administrator access required)</h2>

        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <td width="200px">
                        <label for="num_elements">
                            Purge Custom type archives
                        </label>
                    </td>
                    <td>
                        <select name="cpt" placeholder="Which type?">
                            <option name="" value=""> --</option>
                            <?php

                           // $custom_post_types = get_post_types(array('public' => true, '_builtin' => false));
                            foreach (custom_post_types as $name) {
                                echo '<option name="' . $name . '" value="' . $name . '">' . $name . '</option>';
                            }
                            ?>
                        </select> <input type="text" name="pages" size="10" placeholder="How many?"/> <input
                                type="submit" name="submit" value="purge_cpt"
                                class="button button-primary button-small">
                    </td>

                </tr>
            </table>

        </form>

        <?php
        echo 'Default number of pages : ' . max_archive_pages; ?>
    </div>
    <hr/>
    <div class="wrap">
        <h2>Purge home archives</h2>

        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <td width="200px">
                        <label for="num_elements">
                            Purge Home page archives
                        </label>
                    </td>
                    <td>
                        <input type="text" name="home" size="10" placeholder="How many?"/>
                        <input type="submit"
                                                                                                  name="submit"
                                                                                                  value="purge_home"
                                                                                                  class="button button-primary button-small">
                    </td>
                </tr>
            </table>

        </form>
    </div>
    <hr/>
    <div class="wrap">
        <h2>Purge articles ( Administrator access required)</h2>
        <?php
        ?>
        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <td width="200px">
                        <label for="num_elements">
                            Purge latest articles
                        </label>
                    </td>
                    <td>

                        <input type="text" name="from_id" size="5" placeholder="Offset"/> to <input type="text"
                                                                                                     name="to_id"
                                                                                                     size="5"
                                                                                                     placeholder="Number"/>
                        <input type="submit" name="submit" value="purge_ids" class="button button-primary button-small">
                    </td>
                </tr>
            </table>

        </form>
    </div>
    <hr/>
    <div class="wrap">
        <h2>Auto purge</h2>

        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <td width="200px">
                        <label for="num_elements">
                            Auto purging content type archives
                        </label>
                    </td>
                    <td>
                        <input type="checkbox" readonly="readonly" disabled="disabled" name="auto" checked="checked"/>
                    </td>
                </tr>
            </table>

        </form>
    </div>
    <hr/>
    <div class="wrap" style="background-color: red; color: #fff;">
        <h2>Weekly cache purge ( Administrator access required)</h2>

        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <td width="200px">
                        <label for="num_elements">
                            Weekly purge
                        </label>
                    </td>
                    <td>
                        <input type="submit" name="submit" value="purge_weekly" class="button button-secondary delete">
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><h5>This button will purge the below </h5>
                        <ul>
                            <li class="mp6-text-notification">/page/*</li>
                            <li>{cpt}/page/*</li>
                            <li>{sections}/page/*</li>
                            <li>{topics}/page/*</li>
                        </ul>
                    </td>
                </tr>
            </table>

        </form>
    </div>

    <?php
}

function checkRootDomain($url, $allowed_domain)
{
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }

    $domain = implode('.', array_slice(explode('.', parse_url($url, PHP_URL_HOST)), -2));
    if ($domain == $allowed_domain) {
        return true;
    } else {
        return false;
    }
}


function  ea_purge_cpt_cache($cpt, $pages){

    if(current_user_can('administrator')) {

    	if (defined('W3TC')) {
		$posts_per_page = get_option('posts_per_page');
		$posts_number = \W3TC\Util_PageUrls::get_archive_posts_count(0, 0, 0, $cpt);
		$posts_pages_number = @ceil($posts_number / $posts_per_page);

		if ($pages > 0 && $posts_pages_number > $pages) {
			$posts_pages_number = $pages;
		}

		if ($posts_pages_number == 0) {
			w3tc_flush_url(\W3TC\Util_Environment::home_domain_root_url() . "/" . $cpt);
			echo '<div id="message" class="notice notice-success"><p>'. \W3TC\Util_Environment::home_domain_root_url() . "/" . $cpt.' purged</p></div>';
		} else {
			for ($pagenum = 1; $pagenum <= $posts_pages_number; $pagenum++) {
				$link = \W3TC\Util_PageUrls::get_pagenum_link($cpt, $pagenum);

				w3tc_flush_url($link);
				echo '<div id="message" class="notice notice-success"><p>' . $link . ' purged</p></div>';
			}
		}
	}
    }else{
        echo '<div id="message" class="updated"><p>You are not allowed to do this!</p></div>';
    }

}

function  ea_purge_main_section_cache($main_section){

    if(current_user_can('administrator')) {

        if (defined('W3TC')) {

        $args = array(
            'post_type' => get_post_types(array('public' => true, '_builtin' => false)),
            'tax_query' => array(
                array(
                    'taxonomy' => 'main_section',
                    'field'    => 'slug',
                    'terms'    => $main_section,
                ),
            ),
        );
        $query = new WP_Query( $args );
              foreach ($query->posts as $post_item) {

                   $link = get_permalink($post_item->ID).'<br/>';
                   w3tc_flush_url($link);
                   echo '<div id="message" class="notice notice-success"><p>' . $link . ' purged</p></div>';
                }
        }
   }else{
       echo '<div id="message" class="updated"><p>You are not allowed to do this!</p></div>';
    }

}

function ea_purge_home_urls($number){

    if(current_user_can('edit_posts')) {

	if (defined('W3TC')) {

		//  die('w3tc defined');

			for ($pagenum = 1; $pagenum <= $number; $pagenum++) {
				$link = \W3TC\Util_PageUrls::get_pagenum_link('', $pagenum);

				w3tc_flush_url($link);
				echo '<div id="message" class="notice notice-success"><p>'.$link .' purged</p></div>';
			}

	}
}else{
    echo '<div id="message" class="updated"><p>You are not allowed to do this!</p></div>';
}

}


function ea_cache_get_last_posts($offset=0, $number=10)
{

    $array = array(
        'post_type' => ea_post_types_registered(),
        'post_status' => 'publish',
        'orderby' => 'ID',
        'order' => 'DESC',
        'posts_per_page' => $number,
        'offset' => $offset
    );
    $articles = get_posts($array);
    if (defined('W3TC')){
        foreach ($articles as $article) {

            w3tc_flush_post($article->ID);
            echo  $article->post_type.': <span style="color:green">' . $article->post_title . '</span> purged (<i>' . $article->ID.'</i>)<br/>';
        }
}

}

// POST SAVE AUTOMATED CACHE FUNCTIONS

function post_status( $new_status, $old_status, $post )
{

   // $custom_post_types = get_post_types(array('public'   => true,'_builtin' => false));

	if ( array_key_exists( $post->post_type, custom_post_types ) && ( $new_status === "publish" || $old_status === "publish" ) )
	{
		publish_purge($post);
	}
}


add_action(  'transition_post_status',  'post_status', 10, 3 );




function publish_purge($post){

    if ( defined( 'W3TC' ) )
		{
            //$custom_post_types = get_post_types(array('public'   => true,'_builtin' => false));
			//$archive = empty( trim( $custom_post_types[$post->post_type] ) ) ? $post->post_type:trim( $custom_post_types[$post->post_type] );
            
          //  var_dump($post);
/*
			$post->post_status = "";
			$link = preg_replace( '~__trashed/$~', '/', get_permalink( $post ) );
			w3tc_flush_url( $link );
*/
            // PURGE POST

            w3tc_flush_post( $post->ID );
            w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/feed");


         
            // PURGE SPECIAL REPORT CHILDREN 

            $children = get_post_meta($post->ID, '_ea_special_report_children', true);
          //  echo "CHILDREN: "; var_dump($children);
            if (is_array($children) && !empty($children)) {
                               
                    foreach ($children as $child) {
                        $srpost= get_post_permalink(get_post($child));
                        w3tc_flush_url($srpost);
                        //echo "SRPOST: ". $srpost ."<br/>";                      
                    }

            }

            //PURGE Taxonomy archives

            $tax = wp_get_post_terms($post->ID, 'sections');
            foreach ($tax as $term){
                $term_page = get_term_link($term , 'sections');
                //w3tc_flush_url($term_page);
                w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/sections/".$term->slug);
              //  echo "TERM: ". $term_page."<br/>";
                w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/feed?sections=".$term->slug);

            }

            //PURGE Tag archives

            $tags = wp_get_post_tags($post->ID);
            foreach ($tags as $tag){
                $tag_page = \W3TC\Util_Environment::home_domain_root_url() . "/topics/".$tag->slug;
              //  echo "TAG: ".$tag_page."<br/>";
               w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/feed?topics=".$tag->slug);
               w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/topics/".$tag->slug);

            }


            //PURGE POST TRANSLATIONS CACHE
            $current_blog_id = get_current_blog_id();
            $translations = ea_msls_get_translations($current_blog_id, $post->ID);
            foreach ($translations as $site => $translation) {

                    w3tc_flush_url(get_blog_permalink($site,$translation));
                  // wp_set_post_tags($post->ID, $site.'__'.$translation, true);
            }


			// PURGE  FIRST 10 PAGES OF MAIN ARCHIVE
			for ($pagenum = 1; $pagenum < max_archive_pages; $pagenum++) {
				$link = \W3TC\Util_PageUrls::get_pagenum_link('', $pagenum);
                w3tc_flush_url($link);
               // echo "HOME: ". $link."<br/>";
            }


            // PURGE CUSTOM POST TYPE ARCHIVES -- DEFAULT 10 PAGES
			$posts_per_page = get_option( 'posts_per_page' );
			$posts_number = \W3TC\Util_PageUrls::get_archive_posts_count( 0, 0, 0, $post->post_type );
			$posts_pages_number = @ceil( $posts_number / $posts_per_page );

			if ( max_archive_pages > 0 && $posts_pages_number > max_archive_pages ) {
				$posts_pages_number = max_archive_pages;
			}

			if ( $posts_pages_number == 0 ) {
				w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/" . $post->post_type );
			} else {
				for ( $pagenum = 1; $pagenum <= $posts_pages_number; $pagenum++ ) {
					$link = \W3TC\Util_PageUrls::get_pagenum_link( $post->post_type, $pagenum );
                    w3tc_flush_url( $link );
                   // echo "CPT: ". $link ."<br/>";
				}
			}
        }
    }


add_action( 'post_submitbox_misc_actions', 'ea_purge_custom' );

function ea_purge_custom($post){
$post_type = $post->post_type;
$post_type_object = get_post_type_object($post_type);
$can_publish = current_user_can($post_type_object->cap->publish_posts);

if( !$can_publish || $post->post_status != 'publish' )
        return false;
?>
 <?php 
 
$nonce = wp_create_nonce( 'purge_nonce' ); // create nonce
$url = admin_url('admin-ajax.php?action=publish_purge_ajax&post_id='.$post->ID.'&nonce='.$nonce);
?>


<div class="misc-pub-section">
    <span class="dashicons dashicons-shield"></span>
<span id="post-upload-display"></span> <a id="ea_purge_cache" data-nonce="<?php echo $nonce; ?>" data-post_id="<?php echo $post->ID; ?>" href="#">CLEAN UP</a>
<div id="purge_result"></div>
</div><!-- .misc-pub-section -->	
<?php
}

add_action("wp_ajax_publish_purge_ajax", "publish_purge_ajax");

function publish_purge_ajax(){

    if ( !wp_verify_nonce( $_REQUEST['nonce'], "purge_nonce")) {
        exit("No naughty business please");
     }  

    if ( defined( 'W3TC' ) )
    {

        $responce = [];
        $post = get_post( $_REQUEST["post_id"]);
        //$custom_post_types = get_post_types(array('public'   => true,'_builtin' => false));
        //$archive = empty( trim( $custom_post_types[$post->post_type] ) ) ? $post->post_type:trim( $custom_post_types[$post->post_type] );
        
      //  var_dump($post);
/*
        $post->post_status = "";
        $link = preg_replace( '~__trashed/$~', '/', get_permalink( $post ) );
        w3tc_flush_url( $link );
*/
        // PURGE POST

        w3tc_flush_post( $post->ID );
        w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/feed");


     
        // PURGE SPECIAL REPORT CHILDREN 

        $children = get_post_meta($post->ID, '_ea_special_report_children', true);
      //  echo "CHILDREN: "; var_dump($children);
        if (is_array($children) && !empty($children)) {
                           
                foreach ($children as $child) {
                    $srpost= get_post_permalink(get_post($child));
                    w3tc_flush_url($srpost);
                    $responce[$child] = $srpost;                      
                }

        }

        //PURGE Taxonomy archives

        $tax = wp_get_post_terms($post->ID, 'sections');
        foreach ($tax as $term){
            $term_page = get_term_link($term , 'sections');
            //w3tc_flush_url($term_page);
            w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/sections/".$term->slug);
            $responce[$term->slug] = $term_page;
            w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/feed?sections=".$term->slug);

        }

        //PURGE Tag archives

        $tags = wp_get_post_tags($post->ID);
        foreach ($tags as $tag){
            $tag_page = \W3TC\Util_Environment::home_domain_root_url() . "/topics/".$tag->slug;
            $responce[$tag->slug] = $tag_page;
           w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/feed?topics=".$tag->slug);
           w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/topics/".$tag->slug);

        }


        //PURGE POST TRANSLATIONS CACHE
        $current_blog_id = get_current_blog_id();
        $translations = ea_msls_get_translations($current_blog_id, $post->ID);
        foreach ($translations as $site => $translation) {

                w3tc_flush_url(get_blog_permalink($site,$translation));
              // wp_set_post_tags($post->ID, $site.'__'.$translation, true);
        }


        // PURGE  FIRST 10 PAGES OF MAIN ARCHIVE
        for ($pagenum = 1; $pagenum < max_archive_pages; $pagenum++) {
            $link = \W3TC\Util_PageUrls::get_pagenum_link('', $pagenum);
            w3tc_flush_url($link);
            $responce['main_'.$pagenum] = $link;
        }


        // PURGE CUSTOM POST TYPE ARCHIVES -- DEFAULT 10 PAGES
        $posts_per_page = get_option( 'posts_per_page' );
        $posts_number = \W3TC\Util_PageUrls::get_archive_posts_count( 0, 0, 0, $post->post_type );
        $posts_pages_number = @ceil( $posts_number / $posts_per_page );

        if ( max_archive_pages > 0 && $posts_pages_number > max_archive_pages ) {
            $posts_pages_number = max_archive_pages;
        }

        if ( $posts_pages_number == 0 ) {
            w3tc_flush_url( \W3TC\Util_Environment::home_domain_root_url() . "/" . $post->post_type );
        } else {
            for ( $pagenum = 1; $pagenum <= $posts_pages_number; $pagenum++ ) {
                $link = \W3TC\Util_PageUrls::get_pagenum_link( $post->post_type, $pagenum );
                w3tc_flush_url( $link );
               $responce['cpt_'.$pagenum] = $link;
            }
        }

        $result = json_encode($responce);
        echo $result;
    }


    die();
}


add_action( 'admin_init', 'purge_script_enqueuer' );

function purge_script_enqueuer() {
   wp_register_script( "purge_cache_script", get_template_directory_uri().'/hooks/cache/purge_ajax.js', array('jquery') );
   wp_localize_script( 'purge_cache_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
   wp_enqueue_script( 'purge_cache_script' );

}