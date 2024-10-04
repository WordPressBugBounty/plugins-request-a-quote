<?php
/**
 * Content Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       5.3
 */
if (!defined('ABSPATH')) exit;

function request_a_quote_content(){
        global $post;
        ob_start();
        if(is_single()){
                $file = emd_get_single_template('request_a_quote',$post->post_type);
                if($file == 'emd-no-access.php'){
                        emd_get_template_part('request-a-quote', 'no-access');
                }
                else {
			while ( have_posts() ) : the_post();
                        	emd_get_template_part('request-a-quote', 'single', str_replace("_","-",$post->post_type));
			endwhile;
                }
        }elseif (is_tax()) {
                $file = emd_get_taxonomy_template('request_a_quote');
                if($file == 'emd-no-access.php'){
                        emd_get_template_part('request-a-quote', 'no-access');
                }
                else {
                        $queried_object = get_queried_object();
			if ( have_posts() ) : 
				while ( have_posts() ) : the_post();
                        		emd_get_template_part('request-a-quote', 'taxonomy', str_replace("_","-",$queried_object->taxonomy . '-' . $post->post_type));
				endwhile;
			endif;
                }
        } elseif (is_post_type_archive()){
                $file = emd_get_archive_template('request_a_quote');
                if($file == 'emd-no-access.php'){
                        emd_get_template_part('request-a-quote', 'no-access');
                }
                else {
			if ( have_posts() ) : 
				while ( have_posts() ) : the_post();
                        		emd_get_template_part('request-a-quote', 'archive', str_replace("_","-",$post->post_type));
				endwhile;
			endif;
                }
        }
        $layout = ob_get_clean();
        echo wp_kses_post($layout);
}
