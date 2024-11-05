<?php
/**
 * Template Name: SB - Buyer Team
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
    <h2 class="heading-large">My Team</h2>
    

       <?php
       $team = get_user_meta($user->ID, '_mm365_associated_buyer');
       if(!empty($team)){
        ?>
        <section class="buyer-team">
        <?php
       foreach (get_user_meta($user->ID, '_mm365_associated_buyer') as $cmp_id) {
           ?>
          
          <div class="buyer-team-card">
                <h3>
                <?php
                    echo get_the_title($cmp_id); 
                ?>
                </h3>

                <div class="contact">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                  </svg>
                  <?php
                      echo esc_html( get_post_meta($cmp_id, 'mm365_contact_person', true) );
                    ?>
                </div>

                <div class="contact">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    <?php
                      echo esc_html( get_post_meta($cmp_id, 'mm365_company_phone', true) );
                    ?>
                </div>

                <div class="contact">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z" />
                  </svg>
                  <?php
                      echo esc_html( get_post_meta($cmp_id, 'mm365_company_email', true) );
                    ?>
                </div>
                <hr class="hr" />
                <div class="d-flex justify-content-between">
                <a href="<?php echo site_url('view-company?cid='. $cmp_id)?>">More Details 
                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                   <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                 </svg>
                </a>

                <?php
                $company_owner_user = get_post_field('post_author',$cmp_id);
                $target_user = get_userdata($company_owner_user);
                if ( method_exists( 'user_switching', 'maybe_switch_url' ) ) {
                  $url = user_switching::maybe_switch_url( $target_user );
                  if ( $url ) {
                      printf(
                          '<a class="switch_user_to" href="%1$s">Switch to <span>%2$s</span></a>',
                          esc_url( $url ),
                          esc_html( $target_user->display_name )
                      );
                  }
                }
                ?>
                </div>
          </div>


           <?php
       }
       }else{
?>
   <div class="alert alert-info" role="alert">There are no team members added yet. Please click on <a class="font-weight-bold" href="<?php echo site_url('add-associated-buyer'); ?>">Add Associated Buyer</a> to onboard a team memebr</div>
 <?php     
      }
       ?>

    </section>
  </div>
</div>
<?php
get_footer();