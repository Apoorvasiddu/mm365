<!-- Edit form fields copany reg -->
<?php
$cmp_id = __( $args['cmp_id'] ); 

$stype = get_post_meta($cmp_id, 'mm365_service_type', true);
?>
<div class="form-group row">
    <div class="col-lg-7">
        <label for="">Company name<span>*</span>
        </label>
        <input placeholder="Please enter your company name" class="form-control" type="text" name="company_name"
            minlength="4" value="<?php echo get_the_title($cmp_id); ?>" required>
    </div>
    <div class="col-lg-2">
        <label for="">Service Type<span>*</span></label><br />
        <input type="radio" name="service_type" value="buyer" <?php if ($stype == 'buyer') {
            echo ' checked="true" ';
        } ?>
        /> Buyer
        &nbsp;<input type="radio" name="service_type" value="seller" <?php if ($stype == 'seller') {
            echo ' checked="true" ';
        } ?>
        /> Supplier
    </div>
    <div class="col-lg-3">
        <label for="">Council<span>*</span></label>
        <select name="company_council" id="company_council" required class="form-control">
            <?php
            $current_council = get_post_meta($cmp_id, 'mm365_company_council', true);
            apply_filters('mm365_dropdown_councils', $current_council);
            ?>
        </select>
    </div>
</div>

<!-- Devider -->
<div class="form-row form-group">
    <div class="col-lg-12">
        <label for="">
            <div id="company_desc_title">
                <?php if ($stype == 'seller'): ?>Description of services or products offered
                <?php else: ?>Company Details
                <?php endif; ?><span>*</span>
            </div>
            <small>Please include a complete description of your company as well as all relevant keywords about
                your products and services to be listed in match making results</small>
        </label>
        <textarea id="company_description" name="company_description"
            data-parsley-errors-container=".descError"><?php echo get_post_meta($cmp_id, 'mm365_company_description', true); ?></textarea>
        <div class="descError"></div>
    </div>
</div>

<div class="form-row form-group">




<div id="basicSearchFields" class="col-lg-4">
                                    <label for="">Find NAICS codes<br/>
                                          </label>
                                   
                                    <section  class="naics-codes">
                                          <div  class="form-row">
                                                <div class="col naics-input-box">
                                                      <input class="form-control naics-input" type="text" min="10"
                                                            max="999999" name="naics_code" placeholder="search and select naics code" >
                                                            <p class="naic-info"></p>
                                                            <div class="naic-suggested"></div>
                                                </div>
                                          </div>
                                    </section>
                                   <label><small>Search by category name or NAICS code then click the list to add</small></label>
                                   <a class="external_link" target="_blank" href="https://www.naics.com/search/"><span>Search for NAICS code</span> &nbsp;<img src="<?php echo get_template_directory_uri() ?>/assets/images/share.svg" alt=""></a>
                              </div>
                              <div class="col-lg-3">
                              <label for="">Selected NAICS codes<span>*</span><br/></label>
                                    <section class="naics-codes-dynamic">
                                    <?php foreach ((get_post_meta($cmp_id, 'mm365_naics_codes')) as $key => $value) { ?>
                          <section class="naics_remove">
                            <div class="form-row  form-group">
                              <div class="col">
                                <input id="mr_naics" class="form-control" type="number" readonly min="10" max="999999"
                                  name="naics_codes[]" value="<?php echo $value; ?>">
                              </div>
                              <div class="col-2 d-flex align-items-end naics-codes-btn"><a href="#"
                                  class="remove-naics-code plus-btn">-</a></div>
                            </div>
                          </section>
                        <?php } ?>



                                    </section>
                              </div>

 




<div class="col-12 d-block d-sm-none pbo-30"></div>

    <div class="col-lg-2">
        <label for="">Contact person<span>*</span></label>
        <input placeholder="Eg:John Doe" class="form-control" type="text" pattern="[a-zA-Z\s]+"
            minlength="4" required name="contact_person"
            value="<?php echo get_post_meta($cmp_id, 'mm365_contact_person', true); ?>">
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-3">
        <label for="">Company address<span>*</span></label>
        <textarea placeholder="Please enter your address" required class="form-control" name="company_address" id=""
            cols="30" rows="1"><?php echo get_post_meta($cmp_id, 'mm365_company_address', true); ?></textarea>
    </div>

