<?php

namespace E20R\Sequences\Modules\Widgets;

/**
 * License:
 *
 * Copyright 2014-2016 Eighty/20 Results by Wicked Strong Chicks, LLC (thomas@eighty20results.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 **/

use E20R\Sequences as Sequences;
use E20R\Sequences\Sequence\Controller;
use E20R\Sequences\Data\Model;

use E20R\Utilities\Utilities;

class Post_Widget extends \WP_Widget {
	
	public function __construct() {
	    
	    $utils = Utilities::get_instance();
	    
	    $utils->log("Loading Sequence Widget for Current post");
	    
		parent::__construct(
			'e20r_sequences__currentpost_widget',
			__( 'Sequences: Current', Controller::plugin_slug ),
			array(
				'description' =>
					__( 'Display a summary of the most recently available sequence post (or page) for the currently logged-in user.', Controller::plugin_slug ),
			)
		);
	}
	
	public function widget( $args, $instance ) {
		
		// global $load_pmpro_sequence_script;
		
		// $load_pmpro_sequence_script = true;
		$utils = Utilities::get_instance();
		
		extract( $args );
		
		$title       = apply_filters( 'widget_title', $instance['title'] );
		$seq_prefix  = apply_filters( 'e20r-sequence-widget-prefix', ( isset( $instance['prefix'] ) ? $instance['prefix'] : null ) );
		$sequence_id = apply_filters( 'e20r-sequence-widget-seqid', ( isset( $instance['sequence_id'] ) ? $instance['sequence_id'] : null ) );
		
		$default_title = apply_filters( 'e20r-sequence-widget-default-post-title', ( isset( $instance['default_post_title'] ) ? $instance['default_post_title'] : null ) );
		$before_title  = apply_filters( 'e20r-sequence-widget-before-widget-title', ( isset( $instance['before_title'] ) ? $instance['before_title'] : null ) );
		$after_title   = apply_filters( 'e20r-sequence-widget-after-widget-title', ( isset( $instance['after_title'] ) ? $instance['after_title'] : null ) );
		
		$wordcount     = $instance['wordcount'];
		$show_title    = ( $instance['show_title'] == 1 ) ? true : false;
		$before_widget = apply_filters( 'e20r-sequence-before-widget', ( isset( $instance['before_widget'] ) ? $instance['before_widget'] : null ) );
		$after_widget  = apply_filters( 'e20r-sequence-after-widget', ( isset( $instance['after_widget'] ) ? $instance['after_widget'] : null ) );
		
		echo $before_widget;
		
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		$utils->log("Loading Sequence post data");
		$this->get_sequence_post_data( $sequence_id, $seq_prefix, $wordcount, $show_title, $default_title );
		
		echo $after_widget;
	}
	
