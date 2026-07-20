<?php global $post;  ?>
<?php include( locate_template( 'templates/page-layout.php' ) ); ?>

    <div class="inner-container">
<?php include( locate_template( 'templates/page-header.php' ) ); // Page Header Template ?>

<?php echo wp_kses_post($outer_container_open) . wp_kses_post($outer_row_open); // Outer Tag Open ?>

<?php /* OPEN MAIN CLASS */
echo wp_kses_post($main_class_open); // support for sidebar ?>

<?php
$format = get_post_format();
if ( false === $format ) {
    $format = 'standard';
}
?>

<?php
/**
 * @hooked \MPHB\Views\SingleRoomTypeView::renderPageWrapperStart - 10
 */
do_action( 'mphb_render_single_room_type_wrapper_start' );
?>
<section class="content-editor">
<?php
while ( have_posts() ) : the_post();

    if ( post_password_required() ) {
        echo get_the_password_form();
        return;
    }
    ?>

        <?php do_action( 'mphb_render_single_room_type_before_content' ); ?>

        <?php
        /**
         * @hooked \MPHB\Views\SingleRoomTypeView::renderTitle				- 10
         * @hooked \MPHB\Views\SingleRoomTypeView::renderFeaturedImage		- 20
         * @hooked \MPHB\Views\SingleRoomTypeView::renderDescription		- 30
         * @hooked \MPHB\Views\SingleRoomTypeView::renderPrice				- 40
         * @hooked \MPHB\Views\SingleRoomTypeView::renderAttributes			- 50
         * @hooked \MPHB\Views\SingleRoomTypeView::renderCalendar			- 60
         * @hooked \MPHB\Views\SingleRoomTypeView::renderReservationForm	- 70
         */
        //do_action( 'mphb_render_single_room_type_content' );
        ?>

    <?php the_content(); ?>

    <?php //do_action( 'mphb_render_single_room_type_after_content' ); ?>

<?php
endwhile;
?>
</section>
<?php
/**
 * @hooked \MPHB\Views\SingleRoomTypeView::renderPageWrapperEnd - 10
 */
do_action( 'mphb_render_single_room_type_wrapper_end' );
?>


<?php // check if sidebar and remove container, else leave it. ?>
    <!-- Comment form for pages -->
<?php echo wp_kses_post($inner_container_open); ?>
    <div class="row">
        <div class="col-md-12">
            <?php comments_template(); ?>
        </div>
    </div>
<?php echo wp_kses_post($inner_container_close); ?>
    <!-- End Comment form for pages -->

<?php
/* CLOSE MAIN CLASS */
echo wp_kses_post($main_class_close); ?>

<?php
/* SIDEBAR */
include themo_sidebar_path(); ?>

<?php
echo wp_kses_post($outer_container_close) . wp_kses_post($outer_row_close); // Outer Tag Close ?>