</div>

<div class="form-row form-group">

    <div class="col-lg-3">
        <?php ?>
        <label for="">Country<span>*</span></label>
        <select required name="company_country" id="" class="country form-control mm365-single"
            data-parsley-errors-container=".countryError">
            <?php
            $country_list = apply_filters('mm365_helper_countries_list', 10);
            $current_country = get_post_meta($cmp_id, 'mm365_company_country');
            foreach ($country_list as $key => $value) {
                if ($current_country[0] == $value->id):
                    $default_country = "selected";
                else:
                    $default_country = '';
                endif;
                echo "<option " . $default_country . "  value='" . $value->id . "' >" . $value->name . "</option>";
            }
            ?>
        </select>
        <div class="countryError"></div>
    </div>

    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-3">
        <label for="">State<span>*</span></label>
        <select required name="company_state" id="" class="state form-control mm365-single"
            data-parsley-errors-container=".stateError">
            <?php
            $states_list = apply_filters('mm365_helper_states_list', $current_country[0], NULL);
            $current_state = get_post_meta($cmp_id, 'mm365_company_state');
            if (is_numeric($current_state[0])) {
                foreach ($states_list as $key => $value) {
                    if ($value->id == $current_state[0]):
                        $default_state = "selected";
                    else:
                        $default_state = '';
                    endif;
                    echo "<option " . $default_state . " value='" . $value->id . "' >" . $value->name . "</option>";
                }
            } else {
                echo "<option value='all' >NA</option>";
            }
            ?>
        </select>
        <div class="stateError"></div>
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-3">
        <label for="">City<span>*</span></label>
        <select required name="company_city" id="" class="city form-control mm365-single"
            data-parsley-errors-container=".cityError">
            <?php
            $cities_list = $states_list = apply_filters('mm365_helper_cities_list', $current_state[0], NULL);
            $current_city = get_post_meta($cmp_id, 'mm365_company_city');
            if (is_numeric($current_city[0])) {
                foreach ($cities_list as $key => $value) {
                    if ($value->id == $current_city[0]):
                        $default_city = "selected";
                    else:
                        $default_city = '';
                    endif;
                    echo "<option " . $default_city . " value='" . $value->id . "' >" . $value->name . "</option>";
                }
            } else {
                echo "<option value='all' >NA</option>";
            }
            ?>
        </select>
        <div class="cityError"></div>
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-3">
        <label for="">ZIP<span>*</span></label>
        <input placeholder="Please enter ZIP code" class="form-control" type="text" required
            data-parsley-required-message="Please enter a valid zip code." name="zip_code" pattern="^[A-Z0-9 -]+$"
            data-parsley-length="[3, 15]" name="zip_code"
            value="<?php echo get_post_meta($cmp_id, 'mm365_zip_code', true); ?>"
            data-parsley-length-message="The ZIP code should be 4 to 15 digits long">
    </div>
</div>

<div class="form-row form-group">

    <div class="col-lg-2">
        <label for="">Phone Type<span>*</span></label><br />
        <?php
        $current_phone_type = get_post_meta($cmp_id, 'mm365_company_phone_type', true);
        $phone_types = array("mobile" => "Mobile", "landphone" => "Land Phone");
        ?>
        <select required name="primary_phone_type" id="" class=" form-control mm365-single"
            data-parsley-errors-container=".primaryPhoneNumberTypeError">
            <option value="">-Select-</option>
            <?php
            foreach ($phone_types as $key => $value) {
                ?>
                <option value="<?php echo esc_html($key) ?>" <?php echo ($key == $current_phone_type) ? 'selected' : '' ?>>
                    <?php echo esc_html($value) ?> </option>
                <?php
            }
            ?>
        </select>
        <div class="primaryPhoneNumberTypeError"></div>
    </div>

    <div class="col-lg-3">
        <label for="">Phone Number<span>*</span></label>
        <input placeholder="E.g. 555 555 5555" class="form-control" type="text" required name="phone"
            pattern="[0-9+()\s]+" data-parsley-length="[6, 15]" name="phone"
            value="<?php echo get_post_meta($cmp_id, 'mm365_company_phone', true); ?>"
            data-parsley-length-message="The phone number should be 6 to 15 digits long">
    </div>

    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-3">
        <label for="">Email<span>*</span></label>
        <input class="form-control" placeholder="Please enter a valid email" type="email" required name="company_email"
            value="<?php echo get_post_meta($cmp_id, 'mm365_company_email', true); ?>"
            data-parsley-type-message="This value should be a valid email ID.">
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4">
        <label for="">Company website</label>
        <input placeholder="E.g. www.example.com" class="form-control" type="text" name="website"
            data-parsley-type='url' value="<?php echo get_post_meta($cmp_id, 'mm365_website', true); ?>"
            data-parsley-type-message="This value seems to be invalid.">
    </div>
