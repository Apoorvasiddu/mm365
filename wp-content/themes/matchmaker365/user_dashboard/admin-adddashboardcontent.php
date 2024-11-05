<?php
/**
 * Template Name: SA - User Dashboard Content
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
  </div>
  <div class="dashboard-content-panel">

  <h1 class="heading-large pbo-10">Manage User Dashboard Content</h1>
<!-- Edit Block -->

<section class="company_preview">

<div class="form-row form-group">

    <div class="col-6">
       <h5>Add Tip/Update</h5>
       <hr/>
       <!-- Form here -->
       <form method="post" id="mm365_add_dashboard_tip" action="#"  data-parsley-validate enctype="multipart/form-data" >
            
          <div class="form-row">
          <div class="col-12">
                <label for="">Title</label><br/>
                <input type="text" name="title" required class="form-control">
              </div>  
              <div class="col-12 pto-20">
                <label for="">Content</label>
                <textarea id="add_user_tip" name="user_tip" required rows="8"></textarea>
              </div>  
          </div>
          <div class="form-row pto-10">
              <div class="col-3 d-flex flex-column">
                  <label for="">Type</label>
                  <div>
                  <input type="radio" name="type" value="tip" id="" checked /> Tip 
                  &nbsp;&nbsp;<input type="radio" name="type" value="update" id="" /> Update 
                  </div>
              </div>
              <div class="col-3 d-flex flex-column">
                  <label for="">Content visible to</label>
                  <select name="content_visible_to" class="mm365-single">
                    <option value="Buyer">Buyer</option>
                    <option value="Supplier">Supplier</option>
                    <option value="Super-Buyer">Super Buyer</option>
                    <option value="Both">All</option>
                  </select>
              </div>
 

            </div>
            <div class="form-row pto-10">
            <div class="col-12">
                    <input type="hidden" id="after_success_redirect" name="redirect_to" value="<?php echo esc_html(site_url('manage-dashboard-contents'));?>">
                    <button type="submit" class="btn btn-primary" ><?php _e('Add', 'mm365') ?></button>
              </div>
            </div>
        </form>

      
    </div>

    <div class="col-6">

    <h5>Tips and updates</h5>
       <hr/>
       <small>The following tips will be randomly visible to users based on thir user roles</small><br/><br/>
       <?php
          $args = array(  
              'post_type' => 'mm365_updatesandtips',
              'posts_per_page' => -1, 
              'orderby' => 'date', 
          );
          $councils_list = array();
          $loop = new WP_Query( $args );  
          while ( $loop->have_posts() ) : $loop->the_post(); 
        ?>
            <div class="card mbo-20">
                <div class="card-body">
                    <h5 class="card-title"><?php echo get_the_title(); ?></h5>
                    <p class="card-text"><?php echo get_the_content(); ?></p>
                    <hr/>
                    <div class="d-flex flex-row">

                      <div class="card-link d-flex flex-column">
                        <label for="">Showing to</label>
                        <span class="text-capitalize"><?php echo get_post_meta(get_the_ID(),'_mm365_visible_to',true); ?></span> 
                      </div>
                      <div class="card-link d-flex flex-column ">
                        <label for="">Type</label>
                        <span class="text-capitalize"><?php echo get_post_meta(get_the_ID(),'_mm365_content_type',true); ?></span> 
                      </div>
                      <div class="card-link d-flex flex-column ">
                        <label for="">Visibility</label>
                        <label class="toggle-control">
                            <input data-tippostid="<?php echo get_the_ID(); ?>" class="toggler tip-visibility-toggle" <?php echo (get_post_meta(get_the_ID(),'_mm365_visibility',true) == 1) ? 'checked': ''; ?>  type="checkbox" name="permission_mr" id="permission_mr">
                            <span class="control"></span>
                        </label>
                      </div>
                      <div class="card-link d-flex flex-column ">
                        <label for="">Action</label>
                        <a href="#" data-tippostid="<?php echo get_the_ID(); ?>" class="delete-tip-post text-danger">Delete</a>
                      </div>
                    </div>

                    <!-- <a href="#" >Buyer</a> -->
                </div>
            </div>
        <?php
          endwhile;
          wp_reset_postdata();
        ?>
    </div>

</div>


<!-- Form Items ends here -->
</section>



  </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();