<?php
/**
 * Template Name: Fix data - Match request
 *
 */

//add_council_to_mmsdc_companies();
//add_council_to_mmsdc_matchrequests();
//add_council_to_meetings(); 
//add_council_to_certificates();
//add_council_to_business_users();
//fix_matchrequests_resultarrays();
//mmdatafix_companynames_for_search();
//mmdatafix_companynames_in_certificate();
//mmdatafix_company_serviceable_location();
//mmdatafix_amp_in_name_fix();
//mmdatafix_update_certification_data_to_company();
//mmdatafix_change_mexican_companies_to_global_initiative_council();
//mm365datafixer_company_description_update();
//wp_redirect( home_url() ); exit;
//mm365datafixer_remove_duplicate_companies();
//mm365_2017_naics_codes_table();

//mm365_minority_size_of_company_update_codes();
//mm365datafixer_remove_duplicate_companies();


$mm365_emails_optionsObj = get_option('mm365_options');

function mm365_missing_naics_code_template(){
    $mm365_emails_optionsObj = get_option('mm365_options');
$content = "Your Matchmaker365 profile is missing your company's NAICS code(s). Our search algorithm prioritizes match requests based on NAICS codes followed by keywords. Updating your profile with your relevant NAICS code(s) will enhance your visibility and ranking in match request results.<br/> 
    Please log in to your profile and complete this update using the link below:
";
$button_link ="www.matchmaker365.org";
$button_label ="Login";
$subject ="  Matchmaker365 Profile is Incomplete – NAICS Code(s) Needed";

echo $output='<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title></title>
<!--[if !mso]><!-->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style type="text/css">
#outlook a { padding:0; }
body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
p { display:block;margin:13px 0; }
</style>
<!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:AllowPNG/>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<![endif]-->
<!--[if lte mso 11]>
<style type="text/css">
.mj-outlook-group-fix { width:100% !important; }
</style>
<![endif]-->
<!--[if !mso]><!-->
<link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
<style type="text/css">
@import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
</style>
<!--<![endif]-->
<style type="text/css">
@media only screen and (min-width:480px) {
.mj-column-per-100 { width:100% !important; max-width: 100%; }
}
</style>
<style media="screen and (min-width:480px)">
.moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }
</style>
<style type="text/css">
@media only screen and (max-width:479px) {
table.mj-full-width-mobile { width: 100% !important; }
td.mj-full-width-mobile { width: auto !important; }
}
</style>
</head>
<body style="word-spacing:normal;">
<div style="">
<!--[if mso | IE]>
<table align="left" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr>
<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->
<div  style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:1920px;">
<table align="left" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#0078d4;background-color:#0078d4;width:100%;">
<tbody> <tr> <td style="direction:ltr;font-size:0px;padding:0px;text-align:left;">
<!--[if mso | IE]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="" style="vertical-align:top;width:600px;" >
<![endif]-->
<div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
<tbody><tr><td  style="vertical-align:top;padding:0px;padding-top:10px;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">
<tbody><tr><td style="font-size:0px;padding:0px;padding-top:10px;word-break:break-word;padding-left: 14px;padding-bottom: 10px;" align="left">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
<tbody> <tr><td  style="width:280px;">
 <img src="'.$mm365_emails_optionsObj['email_header_logo_image'].'" alt="MMSDC Logo" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="225" height="auto"></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></div>
