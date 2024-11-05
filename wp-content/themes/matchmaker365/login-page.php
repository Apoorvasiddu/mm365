<?php

/**
 * Template Name: Custom Login Page
 *
 */

get_header();

//default council
$council_shortname = 'mmsdc';

if(isset($_REQUEST['council'])){
  $council_shortname  = $_REQUEST['council'];
} 

?>

<div class="container">
  <div class="row pto-100 pbo-100 login-councilblock" style="min-height:50vh">
    <div class="col-md-7 pto-100">


<?php apply_filters('mm365_public_login_councilInfo',$council_shortname) ?>

    </div>
    <div class="col-md-4 offset-md-1 pto-30">
      <?php echo do_shortcode('[uwp_login]'); ?>
    </div>
  </div>
</div>


<?php 


get_footer();