	public function form( $instance ) {
		
		// Set up the c_time (or default) settings
		if ( $instance ) {
			
			$show_title        = ( empty( $instance['show_title'] ) ? 0 : esc_attr( $instance['show_title'] ) );
			$default_title     = esc_attr( $instance['default_post_title'] );
			$title             = esc_attr( $instance['title'] );
			$sequence_id       = esc_attr( $instance['sequence_id'] );
			$excerpt_wordcount = esc_attr( $instance['wordcount'] );
			$seq_prefix        = esc_attr( $instance['prefix'] );
			
		} else {
			// dbg_log("Widget config: No config found");
			$default_title     = __( 'Your most recently available content', Controller::plugin_slug );
			$title             = null;
			$show_title        = 0;
			$sequence_id       = 0;
			$seq_prefix        = null;
			$excerpt_wordcount = 40;
		}
		
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget title', Controller::plugin_slug ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo $title; ?>"/>
        </p>

        <p>
            <input class="widefat" id="<?php echo $this->get_field_id( 'show_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'show_title' ) ?>" type="checkbox"
                   value="1" <?php checked( $show_title, 1 ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show Post/Page title', Controller::plugin_slug ); ?></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'default_post_title' ); ?>"><?php _e( 'Default post/page title (if "hidden")', Controller::plugin_slug ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'default_post_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'default_post_title' ) ?>" type="text"
                   value="<?php echo $default_title; ?>"/>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'prefix' ); ?>"><?php _e( 'Post title prefix', Controller::plugin_slug ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'prefix' ); ?>"
                   name="<?php echo $this->get_field_name( 'prefix' ) ?>" type="text"
                   value="<?php echo $seq_prefix; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wordcount' ); ?>"><?php _e( 'Max size of post/page excerpt (# of words)', Controller::plugin_slug ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'wordcount' ); ?>"
                   name="<?php echo $this->get_field_name( 'wordcount' ) ?>" type="text"
                   value="<?php echo $excerpt_wordcount; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'sequence_id' ); ?>"><?php _e( 'Sequence to use', Controller::plugin_slug ); ?></label>
            <select id="<?php echo $this->get_field_id( 'sequence_id' ); ?>"
                    name="<?php echo $this->get_field_name( 'sequence_id' ) ?>">
				<?php echo $this->sequence_options( $sequence_id ); ?>
            </select>
        </p>
		<?php
	}
	
	public function update( $new_instance, $old_instance ) {
	 
		$instance = $old_instance;
		
		$instance['show_title']         = strip_tags( $new_instance['show_title'] );
		$instance['default_post_title'] = strip_tags( $new_instance['default_post_title'] );
		
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['sequence_id'] = strip_tags( $new_instance['sequence_id'] );
		$instance['wordcount']   = strip_tags( $new_instance['wordcount'] );
		$instance['prefix']      = strip_tags( $new_instance['prefix'] );
		
		return $instance;
	}
	
	private function sequence_options( $sequence_id ) {
		
		global $id;
		
		$sequences = new \WP_Query( array(
			"post_type" => Model::cpt_type,
		) );
		
		ob_start();
		if ( $sequences->found_posts == 0 ) {
			?>
            <option value="0" selected="selected"><?php _e( 'No sequences defined', Controller::plugin_slug ); ?></option><?php
		} else {
			?>
            <option value="0" <?php echo( $sequence_id != 0 ? '' : 'selected="selected"' ); ?>></option><?php
			
			while ( $sequences->have_posts() ) : $sequences->the_post(); ?>
                <option value="<?php esc_attr_e( $id ); ?>" <?php echo selected( $id, $sequence_id ); ?> ><?php echo the_title_attribute(); ?></option><?php
			endwhile;
		}
		
		wp_reset_postdata();
		
		$html = ob_get_clean();
		
		return $html;
	}
	
	private function get_sequence_post_data( $sequence_id, $seq_prefix = null, $excerpt_length = 0, $show_title = true, $default_title ) {
		
		global $post;
		global $current_user;
		
		$utils = Utilities::get_instance();
		
		if ( $sequence_id != 0 ) {
			$utils->log("Loading Sequence (ID: {$sequence_id})");
			$sequence = Controller::get_instance();
			$sequence->init( $sequence_id );
		} else {
			?>

            <li class="widget widget-text">
                <h3 id="e20r-seq-post-notfound">Error</h3>
                <div class="text-widget">
					<?php _e( "No sequence specified for this widget!", Controller::plugin_slug ); ?>
                </div>
            </li>
			
			<?php
			
			return false;
		}
		
		if ( is_user_logged_in() ) {
			
			$seq_post = $sequence->find_closest_post( $current_user->ID );
			
			if ( empty( $seq_post ) ) { ?>
                <span id="e20r-seq-post-notfound">
                <h3 id="<?php echo apply_filters( 'e20r-seq-recentpost-widget-nopostfound', 'e20r-seq-widget-recentpost-nopostfound-title' ); ?>"
                    class="widget-title"><?php _e( "Configuration Error", Controller::plugin_slug ); ?></h3>
					<div id="e20r-seq-post-body"
                         class="text-widget <?php echo apply_filters( 'e20r-seq-widget-recentpost-nopostfound-body', '' ); ?>">
						<?php echo( $sequence_id != 0 ? get_the_title( $sequence_id ) . __( ': No post(s) found!', Controller::plugin_slug ) : __( 'No sequence specified', Controller::plugin_slug ) ); ?>
					</div>
                </span><?php
			} else if ( $sequence->has_post_access( $current_user->ID, $seq_post->id, false, $sequence_id ) ) {
    
				add_image_size( 'e20r_seq_recentpost_widget_size', 85, 45, false );
				
				$utils->log( "Widget - Posts: " . print_r( $seq_post, true ) );
				
				$image = ( has_post_thumbnail( $seq_post->id ) ? get_the_post_thumbnail( $seq_post->id, 'e20r_seq_recentpost_widget_size' ) : '<div class="noThumb"></div>' );
				
				if ( $show_title ) { ?>
                <h3 id="<?php echo apply_filters( 'e20r-seq-recent-post-widget-title-id', 'e20r-seq-widget-recentpost-title' ); ?>"
                    class="widget-title">
                    <span class="widget-inner"><?php echo ( $seq_prefix != '' ? $seq_prefix . ' ' : ' ' ) . esc_html__( $seq_post->title ); ?></span>
                    </h3><?php
				} else { ?>

                    <h3
                    id="<?php echo apply_filters( 'e20r-seq-recent-post-widget-title-id', 'e20r-seq-widget-recentpost-title' ); ?>"
                    class="widget-title"><?php esc_html_e( $default_title ); ?></h3><?php
					
				} ?>
                <div id="e20r-seq-post-body" class="text-widget">
                    <!-- <p class="e20r-seq-when">Available on <?php $this->print_available_date( $sequence, $seq_post->id ); ?></p> -->
                    <div id="e20r-seq-post-body-text"><?php
						echo $image;
						echo $this->limit_excerpt_words( get_the_excerpt( $seq_post->id ), $excerpt_length ); ?>
                    </div>
                    <div id="e20r-seq-post-link" <?php echo apply_filters( 'e20r-seq-widget-postlink-class', '' ); ?>>
                        <a href="<?php esc_url_raw( $seq_post->permalink ); ?>"
                           title="<?php esc_html_e( $seq_post->title ); ?>"><?php _e( 'Click to read', Controller::plugin_slug ); ?></a>
                    </div>
                </div> <?php
			} else { ?>
                <span id="e20r-seq-post-notfound">
                <h3 class="widget-title"><?php _e( "Membership Level Error", Controller::plugin_slug ); ?></h3>
					<div id="e20r-seq-post-body" class="text-widget">
						<?php _e( "Sorry, your current membership level does not give you access to this content.", Controller::plugin_slug ); ?>
					</div>
                </span><?php
			}
		}
	}
	
	private function limit_excerpt_words( $string, $word_limit ) {
		
		$words = explode( " ", $string, ( $word_limit + 1 ) );
		
		if ( count( $words ) > $word_limit ) {
			array_pop( $words );
			array_push( $words, '[...]' );
		}
		
		return implode( " ", $words );
	}
	
	/**
	 * @param Controller $seq
	 * @param integer             $postId
	 */
	private function print_available_date( Controller $seq, $postId ) {
		
		$seq_post  = $seq->get_post_details( $postId );
		$max_delay = 0;
		
		foreach ( $seq_post as $k => $post ) {
			
			if ( isset( $post->delay) && isset($max_delay->delay) && ( $post->delay < $max_delay->delay ) ) {
				
				unset( $seq_post[ $k ] );
			} else {
				$max_delay = clone $post;
			}
		}
		
		$post = $seq_post[0];
		
		if ( ( $seq->options->delayType == 'byDays' ) && ( $seq->options->showDelayAs == E20R_SEQ_AS_DAYNO ) ) {
			printf( __( 'day %d of membership', Controller::plugin_slug ), $seq->display_proper_delay( $post->delay ) );
		} else {
			echo $seq->display_proper_delay( $post->delay );
		}
	}
}