<!--[if mso | IE]>
</td></tr></table>
<![endif]-->
</td></tr></tbody></table></div>
<!--[if mso | IE]>
</td></tr></table>
<table align="left" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff"><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->
<div  style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:850px;">
<table align="left" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:0px;padding-bottom:20px;padding-top:10px;text-align:left;">
<!--[if mso | IE]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" >
<![endif]-->
<div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td  style="vertical-align:top;padding:0px;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%"><tbody>
<tr><td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
<img src="'.$mm365_emails_optionsObj['email_mmsdc_logo_image'].'" alt="MMSDC Logo" width="350px" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="150" >
</td>
</tr>
<tr> <td align="left" style="font-size:0px;padding:0 25px;word-break:break-word;">
<div style="font-family:Arial;font-size:24px;line-height:1;text-align:left;color:#000000;font-weight: 600;">
<p>Dear Matchmaker365 Supplier,<br></p> </div> </td> </tr><tr><td align="left" style="font-size:0px;padding:0 25px;word-break:break-word;">
<div style="font-family:Arial;font-size:14px;line-height:25px;text-align:left;color:#000000;">
<p>'.$content.'<br></p></div></td></tr><tr><td align="left" vertical-align="middle" style="font-size:0px;padding:10px 0 0 23px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;">
<tbody><tr><td align="left" bgcolor="#2395ec" role="presentation" style="border:none;border-radius:3px;cursor:auto;mso-padding-alt:10px 25px;background:#2395ec;" valign="middle">
<a href="'.$button_link.'" style="display:inline-block;background:#2395ec;color:white;font-family:Arial, sans-serif;font-size:16px;font-weight:bold;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px;mso-padding-alt:0px;border-radius:3px;" target="_blank">'.$button_label.'</a>
</td></tr></tbody></table></td></tr>

<tr><td align="left" style="font-size:0px;padding:0 25px;padding-top:40px;word-break:break-word;">
<div style="font-family:Arial, sans-serif;font-size:17px;line-height:25px;text-align:left;color:#000000;"
>If you are unsure of your company’s NAICS code(s), you can find more information at the following site:
https://www.naics.com/</div></td></tr>

<tr><td align="left" style="font-size:0px;padding:0 25px;padding-top:40px;word-break:break-word;">
<div style="font-family:Arial, sans-serif;font-size:17px;line-height:25px;text-align:left;color:#000000;"
>Thank you for your prompt attention to this matter.</div></td></tr>

<tr><td
align="left" style="font-size:0px;padding:0 25px;padding-top:40px;word-break:break-word;">
<div style="font-family:Arial, sans-serif;font-size:17px;line-height:25px;text-align:left;color:#000000;"
><strong>Best regards,<br/>Matchmaker365 Team. </strong></div></td></tr>

</tbody></table></td></tr></tbody></table></div>
<!--[if mso | IE]>  </td> </tr> </table> <![endif]--> </td></tr></tbody></table></div> <!--[if mso | IE]> </td></tr></table> 
<![endif]-->  </div></body></html>';

return $output;
}


//NAICS CODE HAS BEEN UPDATED:

function mm365_update_naics_code_template($title,$content,$button_link,$button_label){
$content = "
It was identified that your Matchmaker365 profile was incomplete due to missing NAICS codes. Based on your company description, we have populated relevant NAICS codes on your behalf. Our search algorithm prioritizes match requests based on NAICS codes followed by keywords. Updating your profile with your relevant NAICS code(s) will enhance your visibility and ranking in match request results. <br/> Please log in to your profile and review this update using the link below:
";
$button_link ="www.matchmaker365.org";
$button_label ="Login";
$subject =" Matchmaker365 Profile – NAICS Code Confirmation";
$output='<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title></title>
<!--[if !mso]><!-->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style type="text/css">
#outlook a { padding:0; }
body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
p { display:block;margin:13px 0; }
</style>
<!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:AllowPNG/>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<![endif]-->
<!--[if lte mso 11]>
<style type="text/css">
.mj-outlook-group-fix { width:100% !important; }
</style>
<![endif]-->
<!--[if !mso]><!-->
<link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
<style type="text/css">
@import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
</style>
<!--<![endif]-->
<style type="text/css">
@media only screen and (min-width:480px) {
.mj-column-per-100 { width:100% !important; max-width: 100%; }
}
</style>
<style media="screen and (min-width:480px)">
.moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }
</style>
<style type="text/css">
@media only screen and (max-width:479px) {
table.mj-full-width-mobile { width: 100% !important; }
td.mj-full-width-mobile { width: auto !important; }
}
</style>
</head>
<body style="word-spacing:normal;">
<div style="">
<!--[if mso | IE]>
<table align="left" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr>
<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->
<div  style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:1920px;">
<table align="left" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#0078d4;background-color:#0078d4;width:100%;">
<tbody> <tr> <td style="direction:ltr;font-size:0px;padding:0px;text-align:left;">
<!--[if mso | IE]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="" style="vertical-align:top;width:600px;" >
<![endif]-->
<div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
<tbody><tr><td  style="vertical-align:top;padding:0px;padding-top:10px;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">
<tbody><tr><td style="font-size:0px;padding:0px;padding-top:10px;word-break:break-word;padding-left: 14px;padding-bottom: 10px;" align="left">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
<tbody> <tr><td  style="width:280px;">
 <img src="'.$mm365_emails_optionsObj['email_header_logo_image'].'" alt="MMSDC Logo" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="225" height="auto"></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></div>
