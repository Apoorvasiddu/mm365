<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$_classes = array(
	'd4p-wrap',
	'wpv-' . GDMAQ_WPV,
	'd4p-page-' . gdmaq_admin()->page,
	'd4p-panel',
	'd4p-panel-' . $_panel,
);

$_tabs = array(
	'whatsnew'  => __( 'What&#8217;s New', 'gd-mail-queue' ),
	'info'      => __( 'Info', 'gd-mail-queue' ),
	'changelog' => __( 'Changelog', 'gd-mail-queue' ),
	'dev4press' => __( 'Dev4Press', 'gd-mail-queue' ),
);

?>

<div class="<?php echo esc_attr( join( ' ', $_classes ) ); ?>">
    <h1><?php printf( __( 'Welcome to GD Mail Queue&nbsp;%s', 'gd-mail-queue' ), gdmaq_settings()->info_version ); ?></h1>
    <p class="d4p-about-text">
		<?php esc_html_e( 'Configure various security related HTTP headers, including Content Security Policy, Referrer Policy and more. All headers can be added to .HTACCESS file.', 'gd-mail-queue' ); ?>
    </p>
    <div class="d4p-about-badge" style="background-color: #773355;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
            <path fill="#FFFFFF" d="M580.25,377L580.25,463C580.25,486.732 560.982,506 537.25,506L185.25,506C161.518,506 142.25,486.732 142.25,463L142.25,377C142.25,353.268 161.518,334 185.25,334L537.25,334C560.982,334 580.25,353.268 580.25,377ZM558.917,377C558.917,365.042 549.208,355.333 537.25,355.333L185.25,355.333C173.292,355.333 163.583,365.042 163.583,377L163.583,463C163.583,474.958 173.292,484.667 185.25,484.667L537.25,484.667C549.208,484.667 558.917,474.958 558.917,463L558.917,377ZM336.25,400C347.288,400 356.25,408.962 356.25,420C356.25,431.038 347.288,440 336.25,440C325.212,440 316.25,431.038 316.25,420C316.25,408.962 325.212,400 336.25,400ZM474.25,430.667L385.25,430.667L385.25,409.333L474.25,409.333L474.25,388L538.25,420L474.25,452L474.25,430.667ZM206.25,400C217.288,400 226.25,408.962 226.25,420C226.25,431.038 217.288,440 206.25,440C195.212,440 186.25,431.038 186.25,420C186.25,408.962 195.212,400 206.25,400ZM271.25,400C282.288,400 291.25,408.962 291.25,420C291.25,431.038 282.288,440 271.25,440C260.212,440 251.25,431.038 251.25,420C251.25,408.962 260.212,400 271.25,400ZM123.255,462L59.75,462L59.75,137C59.75,137 214.083,6 291.25,6C368.417,6 522.75,137 522.75,137L522.75,312.27C521.271,312.091 519.77,312 518.25,312L501.417,312L501.417,147.021C480.923,130.257 421.866,83.474 365.205,53.098C338.011,38.52 311.696,27.333 291.25,27.333C270.804,27.333 244.489,38.52 217.295,53.098C160.634,83.474 101.577,130.257 81.083,147.021L81.083,440.667L123.25,440.667L123.25,461.25C123.25,461.5 123.252,461.75 123.255,462ZM109.09,192.708L100.382,186.548L112.702,169.132L121.41,175.292C121.41,175.292 163.04,204.74 208.834,228.299C238.127,243.369 269.058,256.333 291.25,256.333C313.442,256.333 344.373,243.369 373.666,228.299C419.46,204.74 461.09,175.292 461.09,175.292L469.798,169.132L482.118,186.548L473.41,192.708C473.41,192.708 414.985,234.017 359.489,258.811C334.515,269.969 310.028,277.667 291.25,277.667C272.472,277.667 247.985,269.969 223.011,258.811C167.515,234.017 109.09,192.708 109.09,192.708Z"/>
        </svg>
		<?php printf( __( 'Version %s', 'gd-mail-queue' ), gdmaq_settings()->info_version ); ?>
    </div>

    <h2 class="nav-tab-wrapper wp-clearfix">
		<?php

		foreach ( $_tabs as $_tab => $_label ) {
			echo '<a href="admin.php?page=gd-mail-queue-about&panel=' . $_tab . '" class="nav-tab' . ( $_tab == $_panel ? ' nav-tab-active' : '' ) . '">' . $_label . '</a>';
		}

		?>
    </h2>

    <div class="d4p-about-inner">