<?php

/**
 * Template Name: Custom Home Page
 *
 */

$user = wp_get_current_user();
if (is_user_logged_in() and in_array('business_user', (array) $user->roles)) {

    //Check if user has atleast one registered company

    //If user has more than one registered company redirect to switch

    //If Only one user write cookie and redirect

    //Get the list fo companies added by the user
    // $multipleCompanyClass = new MultiCompanies();
    // $companies_list = $multipleCompanyClass->users_companies($user->ID);
    $companies_list = apply_filters('mm365_businessuser_companies_list',$user->ID);

    // //More than two companies redirect to select-company page
    if (count($companies_list) > 1 and !isset($_COOKIE['active_company_id'])) {
        $redirect = site_url() . '/select-company';
        wp_redirect($redirect);
        exit;
    } else {

        //Change this to Dashboard page
        wp_redirect('user-dashboard');

    }

   // wp_redirect('user-dashboard');


} elseif (is_user_logged_in() and in_array('mmsdc_manager', (array) $user->roles)) {
    wp_redirect('admin-dashboard');
} elseif (is_user_logged_in() and in_array('council_manager', (array) $user->roles)) {
    wp_redirect('council-dashboard');
} elseif (is_user_logged_in() and in_array('super_buyer', (array) $user->roles)) {
    wp_redirect('superbuyer-dashboard');
}


get_header();

//$councilClass = new Council();
?>


<!-- Hero Section -->

