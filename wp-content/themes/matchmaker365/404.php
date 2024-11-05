<?php

/**
 * The template for displaying 404 pages (Not Found)
 */
get_header();
?>
<div id="primary" class="container">
        <div id="content" class="site-content" role="main" style=
        "min-height:60vh">
 
            <header class="page-header text-center mto-100">
                <h1 class="page-title"><?php _e( '404', 'mm365' ); ?></h1>
            </header>
 
            <div class="page-wrapper text-center">
                <div class="page-content">
                    <h2><?php _e( 'This page is either moved or does not exist', 'mm365' ); ?></h2>
                </div><!-- .page-content -->
            </div><!-- .page-wrapper -->

            <?php
                // $cron_jobs = get_option( 'cron' );
                // var_dump($cron_jobs);
            ?>
 
        </div><!-- #content -->
    </div><!-- #primary -->
<?php
get_footer();