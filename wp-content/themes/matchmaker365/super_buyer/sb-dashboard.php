<?php
/**
 * Template Name: SB - Dashboard
 *
 */

 
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['super_buyer']);


$user_info = get_userdata($user->ID);
get_header();
  
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>

  

  <div class="dashboard-content-panel">
  <h2 class="heading-large">Dashboard</h2>
    <section class="company_preview">

        <div class="container-fluid">
          <div class="row pbo-10">
            <div class="col-7">
            <p>Welcome <span class="heading-medium"><?php echo $user_info->display_name ?></span>, </p>
            </div>
          </div>
        </div>

        <div class="container-fluid">
          <div class="row pbo-10">
              <div class="col-12"><h6>Your team has created</h6><hr></div>
          </div>
        </div>  

        <div class="container-fluid">
          <div class="row user-dash pbo-30">
                <div class="col-12">
                  <div class="stat-highlight-box">
                          <div class="count">
                            <?php 
                              $mr_count =  apply_filters('mm365_superbuyer_get_matchrequests_count',1); 
                              echo esc_html(str_pad($mr_count, 2, '0', STR_PAD_LEFT));      
                            ?>
                          </div>
                          <div class="info">Match requests created</div>
                  </div>

                  <div class="stat-highlight-box">
                          <div class="count">
                            <?php 
                              $mr_count =  apply_filters('mm365_superbuyer_get_matchrequests_count', 'mm365_matchrequest_status','completed'); 
                              echo esc_html(str_pad($mr_count, 2, '0', STR_PAD_LEFT));      
                            ?>
                          </div>
                          <div class="info">Match requests completed</div>
                  </div>                 

                  <div class="stat-highlight-box">
                          <div class="count">
                          <?php 
                            $meeting_count =  apply_filters('mm365_superbuyer_get_meetings_created_count',1); 
                            echo esc_html(str_pad($meeting_count, 2, '0', STR_PAD_LEFT));              
                          ?></div>
                          <div class="info">Meeting<?php echo ($meeting_count > 1) ? 's':'' ?> Created</div>
                  </div>
                 
                  
                </div>
          </div>
        </div>

        <div class="container-fluid">
            <div class="row pbo-30">
                <div class="col-6">
                    <h6>Tips & Tricks</h6>
         <!-- Tips and tricks content block loop here -->
                    <hr>
                    <?php
                  $args = array(  
                      'post_type' => 'mm365_updatesandtips',
                      'posts_per_page' => 1, 
                      'orderby' => 'rand', 
                      'meta_query' => array(
                        array(
                            'key'     => '_mm365_content_type',
                            'value'   => 'Tip',
                            'compare' => '=',
                        ),
                        array(
                          'key'     => '_mm365_visibility',
                          'value'   => '1',
                          'compare' => '=',
                        ),
                        array(
                            'key'     => '_mm365_visible_to',
                            'value'   => array('Both','Super-Buyer'),
                            'compare' => 'IN',
                        )
              
                     ),
                  );
                  $councils_list = array();
                  $loop = new WP_Query( $args );  
                  while ( $loop->have_posts() ) : $loop->the_post(); 
                ?>
                <h4><?php echo get_the_title(); ?></h4>
                <?php echo get_the_content(); ?>
                <?php
                  endwhile;
                  wp_reset_postdata();
                ?>
                </div>
                <div class="col-6">
                    <h6>Updates</h6>
                    <hr>
                    <?php
                  $args = array(  
                      'post_type' => 'mm365_updatesandtips',
                      'posts_per_page' => 3, 
                      'orderby' => 'rand', 
                      'meta_query' => array(
                        array(
                            'key'     => '_mm365_content_type',
                            'value'   => 'Update',
                            'compare' => '=',
                        ),
                        array(
                          'key'     => '_mm365_visibility',
                          'value'   => '1',
                          'compare' => '=',
                        ),
                        array(
                            'key'     => '_mm365_visible_to',
                            'value'   => array('Both','Super-Buyer'),
                            'compare' => 'IN',
                        )
              
                     ),
                  );
                  $councils_list = array();
                  $loop = new WP_Query( $args );  
                  while ( $loop->have_posts() ) : $loop->the_post(); 
                ?>
                <h4><?php echo get_the_title(); ?></h4>
                <?php echo get_the_content(); ?>
                <hr/>
                <?php
                  endwhile;
                  wp_reset_postdata();
                ?>
                </div>
            </div>
        </div>

    </section>

  </div>
</div>

<?php
get_footer();