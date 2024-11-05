<?php
/**
 * Template Name: Match Request Publish
 *
 */


$user = wp_get_current_user();
$mr_id = $_REQUEST['mr_id'];
//Check if post belongs to current user
if(get_current_user_id() == get_post_field( 'post_author', $mr_id ))
{
        $matchrequest_post = array('ID'=> $mr_id,'post_status'  => 'publish');
        if(wp_update_post($matchrequest_post) AND  apply_filters('mm365_match_findsuppliers',$mr_id)){

            $matched_companies = maybe_unserialize(get_post_meta($mr_id, 'mm365_matched_companies', true ));
            $status            =  get_post_meta($mr_id, 'mm365_matchrequest_status', true ) ;

            if(!empty($matched_companies) AND  $status =='pending'){
              //If there are matches
              $icon     = 'success';
              $message  = "We have received your match request.";
              $sub_text = "After your request is approved, you can view match results.";
            }elseif(!empty($matched_companies) AND  $status =='auto-approved'){

              $url         = site_url().'/view-match?mr_id='.$mr_id;
              $redirect_to = add_query_arg( '_wpnonce', wp_create_nonce( 'view_match' ), $url );
              wp_redirect($redirect_to);
            }
            else{
              //If there are no matches
              $icon     = 'warning';
              $message  = "Your match request produced no matches.";
              $sub_text = "Kindly note that match requests depend entirely on the NAICS code provided. If the code is invalid or incomplete, it may result in failure to find the appropriate supplier.<br/><br/>Please refine your search by selecting as many drop down details as possible and by providing more specific details in the description field.<br/> Please click on the below button to edit the match request.";
            
              $url = site_url().'/edit-matchrequest?mr_id='.$mr_id."&mr_state=active";
              $sub_text .= '<br/><br/><a href="'.wp_nonce_url( $url, 'match_request' ).'" class="btn btn-primary">Edit Match Request</a>';
            
            }
          
            //If there are no matches
        }else{
            $icon     = 'failure';
            $message  = "Oops! Something went wrong";
            $sub_text = "This could be a temporary glitch in the system. Please try again later";
        }

}else{
    $icon     = 'failure';
    $message  = "Unauthorised action!";
    $sub_text =  "";
}




if(is_user_logged_in() AND in_array( 'business_user', (array) $user->roles )){
  get_header();
?>
<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
  </div>
  <section class="dashboard-content-panel">
  <!-- Panel starts -->
  <div class="container">
  <div class="row">
    <div class="col-12">
        <div class="card  text-center pto-100 pbo-100">

            <img class="card-img-top" height="120px" src="<?php echo get_template_directory_uri()?>/assets/images/<?php echo esc_html($icon); ?>.svg" alt="Card image cap">
            <div class="card-body">
            <h1 class="card-title"><?php echo esc_html($message);?></h1>
            <p class="card-text"><?php echo $sub_text;?></p>
            </div>
        </div>
    </div>
  </div>
  </div>
  <!-- Panel ends -->
  </section>

</div>
<?php } else {       $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      wp_redirect(wp_login_url($actual_link));?>
<h2>Please sign in to continue</h2>
<?php } ?>
<?php
get_footer();