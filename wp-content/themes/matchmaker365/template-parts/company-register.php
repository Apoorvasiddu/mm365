<?php

$user = wp_get_current_user();
?>

<div class="form-group row">
    <div class="col-lg-7" data-intro="Please enter your company name">
        <label for="">Company name<span>*</span></label>
        <input placeholder="Please enter your company name" class="form-control" type="text" name="company_name"
            minlength="4" required>
    </div>
    <div class="col-lg-2"
        data-intro="You can register your compnay as Buyer or Supplier. Buyers will not be considered in match requests">
        <label for="">Service Type<span>*</span></label><br />
        <input type="radio" name="service_type" value="buyer"> Buyer &nbsp;<input type="radio" name="service_type"
            value="seller" checked> Supplier
    </div>
    <div class="col-lg-3" data-intro="Council which your company is associated with">
        <label for="">Council<span>*</span></label>
        <?php
        $current_council = apply_filters('mm365_helper_get_usercouncil', $user->ID);
        ?>
        <select name="company_council" id="company_council" required class="form-control">
            <?php
            apply_filters('mm365_dropdown_councils', $current_council);
            ?>
        </select>
    </div>
</div>
<div class="form-row form-group">
    <div class="col-lg-12"
        data-intro="Please include a complete description of your company as well as all relevant keywords about your products and services to be listed in match making results">
        <label for="">
            <div id="company_desc_title">Description of services or products offered<span>*</span></div>
            <small>Please include a complete description of your company as well as all relevant keywords about your
                products and services to be listed in match making results</small>
        </label>
        <textarea id="company_description" name="company_description"
            data-parsley-errors-container=".descError"></textarea>
        <div class="descError"></div>
    </div>
</div>
<div class="col-12 d-block d-sm-none pbo-30"></div>
<div class="form-row form-group">
    <!-- NAICS Codes -->
    <div class="col-lg-4"
        data-intro="The North American Industry Classification System (NAICS). If you are not sure about the NAICS codes, find them from the link below">
        <label for="">NAICS code<span>*</span></small>
        </label>
        <section class="naics-codes">
            <div class="form-row  form-group">
                <div class="col">
                    <input placeholder="E.g. 123456" class="form-control" type="number" min="10" max="999999"
                        name="naics_codes[]" required>
                </div>
                <div class="col-2 d-flex align-items-start naics-codes-btn"><a href="#"
                        class="add-naics-code plus-btn">+</a></div>
            </div>
        </section>
        <section class="naics-codes-dynamic"></section>
        <label for=""><small>Please enter only one NAICS code per row</small>
        </label>
        
        <a class="external_link" target="_blank" href="https://www.naics.com/search/"><span>Search for NAICS code</span>
            &nbsp;<img src="<?php echo get_template_directory_uri() ?>/assets/images/share.svg" alt=""></a>
    </div>


    <div class="col-lg-4" data-intro="Name of the person to contact for the business needs">
        <label for="">Contact person<span>*</span></label>
        <input placeholder="Please enter your full name" class="form-control" pattern="[a-zA-Z\s]+" minlength="4"
            type="text" required name="contact_person">
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4" data-intro="Company's address. If there are multiple addresses add the primary address here">
        <label for="">Company address<span>*</span></label>
        <textarea placeholder="Please enter your address" required class="form-control" name="company_address" id=""
            cols="30" rows="1"></textarea>
    </div>
