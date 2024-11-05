<?php

/**
 * Buttons that appear for Council manager
 * on the top right side of view conference
 * 
 */

$conf_id = $args['conf_id']; 

?>

<a id="" href="#applications-received-section" class="btn btn-primary blue">View Participants</a>

<a id="" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'view_offline_conf' ), site_url('view-offline-conference').'?conf_id='.$conf_id.'&print_conf='.rand(3,1000) ); ?>" class="btn btn-primary green">Export Deligates Info</a>
