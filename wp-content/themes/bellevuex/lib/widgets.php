<?php
//======================================================================
// Register sidebars and widgets
//======================================================================

//-----------------------------------------------------
// roots_widgets_init
//-----------------------------------------------------
function roots_widgets_init() {
	// Sidebars
	register_sidebar(array(
		'name'          => esc_html__('Primary', 'bellevue'),
		'id'            => 'sidebar-primary',
		'before_widget' => '<section class="widget %1$s %2$s"><div class="widget-inner">',
		'after_widget'  => '</div></section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	));
	//$i = $themo_footer_columns;
	/* Themovation Theme Options */
	if ( function_exists( 'get_theme_mod' ) ) {
		/* Footer  Columns */

		$themo_footer_show = get_theme_mod( 'themo_footer_widget_switch', 'off' );

		if($themo_footer_show == true){
			$themo_footer_columns = get_theme_mod( 'themo_footer_columns', 2 );

			for ($i = 1; $i <= $themo_footer_columns; $i++) {
				register_sidebar(array(
					'name'          => sprintf(esc_html__('Footer 1 Column %1$s', 'bellevue'),$i),
					'id'            => "sidebar-footer-$i",
					'before_widget' => '<section class="widget %1$s %2$s"><div class="widget-inner">',
					'after_widget'  => '</div></section>',
					'before_title'  => '<h3 class="widget-title">',
					'after_title'   => '</h3>',
				));
			}
		}

        $themo_footer2_show = get_theme_mod( 'themo_footer2_widget_switch', 'off' );

        if($themo_footer2_show == true){
            $themo_footer2_columns = get_theme_mod( 'themo_footer2_columns', 2 );

            for ($i = 1; $i <= $themo_footer2_columns; $i++) {
                register_sidebar(array(
                    'name'          => sprintf(esc_html__('Footer 2 Column %1$s', 'bellevue'),$i),
                    'id'            => "sidebar-footer2-$i",
                    'before_widget' => '<section class="widget %1$s %2$s"><div class="widget-inner">',
                    'after_widget'  => '</div></section>',
                    'before_title'  => '<h3 class="widget-title">',
                    'after_title'   => '</h3>',
                ));
            }
        }
	}

    // Widgets
    register_widget('WP_Widget_Themo_Social_Icons');
	register_widget('WP_Widget_Themo_Payments_Accepted');
	register_widget('WP_Widget_Themo_Contact_Info');
    register_widget('WP_Widget_Themo_Logo');
}
add_action('widgets_init', 'roots_widgets_init');



//-----------------------------------------------------
// Social Media Icon Widget
//-----------------------------------------------------
class WP_Widget_Themo_Social_Icons extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_themo_social_icons', 'description' => esc_html__( "Social Icons", 'bellevue') );
		parent::__construct('themo-social-icons', esc_html__('Social Icons', 'bellevue'), $widget_ops);
		$this->alt_option_name = 'widget_themo_social_icons';

		/*
		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
		*/
	}

	function widget($args, $instance) {
		/*$cache = wp_cache_get('widget_themo_social_icons', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = sanitize_html_class($this->id);

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo esc_html( $cache[ $args['widget_id'] ] );
			return;
		}

		ob_start();*/
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

        $themo_social_align = empty($instance[ 'themo_social_align' ]) ? 'false' : 'true';

        ?>

        <?php // GET SOCIAL ICONS ?>
        <?php
        $themo_social_align_class = false;
        if ('true' == $themo_social_align){
            $themo_social_align_class=' th-social-align-right';
        }
        ?>
		<section class="widget widget-social<?php echo esc_attr($themo_social_align_class); ?>">
    		<div class="widget-inner">
        		<?php if ( $title ) { ?>
                <h3 class="widget-title"><?php echo esc_attr($title); ?></h3>
                <?php } ?>
        			<div class="soc-widget">
        			<?php echo themo_return_social_icons(); ?>
           			</div>
    			</div>
		</section>

		<?php
		/*
        $cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_themo_social_icons', $cache, 'widget');
		*/
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
        // Add this line
        $instance[ 'themo_social_align' ] = $new_instance[ 'themo_social_align' ];
		/*$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_themo_social_icons']) )
			delete_option('widget_themo_social_icons');
		*/
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_themo_social_icons', 'widget');
	}

	function form( $instance ) {

        $defaults = array( 'title' => __( '', 'bellevue' ), 'themo_social_align' => 'off' );
        $instance = wp_parse_args( ( array ) $instance, $defaults );

        //$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title</label>
            <input class="widefat"  id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance[ 'title' ] ); ?>" />
        </p>
        <!-- The checkbox -->
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance[ 'themo_social_align' ], 'on' ); ?> id="<?php echo $this->get_field_id( 'themo_social_align' ); ?>" name="<?php echo $this->get_field_name( 'themo_social_align' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'themo_social_align' ); ?>">Align Right</label>
        </p>

