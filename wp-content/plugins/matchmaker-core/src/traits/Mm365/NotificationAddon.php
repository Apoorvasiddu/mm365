<?php
namespace Mm365;

/**
 * All the supporting functions for companies
 * 
 * 
 */

trait NotificationAddon
{


    function mm365_email_body($title,$content,$button_link,$button_label){

        $output = '
        </html>
        <html xmlns="http://www.w3.org/1999/xhtml">
           <head>
              <meta http-equiv="content-type" content="text/html; charset=utf-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0;">
              <meta name="format-detection" content="telephone=no"/>
              <style>
                 @import url("https://use.typekit.net/eix8uwn.css");
                 /* Reset styles */ 
                 body { margin: 0; padding: 0; min-width: 100%; width: 100% !important; height: 100% !important;}
                 body, table, td, div, p, a { -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%; }
                 table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important; border-spacing: 0; }
                 img { border: 0; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
                 #outlook a { padding: 0; }
                 /* Rounded corners for advanced mail clients only */ 
                 @media all and (min-width: 600px) {
                 .container { border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -khtml-border-radius: 8px;}
                 }
                 /* Set color for links */ 
                 a, a:hover {
                 color: #006aff;
                 }
              </style>
              <!-- MESSAGE SUBJECT -->
              <title>Matchmaker365</title>
           </head>
           <!-- BODY -->
           <body topmargin="0" rightmargin="0" bottommargin="0" leftmargin="0" marginwidth="0" marginheight="0" width="100%" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
              background-color: #f5f5f5;
              color: #202022;"
              bgcolor="#f5f5f5"
              text="#202022">
              <!-- BACKGROUND -->
              <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%;">
                 <tr>
                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;"
                       bgcolor="#F5F5F5">
                       <!-- LOGO -->
                       <table border="0" cellpadding="0" cellspacing="0" align="center"
                          width="700" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
                          max-width: 700px;">
                          <tr>
                             <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 24px 6.25%; width: 87.5%;">
                                <a target="_blank" style="text-decoration: none;"
                                   href="#"><img border="0" vspace="0" hspace="0"
                                   src="'.site_url().'/wp-content/themes/matchmaker365/assets/images/mmsdc_logo.png"
                                   width="auto" height="48"
                                   alt="Logo" title="Logo" style="
                                   color: #202022;
                                   font-size: 10px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;" /></a>
                             </td>
                          </tr>
                       </table>
                       <!-- CONTAINER -->
                       <table border="0" cellpadding="0" cellspacing="0" align="center"
                          bgcolor="#FFFFFF"
                          width="700" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
                          max-width: 700px;" class="container">
                          <!-- HEADER -->
                          <tr>
                             <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 24px 6.25%; width: 87.5%; font-size: 28px; font-weight: bold; line-height: 130%;
                                color: #202022;
                                font-family: Effra, sans-serif;">
                                '.$title.'
                             </td>
                          </tr>
                          <!-- BODY COPY -->
                          <tr>
                             <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0 6.25% 4px; width: 87.5%; font-size: 16px; font-weight: 400; line-height: 160%; 
                                color: #000000;
                                font-family: Effra, sans-serif;">
                                <p style="margin: 0 0 20px; font-size: 16px; font-weight: 400; line-height: 140%; 
                                   color: #202022;
                                   font-family: Effra, sans-serif;">
                                   '.$content.'
                                </p>
                             </td>
                          </tr>
                          <tr>
                             <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0 6.25% 24px; width: 87.5%;"></td>
                          </tr>   
                          <!-- BUTTON ROW -->
                          <tr>
                             <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0 6.25% 24px; width: 87.5%;">
                                <!-- BUTTON -->
                                <a href="#" target="_blank" style="text-decoration: none;">
                                   <table border="0" cellpadding="0" cellspacing="0" align="left" style="min-width: 120px; border-collapse: collapse; border-spacing: 0; padding: 0; margin: 0 8px 8px 0;">
                                      <tr>
                                         <td align="center" valign="middle" style="padding: 12px 24px; margin: 0; text-decoration: none; border-collapse: collapse; border-spacing: 0; border-radius: 25px; -webkit-border-radius: 25px; -moz-border-radius: 25px; -khtml-border-radius: 25px;"
                                            bgcolor="#006aff">
                                            <p style="padding: 0px; margin:0px;">
                                            <a target="_blank" style="text-decoration: none;
                                              color: #FFFFFF; font-family:Effra, sans-serif; font-size: 17px; font-weight: 500; line-height: 120%;"
                                              href="'.$button_link.'">'.$button_label.'</a>     
                                            </p>
                                </td>
                                </tr>  
                                </table>
                                </a>
                             </td>
                          </tr>
        
                          <tr>
                             <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0 6.25% 24px; width: 87.5%;">
                              <p style="margin: 0 0 20px; font-size: 16px; font-weight: 400; line-height: 140%; 
                              color: #202022;
                              font-family: Effra, sans-serif;">
                              Thanks and Regards,<br/>
                              Matchmaker365 Team
                              </p>
                             </td>
                          </tr>
        
        
                          <!-- LINE 
                          <tr>
                             <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0 6.25%; width: 87.5%;" class="line">
                                <hr
                                   color="#dfe2e6" align="center" width="100%" size="1" noshade style="margin: 0; padding: 0;" />
                             </td>
                          </tr>
                          <tr>
                             <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 24px 6.25% 28px; padding-right: 6.25%; width: 87.5%; font-size: 15px; font-weight: 400; line-height: 160%;
                                color:#202022;
                                font-family: Effra, sans-serif;">
                                Need Support? <a href="v2soft.com" target="_blank" style="color: #006aff; font-family: Effra, sans-serif; font-size: 15px; font-weight: 400; line-height: 160%;">V2Soft</a>
                             </td>
                          </tr>-->
                       </table>
                       <!-- FOOTER -->
                       <table border="0" cellpadding="0" cellspacing="0" align="center"
                          width="700" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
                          max-width: 700px;">
                          <tr>
                             <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 20px 6.25%; width: 87.5%; font-size: 13px; font-weight: 400; line-height: 150%;
                                color: #8e9399;
                                font-family: Effra, sans-serif;">
                               This is a system generated email. Please do not reply.
                             </td>
                          </tr>
                       </table>
                    </td>
                 </tr>
              </table>
           </body>
        </html>';
        return $output;
        
        
        
        }

}