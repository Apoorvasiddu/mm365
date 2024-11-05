<?php
namespace Mm365;


trait MatchrequestAddon
{

  /**
   * @param string $string 
   * Accepts string , removes all stop words for search
   * 
   */
    function keyword_cleanser($string){

        $stopwords_list='i|a|about|an|and|are|as|at|be|by|com|de|en|for|from|how|in|is|it|la|of|on|or|that|the|this|to|was|what|when|where|who|will|with|und|the|www|the|and|is|your|me|for|where|etc|are|The|we|looking|searching|should|could|but|need|look|find|companies|company|factory|factories|manufacturer|manufacturing|providers|manufacturers|products|product|industry|industries';
        $stopwords = explode("|",$stopwords_list);
        mb_internal_encoding('UTF-8');
        //$stopwords = array(); ["]
        $string = preg_replace('/[\pP]/u', '', trim(preg_replace('/\s\s+/iu', '', mb_strtolower($string))));
        $matchWords = array_filter(explode(' ',$string) , function ($item) use ($stopwords) { return !($item == '' || in_array($item, $stopwords) || mb_strlen($item) <= 2 || is_numeric($item));});
        $wordCountArr = array_count_values($matchWords);
        arsort($wordCountArr);
        $result =  array_keys(array_slice($wordCountArr, 0, 50));
        $commaList = implode(' ', $result);
        return $commaList;
    }

    /**
     * Show Match Status
     * @param int $mr_id
     * @param string $css_class
     */

     function matchrequest_show_status($mr_id, $css_class = 'mr-indicator'){

      $status   = get_post_meta($mr_id, 'mm365_matchrequest_status', true );

      switch ($status) {
          case 'nomatch':
              echo "<span data-position='left' data-intro='Current match status' class='".$css_class." ".$status."'>No Match</span>";
          break;
          case 'auto-approved':
              echo "<span data-position='left' data-intro='Current match status' class='".$css_class." ".$status."'>Auto Approved</span>";
          break;
          default:
              echo "<span data-position='left' data-intro='Current match status' class='".$css_class." ".$status."'>".ucfirst($status)."</span>";
          break;
      }
  }

}