<!-- Second fold -->
<main class="home_cover">

    <!-- Logo portion -->
    <section class="pre-hero">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-6">
                    <img class="logo" src="<?php echo get_template_directory_uri() ?>/assets/images/mmsdc_logo.png"
                        alt="">
                </div>
                <div class="col-6">
                    <div class="d-flex justify-content-end align-items-center hero-nav">
                        <a href="<?php echo site_url('/login'); ?>">Login / Signup</a>
                        <div class="burger">
                            <svg class="burger-btn" width="50" height="22" viewBox="0 0 40 26"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect class="burger-btn--1" width="40" height="4" rx="3" ry="3" />
                                <rect class="burger-btn--2" width="40" height="4" y="10" rx="3" ry="3" />
                                <rect class="burger-btn--3" width="40" height="4" y="20" rx="3" ry="3" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- brand ribbon -->
    <div class="council_icons">

        <div class="container">
            <div class="row">
                <!-- Founding council logos -->
                <div class="founders-icons">
                    <?php
                    foreach (apply_filters('mm365_council_list_by_category','founding') as $id => $shortname) {
                        $logo = get_post_meta($id, 'mm365_council_logo', TRUE);
                        if (!empty($logo)) {
                            foreach ($logo as $key => $value) {
                                $path_to_file = $value;
                            }
                        } else {
                            $path_to_file = '';
                        }
                        ?>
                        <img src="<?php echo esc_attr($path_to_file) ?>" alt="<?php echo esc_html($shortname); ?>">
                        <?php
                    }
                    ?>
                </div>
                <!-- Founding council logos ends -->
            </div>
        </div>


        <section class="homepop">
            <div class="container">
                <div class="pentagrid">
                    <div class="p-col">
                        <h3>Founding Councils</h3>
                        <ul>
                            <?php
                            foreach (apply_filters('mm365_council_list_by_category','founding') as $id => $shortname) {
                                ?>
                                <li><a href="#" data-council-id="<?php echo esc_html($id); ?>" class=""><?php echo esc_html($shortname); ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="p-col">
                        <h3>Affiliate Councils</h3>
                        <ul>
                            <?php
                            foreach (apply_filters('mm365_council_list_by_category','affiliates') as $id => $shortname) {
                                ?>
                                <li><a href="#" data-council-id="<?php echo esc_html($id); ?>" class=""><?php echo esc_html($shortname); ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="p-col">
                        <h3>Global Initiative</h3>
                        <ul>
                            <?php
                            foreach (apply_filters('mm365_council_list_by_category','global') as $id => $shortname) {
                                ?>
                                <li><a href="#" data-council-id="<?php echo esc_html($id); ?>" class=""><?php echo esc_html($shortname); ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="p-col">
                        <h3>MBDA Centers</h3>
                        <ul>
                            <?php
                            foreach (apply_filters('mm365_council_list_by_category','mbda') as $id => $shortname) {
                                ?>
                                <li><a href="#" data-council-id="<?php echo esc_html($id); ?>" class=""><?php echo esc_html($shortname); ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="p-col">
                        <h3>Other Associations</h3>
                        <ul>
                            <?php
                            foreach (apply_filters('mm365_council_list_by_category','other') as $id => $shortname) {
                                ?>
                                <li><a href="#" data-council-id="<?php echo esc_html($id); ?>" class=""><?php echo esc_html($shortname); ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Councils popup area -->
        <section class="home-council-pop">
            <div class="container">
                <div class="row">
                    <div class="col-md-2">
                        <!-- back button here -->
                        <a href="#" class="close-content">
                            <svg width="32px" height="32px" viewBox="0 -6.5 38 38" version="1.1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                fill="#ffffff" stroke="#ffffff">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <title>left-arrow</title>
                                    <desc>Created with Sketch.</desc>
                                    <g id="icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <g id="ui-gambling-website-lined-icnos-casinoshunter"
                                            transform="translate(-1641.000000, -158.000000)" fill="#ffffff"
                                            fill-rule="nonzero">
                                            <g id="1" transform="translate(1350.000000, 120.000000)">
                                                <path
                                                    d="M317.812138,38.5802109 L328.325224,49.0042713 L328.41312,49.0858421 C328.764883,49.4346574 328.96954,49.8946897 329,50.4382227 L328.998248,50.6209428 C328.97273,51.0514917 328.80819,51.4628128 328.48394,51.8313977 L328.36126,51.9580208 L317.812138,62.4197891 C317.031988,63.1934036 315.770571,63.1934036 314.990421,62.4197891 C314.205605,61.6415481 314.205605,60.3762573 314.990358,59.5980789 L322.274264,52.3739093 L292.99947,52.3746291 C291.897068,52.3746291 291,51.4850764 291,50.3835318 C291,49.2819872 291.897068,48.3924345 292.999445,48.3924345 L322.039203,48.3917152 L314.990421,41.4019837 C314.205605,40.6237427 314.205605,39.3584519 314.990421,38.5802109 C315.770571,37.8065964 317.031988,37.8065964 317.812138,38.5802109 Z"
                                                    id="left-arrow"
                                                    transform="translate(310.000000, 50.500000) scale(-1, 1) translate(-310.000000, -50.500000) ">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            Home
                        </a>
                    </div>
                    <div id="council-info-ajax-response" class="col-md-10">
                        <!-- content appears here through AJAX -->
                    </div>
                </div>
            </div>
        </section>

    </div>


    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2>CONNECTING</h2>
                    <h3 class="thin-text">SUPPLIERS WITH SOURCING OPPORTUNITIES</h3>
                    <p>MatchMaker365 is a tool leveraging technology for a streamlined process for you and your buyers.
                        MatchMaker365 connects buyers, with actual sourcing opportunities, to suppliers that provide the
                        goods or services needed.</p>
                    <div class="buttons">
                        <a href="<?php echo site_url('/login'); ?>" class="btn">LOGIN</a>
                        <a href="<?php echo site_url('/register') ?>" class="btn hollow">SIGN UP</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
<section class="section-black">

    <div class="container">

        <div class="row">
            <div class="col-md-12 pbo-30">
                <h2>GLOBAL <span class="thin-text">INITIATIVE</span></h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <img class="w-full" src="<?php echo get_template_directory_uri() ?>/assets/images/home/home_img_1.png"
                    alt="">
            </div>
            <div class="col-md-6">
                <img class="w-full" src="<?php echo get_template_directory_uri() ?>/assets/images/home/home_right.png"
                    alt="">
            </div>
        </div>

        <div class="row">
            <div class="col-12 pto-30 pbo-30">
                <p>The MMSDC Global Initiative program offers solutions to companies seeking international expansion by
                    cultivating alliances and partnerships with strategic institutions and trade promotion offices. With
                    the support of Matchmaker365, the Global Initiative fosters alliances that lead to creating teaming
                    arrangements, promoting partnerships, scaling businesses internationally, and facilitating direct
                    connections to establish business abroad.</p>
                <p>The Global Initiative develops strategic projects and consortiums to integrate supply chains with
                    re-shoring strategies through the United States-Mexico-Canada Trade Agreement (USMCA).</p>
            </div>
        </div>



    </div>

</section>




<?php

get_footer();