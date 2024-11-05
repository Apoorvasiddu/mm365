<?php
namespace Mm365;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class PublicFacing
{

    use CouncilAddons;

    function __construct()
    {
        add_filter('mm365_public_login_councilInfo', [$this, 'loginpage_CouncilInfo'], 10, 1);
    }

    /**
     * 
     * 
     * 
     */
    function loginpage_CouncilInfo($council_shortname)
    {
        $council_id = $this->get_councilIdByMeta('mm365_council_shortname', $council_shortname);
        if($council_id != NULL){

            ?>
        <div class="d-flex gap-20">
            <div class="logo">
                <img src="<?php echo esc_attr($this->get_councilLogo($council_id)); ?>"  alt="">
            </div>
            <div class="council">
                <h3>
                    <?php echo esc_html($this->get_council_info($council_id, 'name')); ?> (
                    <?php echo esc_html($this->get_council_info($council_id)); ?>)
                </h3>
                <p>
                    <?php echo esc_html($this->get_councilDescription($council_id)); ?>
                </p>
            </div>
        </div>
        <hr />
        <div class="d-flex gap-20 justify-between council-contacts">
            <div class="">
                <h4>Address</h4>
                <?php 
                echo wp_kses($this->get_councilAddress($council_id),array('br' => array()));
                
                ?>
            </div>
            <div>
                <h4>Contact</h4>
                <div class="pbo-10">
                <?php
                echo $this->get_councilEmail($council_id);
                ?>
                </div>
                
                <?php  
                echo "<i class='fas fa-phone-volume'></i>  ".$this->get_councilPhone($council_id);
                ?>
            </div>
            <div>
            <?php
                $web = $this->get_councilWebsite($council_id);
                $socialLinks = $this->get_councilSocialLinks($council_id);
                $icons = ['facebook' => 'fb.svg', 'instagram' => 'insta.svg', 'twitter' => 'twittr.svg', 'linkedin' => 'Linkin.svg', 'youtube' => 'youtub.svg', 'flickr' => 'flicker.svg'];
                
                if($web != ''){
                ?>
                    <h4>Website</h4>
                    <a href="<?php echo esc_html($web); ?>">
                        <?php echo esc_html($web); ?>
                    </a><br />
                <?php } ?>

                <?php
                if(!empty($socialLinks)){
                ?>
                <h4 class="pto-20">Connect with us</h4>
                <ul class="login-social-links">
                    <?php
                    foreach ($socialLinks as $key => $value) {
                        if ($value != '') {
                            ?>

                            <li><a href="https://<?php echo $key ?>.com/<?php echo $value ?>"
                                    target="_blank" tabindex="0"><img
                                        src="<?php echo get_template_directory_uri(); ?>/assets/images/<?php echo $icons[$key] ?>"
                                        alt=""></a></li>
                            <?php
                        }
                    }
                    ?>

                </ul>
                <?php } ?>
            </div>
        </div>
        <?php
        }

    }



}