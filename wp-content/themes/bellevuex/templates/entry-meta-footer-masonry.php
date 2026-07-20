<?php
$num_comments = get_comments_number(); // get_comments_number returns only a numeric value
$write_comments = "";

if ( comments_open() ) {
    if ( $num_comments == 0 ) {
        $comments = esc_html__('No Comments', 'bellevue');
    } elseif ( $num_comments > 1 ) {
        $comments = $num_comments . esc_html__(' Comments', 'bellevue');
    } else {
        $comments = esc_html__('1', 'bellevue') . "<i class='". esc_html__('themo-comment-icon fa fa-comment-o', 'bellevue') ."'></i>";
    }
    $write_comments = '<a href="' . esc_url(get_comments_link()) .'">'. $comments.'</a>';
}
?>
<div class="date-meta">
    <span class="thmv-author">
    <i class="xs-icon fa fa-calendar-o"></i>
    <!--div class="post-meta"-->
        <span class="themo-mas-date-meta show-author"><?php the_author_posts_link(); ?></span>
    </span>
    
        <time class="themo-mas-date-meta published thmv-date" datetime="<?php echo get_the_time('c'); ?>"><?php echo get_the_date(); ?></time>
        <?php if ( $num_comments !== '0' ){?>
            <span class="themo-mas-date-meta show-comments"><?php echo wp_kses_post( $write_comments ); ?></span>
        <?php } ?>
    <!--/div-->
    <span class="is-sticky"><?php echo esc_html__('Featured Post', 'bellevue'); ?></span>
</div>