</div>

<div class="form-row form-group">
    <div class="col-lg-4">
        <label for="">Alternate contact person</label>
        <input placeholder="Please enter the full name" class="form-control" type="text" type="text"
            pattern="[a-zA-Z\s]+" minlength="4" name="alt_contact_person"
            value="<?php echo get_post_meta($cmp_id, 'mm365_alt_contact_person', true); ?>">
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4">
        <label for="">Alternate phone</label>
        <input placeholder="E.g. 333 333 3333" class="form-control" type="text" pattern="[0-9+()\s]+"
            data-parsley-length="[6, 15]" name="alt_phone"
            value="<?php echo get_post_meta($cmp_id, 'mm365_alt_phone', true); ?>"
            data-parsley-length-message="The phone number should be 6 to 15 digits long">
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4">
        <label for="">Alternate email</label>
        <input placeholder="Please enter an alternative email" class="form-control" type="email"
            data-parsley-type='email' name="alt_email"
            value="<?php echo get_post_meta($cmp_id, 'mm365_alt_email', true); ?>"
            data-parsley-type-message="This value should be a valid email ID.">
    </div>
</div>


<div class="form-row form-group">


    <div class="col-lg-4">
        <label for="">Industry<span>*</span></label>
        <select required data-parsley-errors-container=".industryError" name="industry[]" id="industry"
            data-parsley-required-message="Must select at least one" class="form-control  mm365-multicheck" multiple>
            <?php
            $current_industry = get_post_meta($cmp_id, 'mm365_industry');
            apply_filters('mm365_dropdown_industries', (array) $current_industry);
            ?>
            <option value="other" <?php if (in_array("other", $current_industry)) {
                echo "selected";
            } ?>
                id="other_industry">Other</option>
        </select>
        <div class="industryError"></div>
        <input type="text" placeholder="Others (Separate using commas)" class="form-control" id="other_industry_input"
            name="other_industry" value="<?php if (in_array("other", $current_industry)):
                $other_pos = (array_search('other', $current_industry));
                echo implode(",", array_slice($current_industry, $other_pos + 1));
            endif; ?>">
    </div>

    <div class="col-lg-4">
        <label for="">Company services<span>*</span></label>

        <select name="services[]" id="services" required data-parsley-errors-container=".servError"
            data-parsley-required-message="Must select at least one" multiple class="form-control  mm365-multicheck">
            <?php
            $current_services = get_post_meta($cmp_id, 'mm365_services');
            apply_filters('mm365_dropdown_services', (array) $current_services);
            ?>
            <option value="other" id="other_services" <?php if (in_array("other", $current_services)) {
                echo "selected";
            } ?>>Other</option>
        </select>
        <div class="servError"></div>
        <input type="text" class="form-control" placeholder="Others (Separate using commas)" value="<?php if (in_array("other", $current_services)):
            $other_pos = (array_search('other', $current_services));
            echo implode(",", array_slice($current_services, $other_pos + 1));
        endif; ?>"
            id="other_services_input" name="other_services">
    </div>

</div>

