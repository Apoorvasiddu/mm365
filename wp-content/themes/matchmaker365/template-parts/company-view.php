<div class="pbo-100">
<?php
$company_id = $_REQUEST['cid'];
if(isset($company_id) AND $company_id !=''){
    

    $user = wp_get_current_user();

    if(in_array( 'mmsdc_manager', (array) $user->roles ) || in_array( 'council_manager', (array) $user->roles ) || in_array( 'super_buyer', (array) $user->roles )){
        
        (isset($_REQUEST['mr_id'])) ? $mr_id = $_REQUEST['mr_id'] : $mr_id = '';
        
        if(!isset($_REQUEST['ret'])):
           echo '<a onclick="history.back();" href="#"><h1 class="heading-large pbo-20"><img class="back-arrow" src="'.get_template_directory_uri().'/assets/images/arrow-left.svg" height="36px" alt=""> Company Information</h1></a>';
        else:
           echo '<a href="'.site_url().'/view-match-request-detail/?mr_id='.$mr_id.'"><h1 class="heading-large pbo-20"><img class="back-arrow" src="'.get_template_directory_uri().'/assets/images/arrow-left.svg" height="36px" alt=""> Company Information</h1></a>';
        endif;
        
        apply_filters('mm365_company_show',$company_id,'publish',false);

    }else{
         
         
         if(isset($_REQUEST['mr_id']) AND $_REQUEST['mr_id'] !=''){

            $mr_id = $_REQUEST['mr_id'];

                if ( FALSE === get_post_status( $mr_id ) ) {

                    echo "Match Request Does not exist";

                } else {	  

                    // The post exists	
                    $matched_companies = maybe_unserialize(get_post_meta( $mr_id, 'mm365_matched_companies', true )); 

                    $approved_companies = array();

                    foreach ($matched_companies as $key => $value) {

                        if($value[1] == '1'){ array_push($approved_companies,$value[0]); }

                    }
                    $requester = get_post_meta( $mr_id, 'mm365_requester_company_id', true );

                    //check if the company is a part of approved company or company is match request owner
                    if(in_array($company_id, $approved_companies)   OR  ($requester == $company_id)){

                        echo '<a onclick="history.back();" href="#"><h1 class="heading-large pbo-20"><img class="back-arrow" src="'.get_template_directory_uri().'/assets/images/arrow-left.svg" height="36px" alt=""> Company Information</h1></a>';

                        apply_filters('mm365_company_show', $company_id, 'publish', false);

                    }else{
                        echo "Unauthorized access!";
                    }
                }
        }else{
            echo "Unauthorized access!";
        }
         //Cross check for authorization match with mr_id

    }
}
?>
</div>