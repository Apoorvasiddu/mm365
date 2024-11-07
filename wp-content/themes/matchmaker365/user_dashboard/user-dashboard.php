<?php
/**
 * Template Name: User Dashboard
 *
 */


$user = wp_get_current_user();
//$mm365_helper = new mm365_helpers($user);

//Restrict the page access users other than 'business_users'
do_action('mm365_helper_check_loginandrole',array('business_user'));

//$dashboardController = new mm365_user_dashboard_controller();
$company_type = get_post_meta( $_COOKIE['active_company_id'], 'mm365_service_type', true );

//Redirect if the user hasn't registered a company yet
do_action('mm365_helper_check_companyregistration', 'register-your-company');

$active_company_id = $_COOKIE['active_company_id'];

get_header();
  
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>

  

  <div class="dashboard-content-panel">

  <?php
 if(metadata_exists( 'post', $active_company_id, 'mm365_naics_codes') == false){
  ?>
  <div class="high-alert blink">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
</svg>

    Attention! <strong>NAICS code</strong> information is missing from your company details. Please <a href="<?php echo site_url('edit-active-company') ?>?cid=<?php echo esc_attr($active_company_id); ?>" class="font-bold">click here</a> to update.</div>
<?php } ?>


  <h2 class="heading-large">Dashboard</h2>

    <section class="company_preview">
      

        <div class="row pbo-30">
           <div class="col-7">
           <p>Welcome <span class="heading-medium"><?php echo get_post_meta( $_COOKIE['active_company_id'], 'mm365_contact_person', true ) ?></span>, </p>

           <?php if($company_type == 'seller'){ ?>

           <h4>Your company has matched for <span class="heading-medium">
            <?php 
            echo apply_filters('mm365_supplierdash_match_apperance_count',$active_company_id);
            
            ?>
            </span> match requests this month! </h4>
            <?php } ?>

           </div>

           <div class="col-3">
             <h6>Subscription Status</h6>
             <?php 

             $subscription_stat = apply_filters('mm365_supplierdash_subscription_status_card',$active_company_id);

             if($subscription_stat == NULL ): ?>
               Not Required
             <?php else: 
              echo "<strong>Status: </strong>".$subscription_stat['status']."<br/>";
              if($subscription_stat['status'] == 'Active'){
                echo "<strong>End Date: </strong>".date('m/d/Y',strtotime($subscription_stat['end_date']))."<br/>";
                echo "<strong>Level: </strong>".$subscription_stat['level']."<br/>";
              }
              ?>
             <?php endif; ?>
           </div>

           <?php if($company_type != 'buyer'): ?> 
           <div class="col-2">
             <h6>Certification Status</h6>
             <?php if(get_post_meta( $_COOKIE['active_company_id'], 'mm365_certification_status', true ) == 'verified'): ?>
               <span class="cmp_badge supplier">Active</span>
             <?php else: ?>
              <span class="cmp_badge red">Not Certified</span>  
             <?php endif; ?>
           </div>
           <?php endif; ?>

        </div>


        <div class="row pbo-10">
            <div class="col-12"><h6>This month summary</h6><hr></div>
        </div>

        <div class="row user-dash pbo-30">
              <div class="col-12">

                <?php 
                  if($company_type == 'seller'){
                    echo apply_filters('mm365_supplierdash_welcome_cards',$active_company_id);
                    $look_for_company_type = 'Supplier';
                  }else {
                    echo apply_filters('mm365_buyerdash_welcome_cards',$active_company_id);
                    $look_for_company_type = 'Buyer';
                  }
                ?>
                
              </div>
        </div>

    
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
                            'value'   => array('Both',$look_for_company_type),
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
                            'value'   => array('Both',$look_for_company_type),
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
    </section>

  </div>
</div>

<?php
get_footer();