<div id="minority_category_block" class=" minority_category_block">

    <div class="form-row">
        <div class="col-12">
            <label for="">Locations where the services or products are available</label>
        </div>
    </div>
    <div class="form-row form-group">
        <!-- v1.6 on wards | Servicable location ends-->
        <div class="col-lg-3">
            <label for="">Countries<span>*</span></label>
            <select required name="serviceable_countries[]" id="serviceable-countries"
                class="serviceable-countries form-control mm365-multicheck" multiple
                data-parsley-errors-container=".srv-countryError">
                <option value="">-Select-</option>
                <?php
                //mm365_cmp_serviceable_states
                $countries = get_post_meta($cmp_id, 'mm365_cmp_serviceable_countries');
                apply_filters('mm365_dropdown_countries', $countries)
                    ?>
            </select>
            <div class="srv-countryError"></div>
        </div>
        <div class="col-12 d-block d-sm-none pbo-30"></div>
        <div class="col-lg-3">
            <label for="">States<span>*</span></label>
            <select required name="serviceable_states[]" id="serviceable-states"
                class="serviceable-states form-control mm365-multicheck" multiple
                data-parsley-errors-container=".srv-stateError">
                <option value="">-Select-</option>
                <?php
                //ALL states condition not fixed
                $serviceable_states = get_post_meta($cmp_id, 'mm365_cmp_serviceable_states');
                apply_filters('mm365_company_preload_serviceable_states', $countries, $serviceable_states);
                ?>
            </select>
            <div class="srv-stateError"></div>
        </div>
        <!-- Servicable location ends -->

        <div class="col-lg-3">
            <label for="">Minority classification<span>*</span></label>
            <select required data-parsley-errors-container=".minority_categoryError" name="minority_category"
                id="minority_category" class="form-control mm365-single">
                <option value="">-select-</option>
                <?php
                $current_minority_category = get_post_meta($cmp_id, 'mm365_minority_category', true);
                apply_filters('mm365_dropdown_minoritycategory', $current_minority_category);
                ?>
            </select>
            <div class="minority_categoryError"></div>
        </div>
        <div class="col-lg-3">
            <label for="">International assistance from council<br /><small>Please select multiple international
                    assistances which you are interested in</small></label>
            <?php $current_intassi = get_post_meta($cmp_id, 'mm365_international_assistance'); ?>
            <select name="international_assistance[]" id="international_assistance" multiple
                class="form-control mm365-multicheck">
                <option value="">-Select-</option>
                <?php
                apply_filters('mm365_dropdown_internationalassistance', $current_intassi);
                ?>
            </select>
        </div>
        <div class="col-12 d-block d-sm-none pbo-30"></div>
    </div>
</div>


<div class="form-row form-group" id="minority_category_block_uploads">
    <div class="col">
        <div class="pad-top-20 pad-bot-20">
            <label for="">Capability statement<span>*</span>
                <br><small>Drag and drop file here or click to upload.
                    (You can only upload .doc, .docx, .pdf, .jpg, .ppt or .pptx formats. Total file size should not
                    exceed 25MB)
                </small>
            </label>
            <?php
            $existing_files = array();

            if (get_post_meta($cmp_id, 'mm365_company_docs', true) != ''):
                echo '<div class="filecard" style="display:none">';
                foreach (get_post_meta($cmp_id, 'mm365_company_docs', true) as $attachment_id => $attachment_url) {
                    echo '<input type="checkbox" name="existing_files[]" id="file_to_delete_' . $attachment_id . '" value="' . $attachment_id . '" checked>';
                    $path = str_replace(site_url('/'), ABSPATH, esc_url($attachment_url));
                    $existing_files[] = array(
                        "size" => filesize($path),
                        "name" => basename(get_attached_file($attachment_id)),
                        "id" => $attachment_id,
                        "path" => $attachment_url
                    );
                }

                echo "</div> <br/>";

            endif;
            ?>
        </div>

        <div class=" dropzonee" id="my-dropzone"
            data-existing="<?php echo htmlspecialchars(json_encode($existing_files), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="dz-message needsclick" for="files">Drag and drop file here or click to upload.<br />
                <small>(You can only upload .doc, .docx, .pdf, .jpg, .ppt or .pptx formats. Total file size should
                    not exceed 25MB)</small>
                <div class="fallback">
                    <input class="form-control-file" type="file" id="wp_custom_attachment" name="files" multiple />
                </div>
            </div>
        </div>
        <ul class="parsley-errors-list filled" id="validate-capability-statement" aria-hidden="false">
            <li class="parsley-required capability-statemets-error">This value is required.</li>
        </ul>
    </div>