<?php
	}
}


//-----------------------------------------------------
// Payments Accepted Widget
//-----------------------------------------------------
class WP_Widget_Themo_Payments_Accepted extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_themo_payments_accepted', 'description' => esc_html__( "Payments Accepted", 'bellevue') );
		parent::__construct('themo-payments-accepted', esc_html__('Payments Accepted', 'bellevue'), $widget_ops);
		$this->alt_option_name = 'widget_themo_payments_accepted';
		/*

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
		*/
	}

	function widget($args, $instance) {

	    /*
		$cache = wp_cache_get('widget_themo_payments_accepted', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = sanitize_html_class($this->id);

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo esc_html( $cache[ $args['widget_id'] ] );
			return;
		}

		ob_start();
	    */

		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base); ?>

		<?php // GET PAYMENTS ACCEPTED ?>
		<section class="widget widget-th-payments">
			<div class="widget-inner">
				<?php if ( $title ) {?>
					<h3 class="widget-title"><?php echo esc_attr($title); ?></h3>
				<?php } ?>
				<div class="th-payments-widget">
					<?php echo themo_return_payments_accepted(); ?>
				</div>
			</div>
		</section>

		<?php

        /*
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_themo_payments_accepted', $cache, 'widget');
        */
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		/*
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_themo_payments_accepted']) )
			delete_option('widget_themo_payments_accepted');
		*/
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_themo_payments_accepted', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		?>
		<p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'bellevue'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
	<?php
	}
}

//-----------------------------------------------------
// Contact Info Widget
//-----------------------------------------------------
class WP_Widget_Themo_Contact_Info extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_themo_contact_info', 'description' => esc_html__( "Contact Info", 'bellevue') );
		parent::__construct('themo-contact-info', esc_html__('Contact Info', 'bellevue'), $widget_ops);
		$this->alt_option_name = 'widget_themo_contact_info';

		/*
		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
		*/
	}

	function widget($args, $instance) {
	    /*
		$cache = wp_cache_get('widget_themo_contact_info', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = sanitize_html_class($this->id);

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo esc_html( $cache[ $args['widget_id'] ] );
			return;
		}

		ob_start();
	    */
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base); ?>

		<?php // GET PAYMENTS ACCEPTED ?>
		<section class="widget widget-th-contact-info">
			<div class="widget-inner">
				<?php if ( $title ) {?>
					<h3 class="widget-title"><?php echo esc_attr($title); ?></h3>
				<?php } ?>
				<div class="th-contact-info-widget">
					<?php echo themo_return_contact_info(); ?>
				</div>
			</div>
		</section>

		<?php

        /*
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_themo_contact_info', $cache, 'widget');
        */
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		/*
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_themo_contact_info']) )
			delete_option('widget_themo_contact_info');
		*/
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_themo_contact_info', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		?>
		<p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'bellevue'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
	<?php
	}
}


//-----------------------------------------------------
// Logo Widget
//-----------------------------------------------------
class WP_Widget_Themo_Logo extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'widget_themo_logo', 'description' => esc_html__( "Footer Logo", 'bellevue') );
        parent::__construct('themo-logo', esc_html__('Footer Logo', 'bellevue'), $widget_ops);
        $this->alt_option_name = 'widget_themo_logo';

        /*
        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
        */
    }

    function widget($args, $instance) {
        /*
        $cache = wp_cache_get('widget_themo_logo', 'widget');

        if ( !is_array($cache) )
            $cache = array();

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = sanitize_html_class($this->id);

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo esc_html( $cache[ $args['widget_id'] ] );
            return;
        }

        ob_start();
        */
        extract($args);

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base); ?>

        <?php // GET PAYMENTS ACCEPTED ?>
        <section class="widget widget-th-logo">
            <div class="widget-inner">
                <?php if ( $title ) {?>
                    <h3 class="widget-title"><?php echo esc_attr($title); ?></h3>
                <?php } ?>
                <div class="th-logo-widget">
                    <?php echo themo_return_footer_logo(); ?>
                </div>
            </div>
        </section>

        <?php
        /*
        $cache[$args['widget_id']] = ob_get_flush();
        wp_cache_set('widget_themo_logo', $cache, 'widget');
        */
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);

        /*
        $this->flush_widget_cache();

        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['widget_themo_logo']) )
            delete_option('widget_themo_logo');
        */
        return $instance;
    }

    function flush_widget_cache() {
        wp_cache_delete('widget_themo_logo', 'widget');
    }

    function form( $instance ) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        ?>
        <p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'bellevue'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
    <?php
    }
}