<!--[if mso | IE]>
</td></tr></table>
<![endif]-->
</td></tr></tbody></table></div>
<!--[if mso | IE]>
</td></tr></table>
<table align="left" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff"><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->
<div  style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:850px;">
<table align="left" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;"><tbody><tr><td style="direction:ltr;font-size:0px;padding:0px;padding-bottom:20px;padding-top:10px;text-align:left;">
<!--[if mso | IE]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" >
<![endif]-->
<div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"><tbody><tr><td  style="vertical-align:top;padding:0px;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%"><tbody>
<tr><td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
<img src="'.$mm365_emails_optionsObj['email_mmsdc_logo_image'].'" alt="MMSDC Logo" width="350px" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="150" >
</td>
</tr>
<tr> <td align="left" style="font-size:0px;padding:0 25px;word-break:break-word;">
<div style="font-family:Arial;font-size:24px;line-height:1;text-align:left;color:#000000;font-weight: 600;">
<p>Dear Matchmaker365 Supplier,<br></p> </div> </td> </tr><tr><td align="left" style="font-size:0px;padding:0 25px;word-break:break-word;">
<div style="font-family:Arial;font-size:14px;line-height:25px;text-align:left;color:#000000;">
<p>'.$content.'<br></p></div></td></tr><tr><td align="left" vertical-align="middle" style="font-size:0px;padding:10px 0 0 23px;word-break:break-word;"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;">
<tbody><tr><td align="left" bgcolor="#2395ec" role="presentation" style="border:none;border-radius:3px;cursor:auto;mso-padding-alt:10px 25px;background:#2395ec;" valign="middle">
<a href="'.$button_link.'" style="display:inline-block;background:#2395ec;color:white;font-family:Arial, sans-serif;font-size:16px;font-weight:bold;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px;mso-padding-alt:0px;border-radius:3px;" target="_blank">'.$button_label.'</a>
</td></tr></tbody></table></td></tr>

<tr><td align="left" style="font-size:0px;padding:0 25px;padding-top:40px;word-break:break-word;">
<div style="font-family:Arial, sans-serif;font-size:17px;line-height:25px;text-align:left;color:#000000;"
>If you are unsure of your company’s NAICS code(s), you can find more information at the following site:
https://www.naics.com/</div></td></tr>

<tr><td align="left" style="font-size:0px;padding:0 25px;padding-top:40px;word-break:break-word;">
<div style="font-family:Arial, sans-serif;font-size:17px;line-height:25px;text-align:left;color:#000000;"
>Thank you for your prompt attention to this matter.</div></td></tr>

<tr><td
align="left" style="font-size:0px;padding:0 25px;padding-top:40px;word-break:break-word;">
<div style="font-family:Arial, sans-serif;font-size:17px;line-height:25px;text-align:left;color:#000000;"
><strong>Best regards,<br/>Matchmaker365 Team. </strong></div></td></tr>

</tbody></table></td></tr></tbody></table></div>
<!--[if mso | IE]>  </td> </tr> </table> <![endif]--> </td></tr></tbody></table></div> <!--[if mso | IE]> </td></tr></table> 
<![endif]-->  </div></body></html>';

return $output;
}


mm365_missing_naics_code_template();
        