</div>



<div class="form-row form-group">



    <div class="col-lg-7">
        <label for="">Current customers
            <br /><small>Please enter only one customer name per row</small>
        </label>

        <section class="main-customers">
            <div class="form-row form-group">
                <div class="col-10">
                    <input placeholder="Please enter only one customer name per row" class="form-control" type="text"
                        name="main_customers[]">
                </div>
                <div class="col-2 d-flex  align-items-end main-customers-btn ">
                    <a href="#" class="add-main-customer plus-btn">+</a>
                </div>
            </div>
        </section>

        <section class="main-customers-dynamic">
            <!-- Show saved values -->
            <?php
            $customers = get_post_meta($cmp_id, 'mm365_main_customers', true);
            if(!empty($customers)){
            foreach (json_decode($customers) as $key => $value) { ?>
                <section class="maincustomer_remove">
                    <div class="form-row form-group">
                        <div class="col-10"> <input class="form-control" type="text" name="main_customers[]"
                                value="<?php echo $value; ?>"> </div>
                        <div class="col-2 d-flex  main-customers-btn align-items-end"><a href="#"
                                class="remove-main-customer plus-btn">-</a></div>
                    </div>
                </section>
            <?php }
            } ?>
        </section>

    </div>

</div>

<div class="form-row form-group">
    <div class="col-lg-4">
        <label for="">Size of company <small>(Annual sales in US Dollars)</small></label>
        <?php

        $current_size_of_company = get_post_meta($cmp_id, 'mm365_size_of_company', true);

        $size = get_post_meta($cmp_id, 'mm365_size_of_company', true);

        if ($size == '&lt;$100,000') {
            $current_size_of_company = "<$100,000";
        } else {
            $current_size_of_company = $size;
        }

        ?>
        <select name="size_of_company" id="" class="form-control mm365-single">
            <option value="">-Select-</option>
            <?php
           
              $size_of_company = array(
                "Less than $100,000",               
                "$100,000 - $500,000",
                "$500,000 - $1,000,000",
                "$1M- $5M",
                "$5M-$10M",
                "$10M-$25M",
               "$25M-$50M",
                "Greater than $50,000,000",
              );
            foreach ($size_of_company as $key) {
                if ($current_size_of_company == $key) {
                    echo "<option selected>" . $key . "</option>";
                } else {
                    echo "<option>" . $key . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4">
        <label for="">Number of employees</label>
        <?php
        $current_number_of_employees = get_post_meta($cmp_id, 'mm365_number_of_employees', true);
        $employee_count = get_post_meta($cmp_id, 'mm365_number_of_employees', true);
        if ($employee_count == '&lt; 20') {
            $current_number_of_employees = "< 20";
        } else {
            $current_number_of_employees = $employee_count;
        }
        ?>
        <select name="number_of_employees" id="" class="form-control  mm365-single">
            <option value="">-Select-</option>
            <?php
            $number_of_employees = array("< 20", "20 to 50", "50 to 100", "100 to 200", "200 to 500", "500 to 1000", "1000+");
            foreach ($number_of_employees as $key) {
                if ($current_number_of_employees == $key) {
                    echo "<option selected>" . $key . "</option>";
                } else {
                    echo "<option>" . $key . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4">
        <label for="">Industry Certifications</label>
        <select name="certifications[]" id="certifications" class="form-control mm365-multicheck" multiple="multiple">

            <?php
            $current_certifications = get_post_meta($cmp_id, 'mm365_certifications');
            apply_filters('mm365_dropdown_certifications', $current_certifications);
            ?>

            <option value="other" <?php if (in_array("other", $current_certifications)) {
                echo "selected";
            } ?>
                id="other_certification">Other</option>
        </select>
        <input type="text" placeholder="Others (Separate using commas)" class="form-control"
            id="other_certification_input" value="<?php if (in_array("other", $current_certifications)):
                $other_pos = (array_search('other', $current_certifications));
                echo implode(",", array_slice($current_certifications, $other_pos + 1));
            endif; ?>"
            name="other_certification">
    </div>

</div>

