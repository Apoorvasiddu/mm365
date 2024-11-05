<?php
/**
 * Template Name: SA - Council Details
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

$council_id     = $_REQUEST['councilid'];
$mode           = $_REQUEST['md'];
$nonce          = $_REQUEST['_wpnonce'];

if (!wp_verify_nonce( $nonce, 'sa_council_view' ) OR get_post_type($council_id) != 'mm365_msdc') {
    die( __( 'Unauthorised action', 'mm365' ) ); 
    wp_safe_redirect( site_url()."/list-councils" );
    exit;
 
}

$shortname = get_post_meta( $council_id, 'mm365_council_shortname', TRUE);
$address = get_post_meta( $council_id, 'mm365_council_address', TRUE);
$zip = get_post_meta( $council_id, 'mm365_council_zip', TRUE);
$contactperson = get_post_meta( $council_id, 'mm365_council_contactperson', TRUE);
$email = get_post_meta( $council_id, 'mm365_council_email', TRUE);
$phone = get_post_meta( $council_id, 'mm365_council_phone', TRUE);
$website = get_post_meta( $council_id, 'mm365_council_website', TRUE);
$current_country = get_post_meta( $council_id, 'mm365_council_country');
$current_state = get_post_meta( $council_id, 'mm365_council_state');
$current_city = get_post_meta( $council_id, 'mm365_council_city');

$logo = get_post_meta($council_id, 'mm365_council_logo',TRUE);
$description = get_post_meta($council_id, 'mm365_council_description',TRUE);
$map_link = get_post_meta($council_id, 'mm365_council_map_link',TRUE);
$facebook = get_post_meta($council_id, 'mm365_council_facebook',TRUE);
$instagram = get_post_meta($council_id, 'mm365_council_instagram',TRUE);
$twitter = get_post_meta($council_id, 'mm365_council_twitter',TRUE);
$linkedin = get_post_meta($council_id, 'mm365_council_linkedin',TRUE);
$youtube = get_post_meta($council_id, 'mm365_council_youtube',TRUE);
$flickr = get_post_meta($council_id, 'mm365_council_flickr',TRUE);

$cm_permissions = get_post_meta($council_id, 'mm365_additional_permissions',TRUE);
$cm_preselect = get_post_meta($council_id, 'mm365_council_preselect',TRUE);
$cm_subscription_required = get_post_meta($council_id, 'mm365_subscription_required',TRUE);
$footer_visibility = get_post_meta($council_id, 'mm365_council_hidefromfooter',TRUE);

if(!empty($logo)){
    foreach($logo as $key => $value){
        $path_to_file = $value;
    }
}else{
    $path_to_file = '';
}

get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
  </div>
  <div class="dashboard-content-panel">

<!-- Request for match form -->

<?php if($mode == 1): ?>
<h1 class="heading-large pbo-10"><a onclick="history.back();" href="#"><img class="back-arrow" src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-left.svg" height="36px" alt=""></a> View Council Details</h1>
<!-- View Block -->
<section class="company_preview">
   <div class="row mbo-30">
     <div class="col-md-6">
        <h6>Council name</h6><h4><?php echo get_the_title($council_id); ?></h4><br/>
        <h6>Description</h6>
        <p><?php echo esc_html($description); ?></p>
     </div>
     <div class="col-md-3"><h6>Short name</h6><h4><?php echo esc_html($shortname); ?></h4></div>
     <div class="col-md-3"><span class="council-preview-logo"><img src="<?php echo esc_url($path_to_file); ?>" alt=""/></span></div>
     
   </div>
   <div class="row mbo-30">
       <div class="col-3 col-lg-3"><h6>Address</h6><p><?php echo esc_html($address);   ?></p></div>
       <div class="col-3 col-lg-3"><h6>Country</h6><p><?php echo apply_filters('mm365_helper_get_countryname',$current_country[0]); ?></p></div>
       <div class="col-3 col-lg-3"><h6>State</h6><p><?php echo apply_filters('mm365_helper_get_statename',$current_state[0]); ?></p></div>
       <div class="col-3 col-lg-3"><h6>City</h6><p><?php echo apply_filters('mm365_helper_get_cityname',$current_city[0],""); ?></p></div>
   </div>
   <div class="row mbo-30">
       <div class="col-6 col-lg-3"><h6>ZIP</h6><p><?php echo esc_html($zip);  ?></p></div>
       <div class="col-6 col-lg-3"><h6>Contact Person</h6><p><?php echo esc_html($contactperson);   ?></p></div>
       <div class="col-6 col-lg-3"><h6>Contact Email</h6><p class="text-break"><?php echo esc_html($email);   ?></p></div>
       <div class="col-6 col-lg-3"><h6>Phone</h6><p><?php echo esc_html($phone);?></p></div>
   </div>
   <div class="row mbo-20">
       <div class="col-6 col-lg-3"><h6>Website</h6><p><?php echo esc_html($website);?></p></div>
       <div class="col-6 col-lg-3"><h6>MAP Link</h6><p><?php echo esc_html($map_link);?></p></div>
   </div>
   <div class="form-row">
      <div class="col-lg-12"> 
        <h6>Social Media</h6><hr/>
      </div>
   </div>
   <div class="row mbo-20">
       <div class="col-3 col-lg-2"><h6>Facebook</h6><a class="wraptext" target="_blank" href="https://facebook.com/<?php echo esc_html($facebook);?>"><?php echo esc_html($facebook);?></a></p></div>
       <div class="col-3 col-lg-2"><h6>Instagram</h6><a class="wraptext" target="_blank" href="https://instagram.com/<?php echo esc_html($instagram);?>"><?php echo esc_html($instagram);?></a></div>
       <div class="col-3 col-lg-2"><h6>Twitter</h6><a class="wraptext" target="_blank" href="https://twitter.com/<?php echo esc_html($twitter);?>"><?php echo esc_html($twitter);?></a></div>
       <div class="col-3 col-lg-2"><h6>LinkedIn</h6><a class="wraptext" target="_blank" href="https://linkedin.com/<?php echo esc_html($linkedin);?>"><?php echo esc_html($linkedin);?></a></div>
       <div class="col-3 col-lg-2"><h6>YouTube</h6><a class="wraptext" target="_blank" href="https://youtube.com/<?php echo esc_html($youtube);?>"><?php echo esc_html($youtube);?></a></div>
       <div class="col-3 col-lg-2"><h6>Flickr</h6><a class="wraptext"  target="_blank" href="https://flickr.com/<?php echo esc_html($flickr);?>"><?php echo esc_html($flickr);?></a></div>
   </div>

   <div class="form-row">
      <div class="col-lg-12"> 
        <h5>Permissions</h5><hr/>
      </div>
   </div>

   <div class="form-row form-group">
       <div class="col-lg-2"  data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests."> 
          <label for="">Match Approval Privilege</label><br/>
           <?php echo ($cm_permissions == 1) ? "Yes": "No"; ?>
        </div>

        <div class="col-lg-3"  data-intro="If pre-select is enabled, match request result set will be pre selected with council manager's council"> 
          <label for="">Pre-select council in match results</label><br/>
          <?php echo ($cm_preselect == 1) ? "Yes": "No"; ?>
        </div>

        <div class="col-lg-3"  data-intro="All the MBEs in this council requires an active subscription to appear in match results"> 
          <label for="">MBEs require active subscription to match</label><br/>
          <?php echo ($cm_subscription_required == 1) ? "Yes": "No"; ?>
        </div>
   </div>

   <div class="form-row mto-30">
      <div class="col-lg-12"> 
        <h5>Other Settings</h5><hr/>
      </div>
   </div>
   <div class="form-row form-group">
       <div class="col-lg-2"  data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests."> 
          <label for="">Hide from footer</label><br/>
          <?php echo ($footer_visibility == 1) ? "Yes": "No"; ?>
        </div>

        <div class="col-lg-2"  data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests."> 
          <label for="">Category</label><br/>
              <?php
               $existing_category = get_post_meta( $council_id, 'mm365_council_category', true ); 
               $categories = array('founding' => "Founding Councils", 
                                   'affiliates' => "Affiliate Councils",
                                   'global' => "Global Initiative",
                                   'mbda' => "MBDA Centers",
                                   'other' => "Other Councils",
                                  );
               echo $categories[$existing_category];                   
              ?>

        </div>

    </div>


</section>
<?php endif; ?>

<?php if($mode == 2): ?>
    <h1 class="heading-large pbo-10"><a onclick="history.back();" href="#"><img class="back-arrow" src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-left.svg" height="36px" alt=""></a> Edit Council</h1>
<!-- Edit Block -->
<form method="post" id="mm365_update_council" action="#"  data-parsley-validate enctype="multipart/form-data" >
<section class="company_preview">
   <div class="form-row form-group">
     <div class="col-md-9" data-intro="Full name of the council">
         <label for="">Council name<span>*</span></label>
         <input placeholder="Please council full name" class="form-control"  type="text" required name="council_name" id="council_name" pattern="/^[a-zA-Z-\s'’]+$/" minlength="4" value="<?php echo get_the_title($council_id); ?>"> 
     </div>
     <div class="col-md-3" data-intro="Short name of the council">
         <label for="">Short name<span>*</span></label>
         <input placeholder="Please enter short name" class="form-control"  type="text" required name="council_short_name" id="council_short_name" pattern="/^[a-zA-Z-\s]+$/" minlength="4" value="<?php echo esc_html($shortname);   ?>"> 
     </div>
   </div>
    <!-- Dropzone -->
    <div class="form-row form-group">
                        <div class="col" data-intro="Council logo should be in PNG format without any background color. Since the image is being displayed on the darker background (footer) the text inside logo should be white or brighter colors to be legible to read">
                        <label for="">Council logo<span>*</span>
                        <br/><small>Please upload background less PNG image with resolution 203x135px. Ensure that the texts in logo are light colored. Files size should not exceed 1MB</small>
                        </label>

                        <?php 
                                $existing_logo = array();
                                if($logo !=''):
                                
                                echo '<div class="filecard" style="display:none">';
                                foreach($logo as $attachment_id => $attachment_url)
                                {
                                        echo '<input type="checkbox" checked name="existing_files[]" id="file_to_delete_'.$attachment_id.'" value="'.$attachment_id.'" >';
                                        $path = str_replace( site_url('/'), ABSPATH, esc_url( $attachment_url) );
                                        $existing_logo[] = array(
                                                                "size" => filesize($path), 
                                                                "name" => basename (get_attached_file( $attachment_id )),
                                                                "id"   => $attachment_id,
                                                                "path" => $attachment_url
                                                            );

                                } 
                                echo "</div> <br/>";
                        endif;?>


                        <br/>
                            <div class="dropzonee" id="council-dropzone" data-existing="<?php echo htmlspecialchars(json_encode($existing_logo), ENT_QUOTES, 'UTF-8'); ?>">        
                                <div class="dz-message needsclick" for="files">Drag & drop Council's logo.<br/>               
                                    <small>Please upload background less PNG image with resolution 203x135px. Ensure that the texts in logo are light colored. Files size should not exceed 1MB</small>
                                    <div class="fallback">
                                    <input class="form-control-file"   type="file" id="wp_custom_attachment" name="files"  />
                                    </div>
                                </div>
                            </div>
                            <ul class="parsley-errors-list filled" id="validate-council-logo" aria-hidden="false"><li class="parsley-required capability-statemets-error">This value is required.</li></ul>       
                        </div>
    </div>
    <!-- Dropzone -->

    <div class="form-row form-group">
     <div class="col-12" data-intro="A short summary about council. The content will be displayed on the footer">
         <label for="">Short Description<span>*</span></label>
         <textarea placeholder="Short description" class="form-control"  type="text" required name="council_description" id="council_description"><?php echo esc_html($description); ?></textarea>
     </div>
   </div>
   <div class="form-row form-group" data-intro="Council's office address and location">
       <div class="col-lg-3">
         <label for="">Address<span>*</span></label>
         <textarea placeholder="Please enter council address" required class="form-control" name="council_address" id="" cols="30" rows="1"><?php echo esc_html($address);   ?></textarea> 
       </div>
       <div class="col-lg-3">
            <label for="">Country<span>*</span></label>
                <select required name="council_country" id="council_country"  class="country form-control mm365-single" data-parsley-errors-container=".countryError">
                    <option value="">-Select-</option>
                    <?php
                        $country_list = apply_filters('mm365_helper_countries_list',1);
                            foreach ($country_list as $key => $value) {   
                                if($current_country[0] == $value->id): $default_country = "selected"; else: $default_country = ''; endif;
                                echo "<option ".$default_country."  value='".$value->id."' >".$value->name."</option>";
                            }
                    ?>
                </select>
            <div class="countryError"></div> 
       </div>
       <div class="col-lg-3">
            <label for="">State<span>*</span></label>
            <select required name="council_state" id="council_state"  class="state form-control mm365-single" data-parsley-errors-container=".stateError" >
                <option value="">-Select-</option>
                <?php
                    $states_list = apply_filters('mm365_helper_states_list',$current_country[0]);                    
                    if(is_numeric($current_state[0])){
                        foreach ($states_list as $key => $value) {   
                            if($value->id == $current_state[0]): $default_state = "selected"; else: $default_state = ''; endif;
                            echo "<option ".$default_state." value='".$value->id."' >".$value->name."</option>";
                        }
                    }else{ echo "<option value='all' >NA</option>";}
                ?>
            </select>
            <div class="stateError"></div> 
       </div>
       <div class="col-lg-3">
            <label for="">City<span>*</span></label>
            <select required name="council_city" id="council_city"  class="city form-control mm365-single" data-parsley-errors-container=".cityError">
            <option value="">-Select-</option>
            <?php
            $cities_list =apply_filters('mm365_helper_cities_list',$current_state[0]);
            if(is_numeric($current_city[0])){
            foreach ($cities_list as $key => $value) {   
                if($value->id == $current_city[0]): $default_city = "selected"; else: $default_city = ''; endif;
                echo "<option ".$default_city." value='".$value->id."' >".$value->name."</option>";
             }
            }else{ echo "<option value='all' >NA</option>";}
          ?>
            </select>
            <div class="cityError"></div> 
       </div>
   </div>
   <div class="form-row form-group" data-intro="Council's contact information">
       <div class="col-lg-3">
        <label for="">ZIP<span>*</span></label>
        <input class="form-control" type="text"  placeholder="Please enter ZIP code" required data-parsley-required-message="Please enter a valid zip code." name="council_zip_code" pattern="[0-9+\-]+" data-parsley-length="[4, 15]"   value="<?php echo esc_html($zip);   ?>" data-parsley-length-message="The ZIP code should be 4 to 15 digits long">       
       </div>
       <div class="col-lg-3">
          <label for="">Contact person<span>*</span></label>
          <input placeholder="Please enter your full name" class="form-control"  pattern="[a-zA-Z\s]+" minlength="4" type="text" required name="council_contact_person" value="<?php echo esc_html($contactperson);   ?>"> 
       </div>
       <div class="col-lg-3"> 
          <label for="">Email<span>*</span></label>
          <input class="form-control" placeholder="Please enter a valid email"  type="email" required name="council_email"  data-parsley-type-message="This value should be a valid email ID." value="<?php echo esc_html($email);   ?>">          
       </div>
       <div class="col-lg-3">
            <label for="">Phone<span>*</span></label>
            <input placeholder="E.g. 555 555 5555" class="form-control" type="text"  required pattern="[0-9+()\s]+" data-parsley-length="[6, 15]"  name="council_phone" data-parsley-length-message="The phone number should be 6 to 15 digits long" value="<?php echo esc_html($phone);   ?>">       
        </div>
   </div>
   
   <div class="form-row form-group">
       <div class="col-lg-3" data-intro="Council's website"> 
          <label for="">Council website</label>
          <input placeholder="E.g. www.example.com" class="form-control" type="text"  name="website" data-parsley-type='url' data-parsley-type-message="This value seems to be invalid." value="<?php echo esc_url($website);   ?>">
        </div>
        <div class="col-lg-3" data-intro="Google / Bing Map link to council's office location. "> 
          <label for="">MAP Link</label>
          <input placeholder="E.g. https://goo.gl/maps/R5NdpkPzR1bhPx1n9" class="form-control" type="text"  name="map_link" data-parsley-type='url' data-parsley-type-message="This value seems to be invalid." value="<?php echo esc_url($map_link);   ?>">
        </div>
   </div>

   <div class="form-row">
      <div class="col-lg-12"> 
        <h6>Social Media</h6><hr/>
      </div>
   </div>
   <div class="form-row form-group" data-intro="Coucil's social media profile. Please add the social media handle, DO NOT paste entire url. For example if the council's facebook page is https://facebook.com/mmsdc just type mmsdc in the input for facebook">
       <div class="col-lg-2"> 
          <label for="">Facebook</label>
          <input placeholder="E.g. mmsdc" class="form-control" type="text"  name="facebook_id"  value="<?php echo esc_html($facebook);   ?>">
        </div>
        <div class="col-lg-2"> 
          <label for="">Instagram</label>
          <input placeholder="E.g. mmsdc" class="form-control" type="text"  name="instagram_id"  value="<?php echo esc_html($instagram);   ?>">
        </div>  
        <div class="col-lg-2"> 
          <label for="">Twitter</label>
          <input placeholder="E.g. mmsdc" class="form-control" type="text"  name="twitter_id"  value="<?php echo esc_html($twitter);   ?>">
        </div>     
        <div class="col-lg-2"> 
          <label for="">LinkedIn</label>
          <input placeholder="E.g. in/mmsdc" class="form-control" type="text"  name="linkedin_id"  value="<?php echo esc_html($linkedin);   ?>">
        </div>  
        <div class="col-lg-2"> 
          <label for="">Youtube</label>
          <input placeholder="E.g. mmsdc" class="form-control" type="text"  name="youtube_id"  value="<?php echo esc_html($youtube);   ?>">
        </div> 
        <div class="col-lg-2"> 
          <label for="">Flickr</label>
          <input placeholder="E.g. mmsdc" class="form-control" type="text"  name="flickr_id"  value="<?php echo esc_html($flickr);   ?>">
        </div> 
   </div>

   <div class="form-row">
      <div class="col-lg-12"> 
        <h5>Permissions</h5><hr/>
      </div>
   </div>

   <div class="form-row form-group">
       <div class="col-lg-2"  data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests."> 
          <label for="">Match Approval Privilege</label>
          <label class="toggle-control">
              <input class="toggler" type="checkbox" <?php echo ($cm_permissions == 1) ? "checked": ""; ?> name="permission_mr" id="permission_mr">
              <span class="control"></span>
          </label>
        </div>

        <div class="col-lg-3"  data-intro="If pre-select is enabled, match request result set will be pre selected with council manager's council"> 
          <label for="">Pre-select council in match results</label>
          <label class="toggle-control">
              <input class="toggler" type="checkbox" <?php echo ($cm_preselect == 1) ? "checked": ""; ?> name="preselect_mr" id="preselect_mr">
              <span class="control"></span>
          </label>
        </div>

        <div class="col-lg-3"  data-intro="All the MBEs in this council requires an active subscription to appear in match results"> 
          <label for="">MBEs require active subscription to match</label>
          <label class="toggle-control">
              <input class="toggler" type="checkbox" <?php echo ($cm_subscription_required == 1) ? "checked": ""; ?> name="mbe_require_subscription" id="mbe_require_subscription">
              <span class="control"></span>
          </label>
        </div>

   </div>

   <div class="form-row mto-30">
      <div class="col-lg-12"> 
        <h5>Other Settings</h5><hr/>
      </div>
   </div>
   <div class="form-row form-group">
       <div class="col-lg-2"  data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests."> 
          <label for="">Hide from footer</label>
          <label class="toggle-control">
              <input class="toggler" type="checkbox" <?php echo ($footer_visibility == 1) ? "checked": ""; ?> name="hide_from_footer" id="hide_from_footer">
              <span class="control"></span>
          </label>
        </div>

        <div class="col-lg-2"  data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests."> 
          <label for="">Category</label>
              <select name="council_category" id="" class="form-control mm365-single">

              <?php
               $existing_category = get_post_meta( $council_id, 'mm365_council_category', true ); 
               $categories = array('founding' => "Founding Councils", 
                                   'affiliates' => "Affiliate Councils",
                                   'global' => "Global Initiative",
                                   'mbda' => "MBDA Centers",
                                   'other' => "Other Councils",
                                  );

               foreach ($categories as $key => $value) {               
              ?>
                  <option <?php if($key == $existing_category): ?> selected <?php endif; ?> value="<?php echo esc_html($key); ?>"><?php echo esc_html($value); ?></option>
              <?php
               }
              ?>
              </select>
        </div>

    </div>

   <div class="form-row pto-10">
      <div class="col text-right">
            <input type="hidden" name="current_user" id="current_user" value="<?php echo  esc_html($user->ID); ?>" />
            <input type="hidden" name="council_id" id="council_id" value="<?php echo  esc_html($council_id); ?>" />
            <input type="hidden" id="after_success_redirect" name="after_success_redirect" value="<?php echo esc_url(site_url()."/list-councils");?>">
            <button id="sa_council_add" type="submit" class="btn btn-primary" ><?php _e('Update', 'mm365') ?></button>
      </div>
  </div>


</section>
</form>
<?php endif; ?>






  </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();