</div>
<div class="form-row form-group" data-intro="Company's address location">
    <div class="col-lg-3">
        <label for="">Country<span>*</span></label>
        <select required name="company_country" id="" class="country form-control mm365-single"
            data-parsley-errors-container=".countryError">
            <option value="">-Select-</option>
            <?php

            $country_list = apply_filters('mm365_helper_countries_list', 10);
            foreach ($country_list as $key => $value) {
                //Preselct USA 
                if ($value->id == '233') {
                    $default_country = "selected";
                } else {
                    $default_country = '';
                }

                echo "<option " . $default_country . " value='" . $value->id . "' >" . $value->name . "</option>";

            }
            ?>
        </select>
        <div class="countryError"></div>
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-3">
        <label for="">State<span>*</span></label>
        <?php
        $states_list = apply_filters('mm365_helper_states_list', 233, NULL);
        ?> <select required name="company_state" id=""
            class="state form-control mm365-single" data-parsley-errors-container=".stateError">
            <option value="">-Select-</option>
            <?php

            foreach ($states_list as $key => $value) {
                echo "<option  value='" . $value->id . "' >" . $value->name . "</option>";
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
            <option value="">-Select-</option>
        </select>
        <div class="cityError"></div>
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-3">
        <label for="">ZIP<span>*</span></label>
        <input class="form-control" type="text" placeholder="Please enter ZIP code" required
            data-parsley-required-message="Please enter a valid zip code." name="zip_code" pattern="^[A-Z0-9 -]+$"
            data-parsley-length="[4, 15]" name="zip_code" value=""
            data-parsley-length-message="The ZIP code should be 4 to 15 digits long">
    </div>
</div>

<div class="form-row form-group" data-intro="Primary phone number, email and website">

    <div class="col-lg-2">
        <label for="">Phone Type<span>*</span></label><br />
        <select required name="primary_phone_type" id="" class=" form-control mm365-single"
            data-parsley-errors-container=".primaryPhoneNumberTypeError">
            <option value="">-Select-</option>
            <option value="mobile"> Mobile </option>
            <option value="landphone">Land Phone</option>
        </select>
        <div class="primaryPhoneNumberTypeError"></div>
    </div>

    <div class="col-lg-3">
        <label for="">Phone Number<span>*</span></label>
        <input placeholder="E.g. 555 555 5555" class="form-control" type="text" required name="phone"
            pattern="[0-9+()\s]+" data-parsley-length="[6, 15]" name="phone"
            data-parsley-length-message="The phone number should be 6 to 15 digits long">
    </div>

    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-3">
        <label for="">Email<span>*</span></label>
        <input class="form-control" placeholder="Please enter a valid email" type="email" required name="company_email"
            data-parsley-type-message="This value should be a valid email ID." value="<?php echo $user->user_email; ?>">
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4">
        <label for="">Company website</label>
        <input placeholder="E.g. www.example.com" class="form-control" type="text" name="website"
            data-parsley-type='url' data-parsley-type-message="This value seems to be invalid.">
    </div>
</div>

<div class="form-row form-group" data-intro="Alternate person to contact and his/her email and phone number">
    <div class="col-lg-4">
        <label for="">Alternate contact person</label>
        <input placeholder="Please enter the full name" class="form-control" type="text" type="text"
            pattern="[a-zA-Z\s]+" minlength="4" name="alt_contact_person">
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4">
        <label for="">Alternate phone</label>
        <input placeholder="E.g. 333 333 3333" class="form-control" type="text" pattern="[0-9+()\s]+"
            data-parsley-length="[6, 15]" name="alt_phone"
            data-parsley-length-message="The phone number should be 6 to 15 digits long">
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4">
        <label for="">Alternate email</label>
        <input placeholder="Please enter an alternative email" class="form-control" type="email" name="alt_email"
            data-parsley-type='email' data-parsley-type-message="This value should be a valid email ID.">
    </div>
</div>

<div class="form-row form-group">
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4" data-intro="Industries your company associated with">
        <label for="">Industry<span>*</span></label>
        <select required data-parsley-errors-container=".industryError" name="industry[]" id="industry"
            data-parsley-required-message="Must select at least one" class="form-control mm365-multicheck"
            multiple="multiple">
            <?php
            apply_filters('mm365_dropdown_industries', array());
            ?>
            <option value="other" id="other_industry">Other</option>
        </select>
        <div class="industryError"></div>
        <input type="text" class="form-control" id="other_industry_input" name="other_industry"
            placeholder="Others (Separate using commas)">
    </div>
    <div class="col-lg-4" data-intro="Services or products which your company provides">
        <label for="">Company services<span>*</span></label>
        <select name="services[]" id="services" required data-parsley-errors-container=".servError" multiple="multiple"
            data-parsley-required-message="Must select at least one" class="form-control mm365-multicheck"
            data-placeholder="Select all that applies">
            <?php
            apply_filters('mm365_dropdown_services', array());
            ?>
            <option value="other" id="other_services">Other</option>
        </select>
        <div class="servError"></div>
        <input type="text" class="form-control" id="other_services_input" name="other_services"
            placeholder="Others (Separate using commas)">
    </div>
</div>

<div id="minority_category_block" class="">
    <div class="form-row">
        <div class="col-12">
            <label for="">Locations where the services or products are available</label>
        </div>
    </div>
    <div class="form-row form-group">
        <!-- v1.6 on wards | Servicable location ends-->
        <div class="col-lg-3" data-intro="Countries where your company is providing the services or products.">
            <label for="">Countries<span>*</span></label>
            <select required name="serviceable_countries[]" id="serviceable-countries"
                class="serviceable-countries form-control mm365-multicheck" multiple
                data-parsley-errors-container=".srv-countryError">
                <option value="">-Select-</option>
                <?php
                apply_filters('mm365_dropdown_countries', ['233'])
                    ?>
            </select>
            <div class="srv-countryError"></div>
        </div>
        <div class="col-12 d-block d-sm-none pbo-30"></div>
        <div class="col-lg-3"
            data-intro="States where your company is providing the service or products. You can select all states or specific states of the selected country from the previous field">
            <label for="">States<span>*</span></label>
            <select required name="serviceable_states[]" id="serviceable-states"
                class="serviceable-states form-control mm365-multicheck" multiple
                data-parsley-errors-container=".srv-stateError">
                <option value="">-Select-</option>
                <?php
                apply_filters('mm365_dropdown_states', 233, array(), TRUE, FALSE);
                ?>
            </select>
            <div class="srv-stateError"></div>
        </div>
        <!-- Servicable location ends -->
        <div class="col-lg-3"
            data-intro="Minority classification. If you are not sure about the classification, please contact your council">
            <label for="">Minority classification<span>*</span></label>
            <select required data-parsley-errors-container=".minority_categoryError" name="minority_category"
                id="minority_category" class="form-control mm365-single">
                <option value="">-select-</option>
                <?php
                apply_filters('mm365_dropdown_minoritycategory', NULL);
                ?>
            </select>
            <div class="minority_categoryError"></div>
        </div>
        <div class="col-lg-3"
            data-intro="If you are looking for any kind of assistance from your council in establishing your business internationally">
            <label for="">International Assistance</label>
            <select name="international_assistance[]" id="international_assistance" multiple
                class="form-control mm365-multicheck">
                <option value="">-Select-</option>
                <?php
                apply_filters('mm365_dropdown_internationalassistance', array());
                ?>
            </select>
            <label><small>Please select multiple international assistances which you are interested in</small></label>
        </div>
        <div class="col-12 d-block d-sm-none pbo-30"></div>
    </div>
</div>
<div class="form-row form-group" id="minority_category_block_uploads">
    <div class="col"
        data-intro="Properly formatted capability statement. The file can be .doc, .docx, .pdf, .jpg, .ppt or .pptx formats. Total file size should not exceed 25MB">
        <label for="">Capability statement<span>*</span>
            <br /><small>Drag and drop file here or click to upload. (You can only upload .doc, .docx, .pdf, .jpg, .ppt
                or .pptx formats. Total file size should not exceed 25MB) </small>
        </label>
        <br />
        <div class="dropzonee" id="my-dropzone" data-existing="">
            <div class="dz-message needsclick" for="files">Drag and drop files here or click to upload.<br />
                <small>(You can only upload .doc, .docx, .pdf, .jpg, .ppt or .pptx formats. Total file size should not
                    exceed 25MB)</small>
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

    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-7" data-intro="Your current customers">
        <label for="">Current customers <br><small>Please enter only one customer name per row</small>
        </label>
        <section class="main-customers">
            <div class="form-row form-group">
                <div class="col-10">
                    <input placeholder="Please enter only one customer name per row" class="form-control" type="text"
                        name="main_customers[]">
                </div>
                <div class="col-2 d-flex align-items-end main-customers-btn ">
                    <a href="#" class="add-main-customer plus-btn">+</a>
                </div>
            </div>
        </section>
        <section class="main-customers-dynamic"></section>
    </div>
</div>
<div class="form-row form-group">
    <div class="col-lg-4" data-intro="Size of your company. Valuatation based on annual sales in US Dollars">
        <label for="">Size of company <small>(Annual sales in US Dollars)</small></label>
        <select name="size_of_company" id="" class="form-control  mm365-single">
            <option value="">-select-</option>
            <option>
                <$100,000 
            </option>
            <option>$100,000 - $500,000</option>
            <option>$500,000 - $1M</option>
            <option>$1M - $5M</option>
            <option>$5M - $50M</option>
            <option>$50M - $200M</option>
            <option>$200M - $500M</option>
            <option>$500M - $1B</option>
            <option>$1B+</option>
        </select>
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4" data-intro="Number of employees in your company">
        <label for="">Number of employees</label>
        <select name="number_of_employees" id="" class="form-control  mm365-single">
            <option value="">-select-</option>
            <option>
                < 20 </option>
            <option>20 to 50</option>
            <option>50 to 100</option>
            <option>100 to 200</option>
            <option>200 to 500</option>
            <option>500 to 1000</option>
            <option>1000+</option>
        </select>
    </div>
    <div class="col-12 d-block d-sm-none pbo-30"></div>
    <div class="col-lg-4"
        data-intro="Certifications awarded to your company. Select all that applies, if any of your certifications are not listed, please choose other option and fill the name of the certification in the input field appear below">
        <label for="">Industry Certifications</label>
        <select name="certifications[]" id="certifications" class="form-control  mm365-multicheck" multiple="multiple">
            <?php
            apply_filters('mm365_dropdown_certifications', array());
            ?>
            <option value="other" id="other_certification">Other</option>
        </select>
        <input type="text" class="form-control" id="other_certification_input" name="other_certification"
            placeholder="Others (Separate using commas)">
    </div>
</div>