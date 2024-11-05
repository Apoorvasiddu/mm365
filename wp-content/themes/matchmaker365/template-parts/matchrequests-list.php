
  <div class="row pbo-20 clearfloats">
          <div class="col-md-5">
               <h1 id="mr" class="heading-large pbo-10">My Match Requests</h1>
          </div>    
          <div class="col-md-7 d-flex align-items-center justify-content-end admin-dash-filter" data-intro="Filter and download the match requestes you have submitted in Excel format" data-step="2">
          <div class="select">
              <select name="slct" id="mm365_mrdownload_filter_select">
                  <option selected value="two_week">Last two weeks</option>
                  <option value="month">Last one month</option>
                  <option value="six_months">Last six months</option>
                  <option value="year">Last one year</option>
              </select>
          </div>
          &nbsp;&nbsp;
          <?php
            $user = wp_get_current_user();
            $url_week       = site_url().'/matchrequests-download?requester='.$user->ID."&period=two_week";
            $url_month      = site_url().'/matchrequests-download?requester='.$user->ID."&period=month";
            $url_six_months = site_url().'/matchrequests-download?requester='.$user->ID."&period=six_months";
            $url_year       = site_url().'/matchrequests-download?requester='.$user->ID."&period=year";
          ?>
            <a id="mrdownload_two_week" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'download_match_requests' ), $url_week ); ?>" class="btn btn-primary dash-report-btn">Download Match Requests</a>
            <a id="mrdownload_month" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'download_match_requests' ), $url_month ); ?>" class="btn btn-primary dash-report-btn">Download Match Requests</a>
            <a id="mrdownload_six_months" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'download_match_requests' ), $url_six_months ); ?>" class="btn btn-primary dash-report-btn">Download Match Requests</a>
            <a id="mrdownload_year" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'download_match_requests' ), $url_year ); ?>" class="btn btn-primary dash-report-btn">Download Match Requests</a>
          </div>
  </div>


        <!-- List Existing Match Requests class="matchrequests-list table table-striped " -->
        <table id="matchlist" class="matchrequests-list table table-striped " cellspacing="0" width="100%" data-intro="The match requests you have submitted so far.">
          <thead class="thead-dark">
            <tr>
              <th width="40%"><h6>Details of services or products you are looking for	</h6></th>
              <th><h6>Requested date & time</h6></th>
              <th><h6>Location</h6></th>
              <th><h6>Status</h6></th>
              <th class="no-sort"><h6></h6></th>
              <th class="no-sort"><h6></h6></th>
              
            </tr>
          </thead>

        </table>

