<?php
ini_set('display_errors',1); 
 error_reporting(E_ALL);

 scraperwiki::save_var('dummy', 0);
/*
scraperwiki::sqliteexecute("CREATE TABLE IF NOT EXISTS turk_table 
(ID int,COTTAGE_URL string , TEXT string, DATE string, dateFrom string, dateTo  string, duration  string, originalPrice  string, discountedPrice  string,  percentage string,  discountAmount string, URL string, TYPE string)");
*/ 
$id = 0;


  // get the number of days in the month
 function getDaysinMonth($month){
    $monthArray = array(1=>"january", 2=>"feburary" ,3=>"march" ,4=>"april" ,5=>"may" ,6=>"june" ,7=>"july" ,8=>"august" ,9=>"september" ,10=>"october" ,11=>"novenber" ,12=>"december");
    foreach($monthArray as $key=>$value){
      if($value == strtolower($month))
         $monthNum = $key;
         continue;
    }
    return cal_days_in_month(CAL_GREGORIAN ,$monthNum ,date("Y"));
 }



  /*
  ** Check for offers that aren't wanted
  **
  */
  function isOffer($str){
    $invalidString = array("Please call","to book this offer","must be made by","over the phone","booked before","for remaining dates","REDUCTION ON SHORT BREAKS ","call 0","booked  before","book before");
    $validString = array("%","£","&pound;","£","&pound;");
    $ok = false;
    # Test that its an offer
    $count = 0;

    foreach($validString as $vStr){
      if(strpos(strtolower($str), strtolower($vStr))!=false){
        $count++;
        $ok = true;
      }
    }  
    # Test that there are no invalid strings within the offer

    if($ok && $count >=2){
      foreach($invalidString as $invStr){
        if(strpos(strtolower($str), strtolower($invStr)))
          return false;
      }
      return true;
    }
    echo "Not an Offer!";
    return false;
  }
    

    // parse text and extract useful data
    //$str - The Text
    //$start - the character to start scraping from
    //$end - the character to scrape to. Default = " " (space)
    function parseTextAround($str,$start,$end=" "){
       $returnArr = array();
       $strArr = explode($start,strtolower($str));
       $i = 0;
       if(count($strArr)>=2){
         foreach($strArr as $item){
           if(!$i % 2){           
             $parsedStr = substr($strArr[$i],-2,2);
             array_push($returnArr,$parsedStr);
             $i = $i+2;
           }
         }
         return $returnArr;
       }
    }
    


    /*
    ** getDiscountAmount($str)
    ** $str = the text to parse
    ** returns the discount in £
    */
    function getDiscountAmount($str){
      # split the text up by full stop for multiple deals
          $discount = "";
          // standardise the currency Symbol
          $str = str_replace("&pound;","£",$str);
          #$str = str_replace("£","",$str);
          // dont parse any records with percentages in it
          // as we can work out the discount ourselves
          if(strpos(strtolower($str),"%"))
            return;
          if(strpos(strtolower($str),"saving") &&  $discount=="")
              $discount = substr($str,strpos(strtolower($str),"saving"));
        
          if(strpos(strtolower($str),"off") &&  $discount=="" )
              $discount = substr($str,strpos($str,"£"),strpos(strtolower($str),"off"));
          if(strpos(strtolower($str),"discount of") &&  $discount=="")
              $discount = substr($str,strpos($str,strpos($str,"£"),"discount of ")+12,8);
          $discount =  preg_replace('/[^0-9.]+/', '',$discount);

      return   $discount;
    }
    
    /*
    ** getDiscountedPrice($str)
    ** $str = the text to parse
    ** returns the discounted price
    */
    function getDiscountedPrice($str){
      $i = 0;
      $amountAfterDiscount = "";
      
      $str = str_replace("&pound;","£",$str);
      $amount = "";

      if(strpos(strtolower($str),"just") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"just")+4,12);  
      if(strpos(strtolower($str),"from") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"from")+4,12);
      if(strpos(strtolower($str),"now") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"now")+3,12);
      if(strpos(strtolower($str),"only") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"only")+4,12);
      if(strpos(strtolower($str),"discounted price ") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"discounted price ")+17 ,6); 

      # if there is a decimal point, remove it and everything after it
      if(strpos($amountAfterDiscount,"."))
          $amountAfterDiscount = substr($amountAfterDiscount,0,strpos($amountAfterDiscount,"."));
      $amountAfterDiscount = preg_replace('/[^0-9.]+/', '', $amountAfterDiscount);    
      return $amountAfterDiscount;
    }
    
     /*
    ** getDiscountedPrice($str)
    ** $str = the text to parse
    ** returns the discounted price
    */
    function getOriginalPrice($str){
      $i = 0;
      $amountAfterDiscount = "";
      $str = str_replace("&pound;","£",$str);
      $amount = "";
      if(strpos(strtolower($str),"weeks ") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"weeks ")+6,3);  
      # if there is a decimal point, remove it and everything after it
      if(strpos($amountAfterDiscount,"."))
          $amountAfterDiscount = substr($amountAfterDiscount,0,strpos($amountAfterDiscount,"."));
      $amountAfterDiscount = preg_replace('/[^0-9.]+/', '', $amountAfterDiscount);      
      return $amountAfterDiscount;
    }
    
    
    /*
    ** calcOriginalPrice($percentageAmount,$discountPrice)
    ** $percentageAmount = The percentage the price has been reduced by
    ** $discountPrice = The price after the deduction
    ** returns the Original Price
    ** eg 325 -  (100 - 25) = 4.33
    ** 4.33 * 100 = 433
    */
    function calcOriginalPrice($discountPrice,$percentageAmount){
          return  round(($discountPrice / (100 - $percentageAmount)) * 100);
    }


    /*
    ** calcPercentage($discountAmount,$originalPrice)
    ** $discountAmount = the discount in £
    ** $originalPrice = the original price in £
    ** returns the discount as a percentage
    */
    function calcPercentage($discountAmount,$originalPrice){
          return round($discountAmount / $originalPrice * 100);
    }
    
    
  



    #################
    ## Parse Dates ##
    #################
 function getfromDate($str,$fromMonth){
        $date= "";
        if(strpos(strtolower($str),strtolower($fromMonth)) &&  $date=="")
          $date= substr($str,strpos(strtolower($str),strtolower($fromMonth))-5,5);
        return preg_replace('/[^0-9.]+/', '', $date);;
    }

    

function getFromMonth($str){
    $mon  = "";
    $_MONTH = array("January", "Febuary","March","April","May","June","July","August","September","October","November","December");
    foreach($_MONTH as $month){
        if(strpos($str,$month) &&  $mon==""){
            $mon= substr($str,strpos($str,$month),5);
            return $month;
         }
    }
}


  function getToMonth($str){
        $_MONTH = array("January", "Febuary","March","April","May","June","July","August","September","October","November","December");
        $_MONTH = array_reverse($_MONTH);
        foreach($_MONTH as $month){
          if(strrpos($str,$month)){
             return $month;
           }
        }
  }



  function getToDate($str,$monthToFind){
      $date = "";
      if(strrpos(strtolower($str),strtolower($monthToFind)) &&  $date=="")
          $date= substr($str,strrpos(strtolower($str),strtolower($monthToFind))-5,5);
        return preg_replace('/[^0-9.]+/', '', $date);
    }

 

 function searchForId($id, $array) {
   foreach ($array as $key => $val) {
     if ($val['COTTAGE_URL'] === $id) {
       return $key;
     }
   }
   return null;
 }




####################################################################################################################
####################################################################################################################
####################################################################################################################
####################################################################################################################
    
 $originalPrice ="";
 $discountedPrice="";
 $calcPercentage="";
 $discountAmount="";
 $percentage = "";
 $i = 0;
 $blacklist = array( );
 
 $url = "http://www.coastandcountry.co.uk/cottage-details/";

 scraperwiki::attach("special_offers_coast_and_country_summary_delete");
 # get an array of the cottage data to scrape
 $cottData = scraperwiki::select("COTTAGE_URL from 'special_offers_coast_and_country_summary_delete'.SWDATA order by COTTAGE_URL");

 $placeholder = scraperwiki::get_var("cottID");

 if($placeholder != ""){
   $index = searchForId($placeholder ,$cottData);
   $cottData = array_splice($cottData,$index);  
 }

 require 'scraperwiki/simple_html_dom.php';
 $dom = new simple_html_dom();

 foreach($cottData as $value){

    scraperwiki::save_var("cottID",$value['COTTAGE_URL']);
    // check the cottage url against the blacklist
    foreach($blacklist as $blItem){
      if($value['COTTAGE_URL'] == $blItem)
        continue 2;
    }

    //load the page into the scraper
    $html = scraperWiki::scrape($url.$value['COTTAGE_URL']);
#$html = scraperWiki::scrape("http://www.coastandcountry.co.uk/cottage-details/21DART");

    $dom->load($html);
    $feature = "";
    $image = "";
    $imgURL = "";
    $xtraFeatures = "";

   $specialOffer = "";
   date_default_timezone_set('GMT');
   $date = new DateTime();
   ########################################
   #         get the special offer        #
   ########################################
   foreach($dom->find('div[id=property-specialoffers-box-content]') as $specialOffer){
    
     $dateFrom = "";
     $dateTo = "";
     $duration = "";
     $originalPrice = "";
     $discountedPrice = "";
     $percentage = "";  
     $discountAmount = "";

     $OK = true;
     $specialOffer = $specialOffer->plaintext;
echo "\nspecialOffer ".$specialOffer;
     $valid = isOffer($specialOffer);
     if($valid){        
        # break the text down into the deals available
       
       # $ok = false;
        $str = $specialOffer;
            
        // functions to parse the special offer string
        #### deal with price ####

        // initialise variables
        $discountAmount = "";
        $originalPrice = "";
        $calcPercentage = "";
                
        // Get discounted price
       $discountedPrice =  getDiscountedPrice($str);  
echo " discountedPrice ".$discountedPrice;
        // Get discount in £
        $discountAmount = getDiscountAmount($str);
echo "\ndiscountAmount ".$discountAmount;

        // get discount percentage
        $percentageArr = parseTextAround($specialOffer,"%");
        if(isset($percentageArr[0]))
           $percentage = $percentageArr[0];
        elseif($discountedPrice != ""){
           $originalPrice = $discountedPrice + $discountAmount;  
           $percentage= calcPercentage($discountAmount,$originalPrice);
        }
echo  "\n percentage ".$percentage;        
        // get original price from discount percentage and discounted price
         $originalPrice = $discountedPrice +  $discountAmount;


        if($originalPrice <=$discountedPrice || $discountedPrice == 0){
           $originalPrice= getOriginalPrice($specialOffer);
           if($originalPrice == "" || $originalPrice < $discountedPrice || $originalPrice == $discountedPrice || $originalPrice == $discountAmount)
              $originalPrice = calcOriginalPrice($discountedPrice,$percentage);
        }


echo "\n originalPrice ".$originalPrice;
                   
        # now fill in any missing values
        $discountAmount =  $originalPrice - $discountedPrice;
 
        if($discountedPrice == "")
        $discountedPrice = $originalPrice - $discountAmount;
           
echo "\n discountedPrice ".$discountedPrice;


        if($percentage == ""){
          // get discount percentage
          $percentageArr = parseTextAround($specialOffer,"%");
          if(isset($percentageArr[0]))
             $percentage = $percentageArr[0];
          elseif( $discountedPrice!=""){
             $originalPrice = $discountedPrice + $discountAmount;  
             $percentage= calcPercentage($discountAmount,$originalPrice);
           }
        }

echo "\n percentage ".$percentage;
                
        if($originalPrice == "")
           $originalPrice = calcOriginalPrice($discountedPrice,$percentage );
echo "\n originalPrice ".$originalPrice;      
echo "\n";


echo "foo";
echo "\n";
echo "bar";
echo "\n";

         
        #### deal with dates ####
        $getToMonth = getToMonth($specialOffer);  
        $getToDate= getToDate($specialOffer,$getToMonth);  

        $getToDate = getDaysinMonth($getToMonth);
        $dateTo = date('Y-m-d',  strtotime($getToDate." ".$getToMonth));  


        $getFromMonth = getFromMonth($specialOffer);
        $getFromDate= getFromDate($specialOffer,$getFromMonth);
        
        if($getFromDate == "")// the whole month is on offer as there is no set date
           $getFromDate = "1st";
        $dateFrom = date('Y-m-d',  strtotime($getFromDate." ".$getFromMonth));   
echo "start";                      
echo $dateFrom;
echo $dateTo;

        // Update dateFrom to saturdays date
        $tmptime = strtotime($getFromDate." ".$getFromMonth);

        $tme =  mktime(0, 0, 0, date('m',$tmptime),date('d',$tmptime),date('Y',$tmptime));

      
        echo date("D",$tme);

        while(date("D",$tme) != "Sat"){
            $tme =  mktime(0, 0, 0, date('m',$tmptime),date('d',$tmptime)+1,date('Y',$tmptime));
            echo date("D",$tme);
        }
        echo date("D",$tme);
    


        $datetime1 = new DateTime($dateFrom);
        $datetime2 = new DateTime($dateTo);
        $interval = $datetime1->diff($datetime2);
        $interval =  $interval->format('%a');

        if($interval == 0){
           $dealLength = 1;
        }else{
           $dealLength = ($interval / 7)+1;
        }
        $dealLength = intval($dealLength) ;
        # limit the deal length to 10 weeks
        if($dealLength > 10)
            $dealLength = 10;   
       
# need to increment the date to show
        for($j=0; $j<$dealLength; $j++){
           $currEndDate = date('Y-m-d',strtotime('+1 week',strtotime($dateFrom)));
            
           $id++;
           $originalPrice = round($originalPrice,0,PHP_ROUND_HALF_DOWN);
           $discountAmount = round($discountAmount,0,PHP_ROUND_HALF_DOWN);
          
           $duration = abs(strtotime($currEndDate) - strtotime($dateFrom ));
           $duration = $duration / (60*60*24);  // should result in 7
                   








           /*  Get the Data  */
           // get Cottage name
           $element = $dom->find('title');
           $text = $element[0]->plaintext;
           $cottageName = substr($text,0, strpos($text,"-"));
           $features = "";





           
           # rules for exceptions
           if($originalPrice == 0) $OK = 0;
           if($percentage == 0) $OK = 0;
           if($discountAmount == 0) $OK = 0;
           if($discountedPrice == 0) $OK = 0;
           if($duration == 0) $OK = 0;
           if(!$dateFrom == 0) $OK = 0;
           if(!$dateTo == 0) $OK = 0;
           if($discountAmount >= $originalPrice) $OK = 0;
          #if($discountAmount >= $discountedPrice) $OK = false;
            

                /*  save the data  */

           if($OK === 0){

                print_r("Not OK");
                try {
                    $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
                    $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
                   # format Date correctly for database
                    $dateFrom = date_create_from_format('jS F Y',$dateFrom);
                    $dateTo =   date_create_from_format('jS F Y',$dateTo);
                } catch (Exception $e) {
                   $dateFrom = "";
                   $dateTo = "";
                }


                 $record = array(
                      'ID'                => $i,
                      'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
                      'DATE_FROM'         => $dateFrom->format('Y-m-d'),
                      'DATE_TO'           => $dateTo->format('Y-m-d'),
                      'DURATION'          => $duration,
                      'WAS_PRICE'         => $originalPrice,
                      'NOW_PRICE'         => $discountedPrice,
                      'DISCOUNT_PERCENT'  => $percentage,
                      'DISCOUNT_AMOUNT'   => $discountAmount,
                      'Text'              => $specialOffer ,
                      'TIMESTAMP'         => $date->getTimestamp(),
                      'RTYPE'             => 'T',
                   );
                 $i++;
                 scraperwiki::save(array('ID'), $record);

           }else{

                try {
                    $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
                    $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
                   # format Date correctly for database
                    $dateFrom = date_create_from_format('jS F Y',$dateFrom);
                    $dateTo =   date_create_from_format('jS F Y',$dateTo);
                } catch (Exception $e) {
                   $dateFrom = "";
                   $dateTo = "";
                }
                $record = array(
                         'ID'                => $i,
                         'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
                         'DATE_FROM'         => $dateFrom->format('Y-m-d'),
                         'DATE_TO'           => $dateTo->format('Y-m-d'),
                         'DURATION'          => $duration,
                         'WAS_PRICE'         => $originalPrice,
                         'NOW_PRICE'         => $discountedPrice,
                         'DISCOUNT_PERCENT'  => $percentage,
                         'DISCOUNT_AMOUNT'   => $discountAmount,
                         'Text'              => $specialOffer ,
                         'TIMESTAMP'         => $date->getTimestamp(),
                         'RTYPE'             => 'P',
                 );



                 $i++;
                 scraperwiki::save(array('ID'), $record);     

           }
          
        }

       }else{ // if not valid
           try {
             $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
             $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
             # format Date correctly for database
             $dateFrom = date_create_from_format('jS F Y',$dateFrom);
             $dateTo =   date_create_from_format('jS F Y',$dateTo);
           } catch (Exception $e) {
              $dateFrom = "";
              $dateTo = "";
           }
           $record = array(
              'ID'                => $i,
              'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
              'DATE_FROM'         => $dateFrom->format('Y-m-d'),
              'DATE_TO'           => $dateTo->format('Y-m-d'),
              'DURATION'          => $duration,
              'WAS_PRICE'         => $originalPrice,
              'NOW_PRICE'         => $discountedPrice,
              'DISCOUNT_PERCENT'  => $percentage,
              'DISCOUNT_AMOUNT'   => $discountAmount,
              'Text'              => $specialOffer ,
              'TIMESTAMP'         => $date->getTimestamp(),
              'RTYPE'             => 'T',
           );


           $i++;
           scraperwiki::save(array('ID'), $record);

}

}
}
?>
<?php
ini_set('display_errors',1); 
 error_reporting(E_ALL);

 scraperwiki::save_var('dummy', 0);
/*
scraperwiki::sqliteexecute("CREATE TABLE IF NOT EXISTS turk_table 
(ID int,COTTAGE_URL string , TEXT string, DATE string, dateFrom string, dateTo  string, duration  string, originalPrice  string, discountedPrice  string,  percentage string,  discountAmount string, URL string, TYPE string)");
*/ 
$id = 0;


  // get the number of days in the month
 function getDaysinMonth($month){
    $monthArray = array(1=>"january", 2=>"feburary" ,3=>"march" ,4=>"april" ,5=>"may" ,6=>"june" ,7=>"july" ,8=>"august" ,9=>"september" ,10=>"october" ,11=>"novenber" ,12=>"december");
    foreach($monthArray as $key=>$value){
      if($value == strtolower($month))
         $monthNum = $key;
         continue;
    }
    return cal_days_in_month(CAL_GREGORIAN ,$monthNum ,date("Y"));
 }



  /*
  ** Check for offers that aren't wanted
  **
  */
  function isOffer($str){
    $invalidString = array("Please call","to book this offer","must be made by","over the phone","booked before","for remaining dates","REDUCTION ON SHORT BREAKS ","call 0","booked  before","book before");
    $validString = array("%","£","&pound;","£","&pound;");
    $ok = false;
    # Test that its an offer
    $count = 0;

    foreach($validString as $vStr){
      if(strpos(strtolower($str), strtolower($vStr))!=false){
        $count++;
        $ok = true;
      }
    }  
    # Test that there are no invalid strings within the offer

    if($ok && $count >=2){
      foreach($invalidString as $invStr){
        if(strpos(strtolower($str), strtolower($invStr)))
          return false;
      }
      return true;
    }
    echo "Not an Offer!";
    return false;
  }
    

    // parse text and extract useful data
    //$str - The Text
    //$start - the character to start scraping from
    //$end - the character to scrape to. Default = " " (space)
    function parseTextAround($str,$start,$end=" "){
       $returnArr = array();
       $strArr = explode($start,strtolower($str));
       $i = 0;
       if(count($strArr)>=2){
         foreach($strArr as $item){
           if(!$i % 2){           
             $parsedStr = substr($strArr[$i],-2,2);
             array_push($returnArr,$parsedStr);
             $i = $i+2;
           }
         }
         return $returnArr;
       }
    }
    


    /*
    ** getDiscountAmount($str)
    ** $str = the text to parse
    ** returns the discount in £
    */
    function getDiscountAmount($str){
      # split the text up by full stop for multiple deals
          $discount = "";
          // standardise the currency Symbol
          $str = str_replace("&pound;","£",$str);
          #$str = str_replace("£","",$str);
          // dont parse any records with percentages in it
          // as we can work out the discount ourselves
          if(strpos(strtolower($str),"%"))
            return;
          if(strpos(strtolower($str),"saving") &&  $discount=="")
              $discount = substr($str,strpos(strtolower($str),"saving"));
        
          if(strpos(strtolower($str),"off") &&  $discount=="" )
              $discount = substr($str,strpos($str,"£"),strpos(strtolower($str),"off"));
          if(strpos(strtolower($str),"discount of") &&  $discount=="")
              $discount = substr($str,strpos($str,strpos($str,"£"),"discount of ")+12,8);
          $discount =  preg_replace('/[^0-9.]+/', '',$discount);

      return   $discount;
    }
    
    /*
    ** getDiscountedPrice($str)
    ** $str = the text to parse
    ** returns the discounted price
    */
    function getDiscountedPrice($str){
      $i = 0;
      $amountAfterDiscount = "";
      
      $str = str_replace("&pound;","£",$str);
      $amount = "";

      if(strpos(strtolower($str),"just") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"just")+4,12);  
      if(strpos(strtolower($str),"from") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"from")+4,12);
      if(strpos(strtolower($str),"now") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"now")+3,12);
      if(strpos(strtolower($str),"only") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"only")+4,12);
      if(strpos(strtolower($str),"discounted price ") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"discounted price ")+17 ,6); 

      # if there is a decimal point, remove it and everything after it
      if(strpos($amountAfterDiscount,"."))
          $amountAfterDiscount = substr($amountAfterDiscount,0,strpos($amountAfterDiscount,"."));
      $amountAfterDiscount = preg_replace('/[^0-9.]+/', '', $amountAfterDiscount);    
      return $amountAfterDiscount;
    }
    
     /*
    ** getDiscountedPrice($str)
    ** $str = the text to parse
    ** returns the discounted price
    */
    function getOriginalPrice($str){
      $i = 0;
      $amountAfterDiscount = "";
      $str = str_replace("&pound;","£",$str);
      $amount = "";
      if(strpos(strtolower($str),"weeks ") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"weeks ")+6,3);  
      # if there is a decimal point, remove it and everything after it
      if(strpos($amountAfterDiscount,"."))
          $amountAfterDiscount = substr($amountAfterDiscount,0,strpos($amountAfterDiscount,"."));
      $amountAfterDiscount = preg_replace('/[^0-9.]+/', '', $amountAfterDiscount);      
      return $amountAfterDiscount;
    }
    
    
    /*
    ** calcOriginalPrice($percentageAmount,$discountPrice)
    ** $percentageAmount = The percentage the price has been reduced by
    ** $discountPrice = The price after the deduction
    ** returns the Original Price
    ** eg 325 -  (100 - 25) = 4.33
    ** 4.33 * 100 = 433
    */
    function calcOriginalPrice($discountPrice,$percentageAmount){
          return  round(($discountPrice / (100 - $percentageAmount)) * 100);
    }


    /*
    ** calcPercentage($discountAmount,$originalPrice)
    ** $discountAmount = the discount in £
    ** $originalPrice = the original price in £
    ** returns the discount as a percentage
    */
    function calcPercentage($discountAmount,$originalPrice){
          return round($discountAmount / $originalPrice * 100);
    }
    
    
  



    #################
    ## Parse Dates ##
    #################
 function getfromDate($str,$fromMonth){
        $date= "";
        if(strpos(strtolower($str),strtolower($fromMonth)) &&  $date=="")
          $date= substr($str,strpos(strtolower($str),strtolower($fromMonth))-5,5);
        return preg_replace('/[^0-9.]+/', '', $date);;
    }

    

function getFromMonth($str){
    $mon  = "";
    $_MONTH = array("January", "Febuary","March","April","May","June","July","August","September","October","November","December");
    foreach($_MONTH as $month){
        if(strpos($str,$month) &&  $mon==""){
            $mon= substr($str,strpos($str,$month),5);
            return $month;
         }
    }
}


  function getToMonth($str){
        $_MONTH = array("January", "Febuary","March","April","May","June","July","August","September","October","November","December");
        $_MONTH = array_reverse($_MONTH);
        foreach($_MONTH as $month){
          if(strrpos($str,$month)){
             return $month;
           }
        }
  }



  function getToDate($str,$monthToFind){
      $date = "";
      if(strrpos(strtolower($str),strtolower($monthToFind)) &&  $date=="")
          $date= substr($str,strrpos(strtolower($str),strtolower($monthToFind))-5,5);
        return preg_replace('/[^0-9.]+/', '', $date);
    }

 

 function searchForId($id, $array) {
   foreach ($array as $key => $val) {
     if ($val['COTTAGE_URL'] === $id) {
       return $key;
     }
   }
   return null;
 }




####################################################################################################################
####################################################################################################################
####################################################################################################################
####################################################################################################################
    
 $originalPrice ="";
 $discountedPrice="";
 $calcPercentage="";
 $discountAmount="";
 $percentage = "";
 $i = 0;
 $blacklist = array( );
 
 $url = "http://www.coastandcountry.co.uk/cottage-details/";

 scraperwiki::attach("special_offers_coast_and_country_summary_delete");
 # get an array of the cottage data to scrape
 $cottData = scraperwiki::select("COTTAGE_URL from 'special_offers_coast_and_country_summary_delete'.SWDATA order by COTTAGE_URL");

 $placeholder = scraperwiki::get_var("cottID");

 if($placeholder != ""){
   $index = searchForId($placeholder ,$cottData);
   $cottData = array_splice($cottData,$index);  
 }

 require 'scraperwiki/simple_html_dom.php';
 $dom = new simple_html_dom();

 foreach($cottData as $value){

    scraperwiki::save_var("cottID",$value['COTTAGE_URL']);
    // check the cottage url against the blacklist
    foreach($blacklist as $blItem){
      if($value['COTTAGE_URL'] == $blItem)
        continue 2;
    }

    //load the page into the scraper
    $html = scraperWiki::scrape($url.$value['COTTAGE_URL']);
#$html = scraperWiki::scrape("http://www.coastandcountry.co.uk/cottage-details/21DART");

    $dom->load($html);
    $feature = "";
    $image = "";
    $imgURL = "";
    $xtraFeatures = "";

   $specialOffer = "";
   date_default_timezone_set('GMT');
   $date = new DateTime();
   ########################################
   #         get the special offer        #
   ########################################
   foreach($dom->find('div[id=property-specialoffers-box-content]') as $specialOffer){
    
     $dateFrom = "";
     $dateTo = "";
     $duration = "";
     $originalPrice = "";
     $discountedPrice = "";
     $percentage = "";  
     $discountAmount = "";

     $OK = true;
     $specialOffer = $specialOffer->plaintext;
echo "\nspecialOffer ".$specialOffer;
     $valid = isOffer($specialOffer);
     if($valid){        
        # break the text down into the deals available
       
       # $ok = false;
        $str = $specialOffer;
            
        // functions to parse the special offer string
        #### deal with price ####

        // initialise variables
        $discountAmount = "";
        $originalPrice = "";
        $calcPercentage = "";
                
        // Get discounted price
       $discountedPrice =  getDiscountedPrice($str);  
echo " discountedPrice ".$discountedPrice;
        // Get discount in £
        $discountAmount = getDiscountAmount($str);
echo "\ndiscountAmount ".$discountAmount;

        // get discount percentage
        $percentageArr = parseTextAround($specialOffer,"%");
        if(isset($percentageArr[0]))
           $percentage = $percentageArr[0];
        elseif($discountedPrice != ""){
           $originalPrice = $discountedPrice + $discountAmount;  
           $percentage= calcPercentage($discountAmount,$originalPrice);
        }
echo  "\n percentage ".$percentage;        
        // get original price from discount percentage and discounted price
         $originalPrice = $discountedPrice +  $discountAmount;


        if($originalPrice <=$discountedPrice || $discountedPrice == 0){
           $originalPrice= getOriginalPrice($specialOffer);
           if($originalPrice == "" || $originalPrice < $discountedPrice || $originalPrice == $discountedPrice || $originalPrice == $discountAmount)
              $originalPrice = calcOriginalPrice($discountedPrice,$percentage);
        }


echo "\n originalPrice ".$originalPrice;
                   
        # now fill in any missing values
        $discountAmount =  $originalPrice - $discountedPrice;
 
        if($discountedPrice == "")
        $discountedPrice = $originalPrice - $discountAmount;
           
echo "\n discountedPrice ".$discountedPrice;


        if($percentage == ""){
          // get discount percentage
          $percentageArr = parseTextAround($specialOffer,"%");
          if(isset($percentageArr[0]))
             $percentage = $percentageArr[0];
          elseif( $discountedPrice!=""){
             $originalPrice = $discountedPrice + $discountAmount;  
             $percentage= calcPercentage($discountAmount,$originalPrice);
           }
        }

echo "\n percentage ".$percentage;
                
        if($originalPrice == "")
           $originalPrice = calcOriginalPrice($discountedPrice,$percentage );
echo "\n originalPrice ".$originalPrice;      
echo "\n";


echo "foo";
echo "\n";
echo "bar";
echo "\n";

         
        #### deal with dates ####
        $getToMonth = getToMonth($specialOffer);  
        $getToDate= getToDate($specialOffer,$getToMonth);  

        $getToDate = getDaysinMonth($getToMonth);
        $dateTo = date('Y-m-d',  strtotime($getToDate." ".$getToMonth));  


        $getFromMonth = getFromMonth($specialOffer);
        $getFromDate= getFromDate($specialOffer,$getFromMonth);
        
        if($getFromDate == "")// the whole month is on offer as there is no set date
           $getFromDate = "1st";
        $dateFrom = date('Y-m-d',  strtotime($getFromDate." ".$getFromMonth));   
echo "start";                      
echo $dateFrom;
echo $dateTo;

        // Update dateFrom to saturdays date
        $tmptime = strtotime($getFromDate." ".$getFromMonth);

        $tme =  mktime(0, 0, 0, date('m',$tmptime),date('d',$tmptime),date('Y',$tmptime));

      
        echo date("D",$tme);

        while(date("D",$tme) != "Sat"){
            $tme =  mktime(0, 0, 0, date('m',$tmptime),date('d',$tmptime)+1,date('Y',$tmptime));
            echo date("D",$tme);
        }
        echo date("D",$tme);
    


        $datetime1 = new DateTime($dateFrom);
        $datetime2 = new DateTime($dateTo);
        $interval = $datetime1->diff($datetime2);
        $interval =  $interval->format('%a');

        if($interval == 0){
           $dealLength = 1;
        }else{
           $dealLength = ($interval / 7)+1;
        }
        $dealLength = intval($dealLength) ;
        # limit the deal length to 10 weeks
        if($dealLength > 10)
            $dealLength = 10;   
       
# need to increment the date to show
        for($j=0; $j<$dealLength; $j++){
           $currEndDate = date('Y-m-d',strtotime('+1 week',strtotime($dateFrom)));
            
           $id++;
           $originalPrice = round($originalPrice,0,PHP_ROUND_HALF_DOWN);
           $discountAmount = round($discountAmount,0,PHP_ROUND_HALF_DOWN);
          
           $duration = abs(strtotime($currEndDate) - strtotime($dateFrom ));
           $duration = $duration / (60*60*24);  // should result in 7
                   








           /*  Get the Data  */
           // get Cottage name
           $element = $dom->find('title');
           $text = $element[0]->plaintext;
           $cottageName = substr($text,0, strpos($text,"-"));
           $features = "";





           
           # rules for exceptions
           if($originalPrice == 0) $OK = 0;
           if($percentage == 0) $OK = 0;
           if($discountAmount == 0) $OK = 0;
           if($discountedPrice == 0) $OK = 0;
           if($duration == 0) $OK = 0;
           if(!$dateFrom == 0) $OK = 0;
           if(!$dateTo == 0) $OK = 0;
           if($discountAmount >= $originalPrice) $OK = 0;
          #if($discountAmount >= $discountedPrice) $OK = false;
            

                /*  save the data  */

           if($OK === 0){

                print_r("Not OK");
                try {
                    $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
                    $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
                   # format Date correctly for database
                    $dateFrom = date_create_from_format('jS F Y',$dateFrom);
                    $dateTo =   date_create_from_format('jS F Y',$dateTo);
                } catch (Exception $e) {
                   $dateFrom = "";
                   $dateTo = "";
                }


                 $record = array(
                      'ID'                => $i,
                      'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
                      'DATE_FROM'         => $dateFrom->format('Y-m-d'),
                      'DATE_TO'           => $dateTo->format('Y-m-d'),
                      'DURATION'          => $duration,
                      'WAS_PRICE'         => $originalPrice,
                      'NOW_PRICE'         => $discountedPrice,
                      'DISCOUNT_PERCENT'  => $percentage,
                      'DISCOUNT_AMOUNT'   => $discountAmount,
                      'Text'              => $specialOffer ,
                      'TIMESTAMP'         => $date->getTimestamp(),
                      'RTYPE'             => 'T',
                   );
                 $i++;
                 scraperwiki::save(array('ID'), $record);

           }else{

                try {
                    $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
                    $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
                   # format Date correctly for database
                    $dateFrom = date_create_from_format('jS F Y',$dateFrom);
                    $dateTo =   date_create_from_format('jS F Y',$dateTo);
                } catch (Exception $e) {
                   $dateFrom = "";
                   $dateTo = "";
                }
                $record = array(
                         'ID'                => $i,
                         'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
                         'DATE_FROM'         => $dateFrom->format('Y-m-d'),
                         'DATE_TO'           => $dateTo->format('Y-m-d'),
                         'DURATION'          => $duration,
                         'WAS_PRICE'         => $originalPrice,
                         'NOW_PRICE'         => $discountedPrice,
                         'DISCOUNT_PERCENT'  => $percentage,
                         'DISCOUNT_AMOUNT'   => $discountAmount,
                         'Text'              => $specialOffer ,
                         'TIMESTAMP'         => $date->getTimestamp(),
                         'RTYPE'             => 'P',
                 );



                 $i++;
                 scraperwiki::save(array('ID'), $record);     

           }
          
        }

       }else{ // if not valid
           try {
             $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
             $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
             # format Date correctly for database
             $dateFrom = date_create_from_format('jS F Y',$dateFrom);
             $dateTo =   date_create_from_format('jS F Y',$dateTo);
           } catch (Exception $e) {
              $dateFrom = "";
              $dateTo = "";
           }
           $record = array(
              'ID'                => $i,
              'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
              'DATE_FROM'         => $dateFrom->format('Y-m-d'),
              'DATE_TO'           => $dateTo->format('Y-m-d'),
              'DURATION'          => $duration,
              'WAS_PRICE'         => $originalPrice,
              'NOW_PRICE'         => $discountedPrice,
              'DISCOUNT_PERCENT'  => $percentage,
              'DISCOUNT_AMOUNT'   => $discountAmount,
              'Text'              => $specialOffer ,
              'TIMESTAMP'         => $date->getTimestamp(),
              'RTYPE'             => 'T',
           );


           $i++;
           scraperwiki::save(array('ID'), $record);

}

}
}
?>
<?php
ini_set('display_errors',1); 
 error_reporting(E_ALL);

 scraperwiki::save_var('dummy', 0);
/*
scraperwiki::sqliteexecute("CREATE TABLE IF NOT EXISTS turk_table 
(ID int,COTTAGE_URL string , TEXT string, DATE string, dateFrom string, dateTo  string, duration  string, originalPrice  string, discountedPrice  string,  percentage string,  discountAmount string, URL string, TYPE string)");
*/ 
$id = 0;


  // get the number of days in the month
 function getDaysinMonth($month){
    $monthArray = array(1=>"january", 2=>"feburary" ,3=>"march" ,4=>"april" ,5=>"may" ,6=>"june" ,7=>"july" ,8=>"august" ,9=>"september" ,10=>"october" ,11=>"novenber" ,12=>"december");
    foreach($monthArray as $key=>$value){
      if($value == strtolower($month))
         $monthNum = $key;
         continue;
    }
    return cal_days_in_month(CAL_GREGORIAN ,$monthNum ,date("Y"));
 }



  /*
  ** Check for offers that aren't wanted
  **
  */
  function isOffer($str){
    $invalidString = array("Please call","to book this offer","must be made by","over the phone","booked before","for remaining dates","REDUCTION ON SHORT BREAKS ","call 0","booked  before","book before");
    $validString = array("%","£","&pound;","£","&pound;");
    $ok = false;
    # Test that its an offer
    $count = 0;

    foreach($validString as $vStr){
      if(strpos(strtolower($str), strtolower($vStr))!=false){
        $count++;
        $ok = true;
      }
    }  
    # Test that there are no invalid strings within the offer

    if($ok && $count >=2){
      foreach($invalidString as $invStr){
        if(strpos(strtolower($str), strtolower($invStr)))
          return false;
      }
      return true;
    }
    echo "Not an Offer!";
    return false;
  }
    

    // parse text and extract useful data
    //$str - The Text
    //$start - the character to start scraping from
    //$end - the character to scrape to. Default = " " (space)
    function parseTextAround($str,$start,$end=" "){
       $returnArr = array();
       $strArr = explode($start,strtolower($str));
       $i = 0;
       if(count($strArr)>=2){
         foreach($strArr as $item){
           if(!$i % 2){           
             $parsedStr = substr($strArr[$i],-2,2);
             array_push($returnArr,$parsedStr);
             $i = $i+2;
           }
         }
         return $returnArr;
       }
    }
    


    /*
    ** getDiscountAmount($str)
    ** $str = the text to parse
    ** returns the discount in £
    */
    function getDiscountAmount($str){
      # split the text up by full stop for multiple deals
          $discount = "";
          // standardise the currency Symbol
          $str = str_replace("&pound;","£",$str);
          #$str = str_replace("£","",$str);
          // dont parse any records with percentages in it
          // as we can work out the discount ourselves
          if(strpos(strtolower($str),"%"))
            return;
          if(strpos(strtolower($str),"saving") &&  $discount=="")
              $discount = substr($str,strpos(strtolower($str),"saving"));
        
          if(strpos(strtolower($str),"off") &&  $discount=="" )
              $discount = substr($str,strpos($str,"£"),strpos(strtolower($str),"off"));
          if(strpos(strtolower($str),"discount of") &&  $discount=="")
              $discount = substr($str,strpos($str,strpos($str,"£"),"discount of ")+12,8);
          $discount =  preg_replace('/[^0-9.]+/', '',$discount);

      return   $discount;
    }
    
    /*
    ** getDiscountedPrice($str)
    ** $str = the text to parse
    ** returns the discounted price
    */
    function getDiscountedPrice($str){
      $i = 0;
      $amountAfterDiscount = "";
      
      $str = str_replace("&pound;","£",$str);
      $amount = "";

      if(strpos(strtolower($str),"just") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"just")+4,12);  
      if(strpos(strtolower($str),"from") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"from")+4,12);
      if(strpos(strtolower($str),"now") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"now")+3,12);
      if(strpos(strtolower($str),"only") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"only")+4,12);
      if(strpos(strtolower($str),"discounted price ") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"discounted price ")+17 ,6); 

      # if there is a decimal point, remove it and everything after it
      if(strpos($amountAfterDiscount,"."))
          $amountAfterDiscount = substr($amountAfterDiscount,0,strpos($amountAfterDiscount,"."));
      $amountAfterDiscount = preg_replace('/[^0-9.]+/', '', $amountAfterDiscount);    
      return $amountAfterDiscount;
    }
    
     /*
    ** getDiscountedPrice($str)
    ** $str = the text to parse
    ** returns the discounted price
    */
    function getOriginalPrice($str){
      $i = 0;
      $amountAfterDiscount = "";
      $str = str_replace("&pound;","£",$str);
      $amount = "";
      if(strpos(strtolower($str),"weeks ") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"weeks ")+6,3);  
      # if there is a decimal point, remove it and everything after it
      if(strpos($amountAfterDiscount,"."))
          $amountAfterDiscount = substr($amountAfterDiscount,0,strpos($amountAfterDiscount,"."));
      $amountAfterDiscount = preg_replace('/[^0-9.]+/', '', $amountAfterDiscount);      
      return $amountAfterDiscount;
    }
    
    
    /*
    ** calcOriginalPrice($percentageAmount,$discountPrice)
    ** $percentageAmount = The percentage the price has been reduced by
    ** $discountPrice = The price after the deduction
    ** returns the Original Price
    ** eg 325 -  (100 - 25) = 4.33
    ** 4.33 * 100 = 433
    */
    function calcOriginalPrice($discountPrice,$percentageAmount){
          return  round(($discountPrice / (100 - $percentageAmount)) * 100);
    }


    /*
    ** calcPercentage($discountAmount,$originalPrice)
    ** $discountAmount = the discount in £
    ** $originalPrice = the original price in £
    ** returns the discount as a percentage
    */
    function calcPercentage($discountAmount,$originalPrice){
          return round($discountAmount / $originalPrice * 100);
    }
    
    
  



    #################
    ## Parse Dates ##
    #################
 function getfromDate($str,$fromMonth){
        $date= "";
        if(strpos(strtolower($str),strtolower($fromMonth)) &&  $date=="")
          $date= substr($str,strpos(strtolower($str),strtolower($fromMonth))-5,5);
        return preg_replace('/[^0-9.]+/', '', $date);;
    }

    

function getFromMonth($str){
    $mon  = "";
    $_MONTH = array("January", "Febuary","March","April","May","June","July","August","September","October","November","December");
    foreach($_MONTH as $month){
        if(strpos($str,$month) &&  $mon==""){
            $mon= substr($str,strpos($str,$month),5);
            return $month;
         }
    }
}


  function getToMonth($str){
        $_MONTH = array("January", "Febuary","March","April","May","June","July","August","September","October","November","December");
        $_MONTH = array_reverse($_MONTH);
        foreach($_MONTH as $month){
          if(strrpos($str,$month)){
             return $month;
           }
        }
  }



  function getToDate($str,$monthToFind){
      $date = "";
      if(strrpos(strtolower($str),strtolower($monthToFind)) &&  $date=="")
          $date= substr($str,strrpos(strtolower($str),strtolower($monthToFind))-5,5);
        return preg_replace('/[^0-9.]+/', '', $date);
    }

 

 function searchForId($id, $array) {
   foreach ($array as $key => $val) {
     if ($val['COTTAGE_URL'] === $id) {
       return $key;
     }
   }
   return null;
 }




####################################################################################################################
####################################################################################################################
####################################################################################################################
####################################################################################################################
    
 $originalPrice ="";
 $discountedPrice="";
 $calcPercentage="";
 $discountAmount="";
 $percentage = "";
 $i = 0;
 $blacklist = array( );
 
 $url = "http://www.coastandcountry.co.uk/cottage-details/";

 scraperwiki::attach("special_offers_coast_and_country_summary_delete");
 # get an array of the cottage data to scrape
 $cottData = scraperwiki::select("COTTAGE_URL from 'special_offers_coast_and_country_summary_delete'.SWDATA order by COTTAGE_URL");

 $placeholder = scraperwiki::get_var("cottID");

 if($placeholder != ""){
   $index = searchForId($placeholder ,$cottData);
   $cottData = array_splice($cottData,$index);  
 }

 require 'scraperwiki/simple_html_dom.php';
 $dom = new simple_html_dom();

 foreach($cottData as $value){

    scraperwiki::save_var("cottID",$value['COTTAGE_URL']);
    // check the cottage url against the blacklist
    foreach($blacklist as $blItem){
      if($value['COTTAGE_URL'] == $blItem)
        continue 2;
    }

    //load the page into the scraper
    $html = scraperWiki::scrape($url.$value['COTTAGE_URL']);
#$html = scraperWiki::scrape("http://www.coastandcountry.co.uk/cottage-details/21DART");

    $dom->load($html);
    $feature = "";
    $image = "";
    $imgURL = "";
    $xtraFeatures = "";

   $specialOffer = "";
   date_default_timezone_set('GMT');
   $date = new DateTime();
   ########################################
   #         get the special offer        #
   ########################################
   foreach($dom->find('div[id=property-specialoffers-box-content]') as $specialOffer){
    
     $dateFrom = "";
     $dateTo = "";
     $duration = "";
     $originalPrice = "";
     $discountedPrice = "";
     $percentage = "";  
     $discountAmount = "";

     $OK = true;
     $specialOffer = $specialOffer->plaintext;
echo "\nspecialOffer ".$specialOffer;
     $valid = isOffer($specialOffer);
     if($valid){        
        # break the text down into the deals available
       
       # $ok = false;
        $str = $specialOffer;
            
        // functions to parse the special offer string
        #### deal with price ####

        // initialise variables
        $discountAmount = "";
        $originalPrice = "";
        $calcPercentage = "";
                
        // Get discounted price
       $discountedPrice =  getDiscountedPrice($str);  
echo " discountedPrice ".$discountedPrice;
        // Get discount in £
        $discountAmount = getDiscountAmount($str);
echo "\ndiscountAmount ".$discountAmount;

        // get discount percentage
        $percentageArr = parseTextAround($specialOffer,"%");
        if(isset($percentageArr[0]))
           $percentage = $percentageArr[0];
        elseif($discountedPrice != ""){
           $originalPrice = $discountedPrice + $discountAmount;  
           $percentage= calcPercentage($discountAmount,$originalPrice);
        }
echo  "\n percentage ".$percentage;        
        // get original price from discount percentage and discounted price
         $originalPrice = $discountedPrice +  $discountAmount;


        if($originalPrice <=$discountedPrice || $discountedPrice == 0){
           $originalPrice= getOriginalPrice($specialOffer);
           if($originalPrice == "" || $originalPrice < $discountedPrice || $originalPrice == $discountedPrice || $originalPrice == $discountAmount)
              $originalPrice = calcOriginalPrice($discountedPrice,$percentage);
        }


echo "\n originalPrice ".$originalPrice;
                   
        # now fill in any missing values
        $discountAmount =  $originalPrice - $discountedPrice;
 
        if($discountedPrice == "")
        $discountedPrice = $originalPrice - $discountAmount;
           
echo "\n discountedPrice ".$discountedPrice;


        if($percentage == ""){
          // get discount percentage
          $percentageArr = parseTextAround($specialOffer,"%");
          if(isset($percentageArr[0]))
             $percentage = $percentageArr[0];
          elseif( $discountedPrice!=""){
             $originalPrice = $discountedPrice + $discountAmount;  
             $percentage= calcPercentage($discountAmount,$originalPrice);
           }
        }

echo "\n percentage ".$percentage;
                
        if($originalPrice == "")
           $originalPrice = calcOriginalPrice($discountedPrice,$percentage );
echo "\n originalPrice ".$originalPrice;      
echo "\n";


echo "foo";
echo "\n";
echo "bar";
echo "\n";

         
        #### deal with dates ####
        $getToMonth = getToMonth($specialOffer);  
        $getToDate= getToDate($specialOffer,$getToMonth);  

        $getToDate = getDaysinMonth($getToMonth);
        $dateTo = date('Y-m-d',  strtotime($getToDate." ".$getToMonth));  


        $getFromMonth = getFromMonth($specialOffer);
        $getFromDate= getFromDate($specialOffer,$getFromMonth);
        
        if($getFromDate == "")// the whole month is on offer as there is no set date
           $getFromDate = "1st";
        $dateFrom = date('Y-m-d',  strtotime($getFromDate." ".$getFromMonth));   
echo "start";                      
echo $dateFrom;
echo $dateTo;

        // Update dateFrom to saturdays date
        $tmptime = strtotime($getFromDate." ".$getFromMonth);

        $tme =  mktime(0, 0, 0, date('m',$tmptime),date('d',$tmptime),date('Y',$tmptime));

      
        echo date("D",$tme);

        while(date("D",$tme) != "Sat"){
            $tme =  mktime(0, 0, 0, date('m',$tmptime),date('d',$tmptime)+1,date('Y',$tmptime));
            echo date("D",$tme);
        }
        echo date("D",$tme);
    


        $datetime1 = new DateTime($dateFrom);
        $datetime2 = new DateTime($dateTo);
        $interval = $datetime1->diff($datetime2);
        $interval =  $interval->format('%a');

        if($interval == 0){
           $dealLength = 1;
        }else{
           $dealLength = ($interval / 7)+1;
        }
        $dealLength = intval($dealLength) ;
        # limit the deal length to 10 weeks
        if($dealLength > 10)
            $dealLength = 10;   
       
# need to increment the date to show
        for($j=0; $j<$dealLength; $j++){
           $currEndDate = date('Y-m-d',strtotime('+1 week',strtotime($dateFrom)));
            
           $id++;
           $originalPrice = round($originalPrice,0,PHP_ROUND_HALF_DOWN);
           $discountAmount = round($discountAmount,0,PHP_ROUND_HALF_DOWN);
          
           $duration = abs(strtotime($currEndDate) - strtotime($dateFrom ));
           $duration = $duration / (60*60*24);  // should result in 7
                   








           /*  Get the Data  */
           // get Cottage name
           $element = $dom->find('title');
           $text = $element[0]->plaintext;
           $cottageName = substr($text,0, strpos($text,"-"));
           $features = "";





           
           # rules for exceptions
           if($originalPrice == 0) $OK = 0;
           if($percentage == 0) $OK = 0;
           if($discountAmount == 0) $OK = 0;
           if($discountedPrice == 0) $OK = 0;
           if($duration == 0) $OK = 0;
           if(!$dateFrom == 0) $OK = 0;
           if(!$dateTo == 0) $OK = 0;
           if($discountAmount >= $originalPrice) $OK = 0;
          #if($discountAmount >= $discountedPrice) $OK = false;
            

                /*  save the data  */

           if($OK === 0){

                print_r("Not OK");
                try {
                    $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
                    $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
                   # format Date correctly for database
                    $dateFrom = date_create_from_format('jS F Y',$dateFrom);
                    $dateTo =   date_create_from_format('jS F Y',$dateTo);
                } catch (Exception $e) {
                   $dateFrom = "";
                   $dateTo = "";
                }


                 $record = array(
                      'ID'                => $i,
                      'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
                      'DATE_FROM'         => $dateFrom->format('Y-m-d'),
                      'DATE_TO'           => $dateTo->format('Y-m-d'),
                      'DURATION'          => $duration,
                      'WAS_PRICE'         => $originalPrice,
                      'NOW_PRICE'         => $discountedPrice,
                      'DISCOUNT_PERCENT'  => $percentage,
                      'DISCOUNT_AMOUNT'   => $discountAmount,
                      'Text'              => $specialOffer ,
                      'TIMESTAMP'         => $date->getTimestamp(),
                      'RTYPE'             => 'T',
                   );
                 $i++;
                 scraperwiki::save(array('ID'), $record);

           }else{

                try {
                    $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
                    $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
                   # format Date correctly for database
                    $dateFrom = date_create_from_format('jS F Y',$dateFrom);
                    $dateTo =   date_create_from_format('jS F Y',$dateTo);
                } catch (Exception $e) {
                   $dateFrom = "";
                   $dateTo = "";
                }
                $record = array(
                         'ID'                => $i,
                         'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
                         'DATE_FROM'         => $dateFrom->format('Y-m-d'),
                         'DATE_TO'           => $dateTo->format('Y-m-d'),
                         'DURATION'          => $duration,
                         'WAS_PRICE'         => $originalPrice,
                         'NOW_PRICE'         => $discountedPrice,
                         'DISCOUNT_PERCENT'  => $percentage,
                         'DISCOUNT_AMOUNT'   => $discountAmount,
                         'Text'              => $specialOffer ,
                         'TIMESTAMP'         => $date->getTimestamp(),
                         'RTYPE'             => 'P',
                 );



                 $i++;
                 scraperwiki::save(array('ID'), $record);     

           }
          
        }

       }else{ // if not valid
           try {
             $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
             $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
             # format Date correctly for database
             $dateFrom = date_create_from_format('jS F Y',$dateFrom);
             $dateTo =   date_create_from_format('jS F Y',$dateTo);
           } catch (Exception $e) {
              $dateFrom = "";
              $dateTo = "";
           }
           $record = array(
              'ID'                => $i,
              'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
              'DATE_FROM'         => $dateFrom->format('Y-m-d'),
              'DATE_TO'           => $dateTo->format('Y-m-d'),
              'DURATION'          => $duration,
              'WAS_PRICE'         => $originalPrice,
              'NOW_PRICE'         => $discountedPrice,
              'DISCOUNT_PERCENT'  => $percentage,
              'DISCOUNT_AMOUNT'   => $discountAmount,
              'Text'              => $specialOffer ,
              'TIMESTAMP'         => $date->getTimestamp(),
              'RTYPE'             => 'T',
           );


           $i++;
           scraperwiki::save(array('ID'), $record);

}

}
}
?>
<?php
ini_set('display_errors',1); 
 error_reporting(E_ALL);

 scraperwiki::save_var('dummy', 0);
/*
scraperwiki::sqliteexecute("CREATE TABLE IF NOT EXISTS turk_table 
(ID int,COTTAGE_URL string , TEXT string, DATE string, dateFrom string, dateTo  string, duration  string, originalPrice  string, discountedPrice  string,  percentage string,  discountAmount string, URL string, TYPE string)");
*/ 
$id = 0;


  // get the number of days in the month
 function getDaysinMonth($month){
    $monthArray = array(1=>"january", 2=>"feburary" ,3=>"march" ,4=>"april" ,5=>"may" ,6=>"june" ,7=>"july" ,8=>"august" ,9=>"september" ,10=>"october" ,11=>"novenber" ,12=>"december");
    foreach($monthArray as $key=>$value){
      if($value == strtolower($month))
         $monthNum = $key;
         continue;
    }
    return cal_days_in_month(CAL_GREGORIAN ,$monthNum ,date("Y"));
 }



  /*
  ** Check for offers that aren't wanted
  **
  */
  function isOffer($str){
    $invalidString = array("Please call","to book this offer","must be made by","over the phone","booked before","for remaining dates","REDUCTION ON SHORT BREAKS ","call 0","booked  before","book before");
    $validString = array("%","£","&pound;","£","&pound;");
    $ok = false;
    # Test that its an offer
    $count = 0;

    foreach($validString as $vStr){
      if(strpos(strtolower($str), strtolower($vStr))!=false){
        $count++;
        $ok = true;
      }
    }  
    # Test that there are no invalid strings within the offer

    if($ok && $count >=2){
      foreach($invalidString as $invStr){
        if(strpos(strtolower($str), strtolower($invStr)))
          return false;
      }
      return true;
    }
    echo "Not an Offer!";
    return false;
  }
    

    // parse text and extract useful data
    //$str - The Text
    //$start - the character to start scraping from
    //$end - the character to scrape to. Default = " " (space)
    function parseTextAround($str,$start,$end=" "){
       $returnArr = array();
       $strArr = explode($start,strtolower($str));
       $i = 0;
       if(count($strArr)>=2){
         foreach($strArr as $item){
           if(!$i % 2){           
             $parsedStr = substr($strArr[$i],-2,2);
             array_push($returnArr,$parsedStr);
             $i = $i+2;
           }
         }
         return $returnArr;
       }
    }
    


    /*
    ** getDiscountAmount($str)
    ** $str = the text to parse
    ** returns the discount in £
    */
    function getDiscountAmount($str){
      # split the text up by full stop for multiple deals
          $discount = "";
          // standardise the currency Symbol
          $str = str_replace("&pound;","£",$str);
          #$str = str_replace("£","",$str);
          // dont parse any records with percentages in it
          // as we can work out the discount ourselves
          if(strpos(strtolower($str),"%"))
            return;
          if(strpos(strtolower($str),"saving") &&  $discount=="")
              $discount = substr($str,strpos(strtolower($str),"saving"));
        
          if(strpos(strtolower($str),"off") &&  $discount=="" )
              $discount = substr($str,strpos($str,"£"),strpos(strtolower($str),"off"));
          if(strpos(strtolower($str),"discount of") &&  $discount=="")
              $discount = substr($str,strpos($str,strpos($str,"£"),"discount of ")+12,8);
          $discount =  preg_replace('/[^0-9.]+/', '',$discount);

      return   $discount;
    }
    
    /*
    ** getDiscountedPrice($str)
    ** $str = the text to parse
    ** returns the discounted price
    */
    function getDiscountedPrice($str){
      $i = 0;
      $amountAfterDiscount = "";
      
      $str = str_replace("&pound;","£",$str);
      $amount = "";

      if(strpos(strtolower($str),"just") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"just")+4,12);  
      if(strpos(strtolower($str),"from") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"from")+4,12);
      if(strpos(strtolower($str),"now") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"now")+3,12);
      if(strpos(strtolower($str),"only") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"only")+4,12);
      if(strpos(strtolower($str),"discounted price ") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"discounted price ")+17 ,6); 

      # if there is a decimal point, remove it and everything after it
      if(strpos($amountAfterDiscount,"."))
          $amountAfterDiscount = substr($amountAfterDiscount,0,strpos($amountAfterDiscount,"."));
      $amountAfterDiscount = preg_replace('/[^0-9.]+/', '', $amountAfterDiscount);    
      return $amountAfterDiscount;
    }
    
     /*
    ** getDiscountedPrice($str)
    ** $str = the text to parse
    ** returns the discounted price
    */
    function getOriginalPrice($str){
      $i = 0;
      $amountAfterDiscount = "";
      $str = str_replace("&pound;","£",$str);
      $amount = "";
      if(strpos(strtolower($str),"weeks ") &&  $amountAfterDiscount=="")
          $amountAfterDiscount = substr($str,strpos(strtolower($str),"weeks ")+6,3);  
      # if there is a decimal point, remove it and everything after it
      if(strpos($amountAfterDiscount,"."))
          $amountAfterDiscount = substr($amountAfterDiscount,0,strpos($amountAfterDiscount,"."));
      $amountAfterDiscount = preg_replace('/[^0-9.]+/', '', $amountAfterDiscount);      
      return $amountAfterDiscount;
    }
    
    
    /*
    ** calcOriginalPrice($percentageAmount,$discountPrice)
    ** $percentageAmount = The percentage the price has been reduced by
    ** $discountPrice = The price after the deduction
    ** returns the Original Price
    ** eg 325 -  (100 - 25) = 4.33
    ** 4.33 * 100 = 433
    */
    function calcOriginalPrice($discountPrice,$percentageAmount){
          return  round(($discountPrice / (100 - $percentageAmount)) * 100);
    }


    /*
    ** calcPercentage($discountAmount,$originalPrice)
    ** $discountAmount = the discount in £
    ** $originalPrice = the original price in £
    ** returns the discount as a percentage
    */
    function calcPercentage($discountAmount,$originalPrice){
          return round($discountAmount / $originalPrice * 100);
    }
    
    
  



    #################
    ## Parse Dates ##
    #################
 function getfromDate($str,$fromMonth){
        $date= "";
        if(strpos(strtolower($str),strtolower($fromMonth)) &&  $date=="")
          $date= substr($str,strpos(strtolower($str),strtolower($fromMonth))-5,5);
        return preg_replace('/[^0-9.]+/', '', $date);;
    }

    

function getFromMonth($str){
    $mon  = "";
    $_MONTH = array("January", "Febuary","March","April","May","June","July","August","September","October","November","December");
    foreach($_MONTH as $month){
        if(strpos($str,$month) &&  $mon==""){
            $mon= substr($str,strpos($str,$month),5);
            return $month;
         }
    }
}


  function getToMonth($str){
        $_MONTH = array("January", "Febuary","March","April","May","June","July","August","September","October","November","December");
        $_MONTH = array_reverse($_MONTH);
        foreach($_MONTH as $month){
          if(strrpos($str,$month)){
             return $month;
           }
        }
  }



  function getToDate($str,$monthToFind){
      $date = "";
      if(strrpos(strtolower($str),strtolower($monthToFind)) &&  $date=="")
          $date= substr($str,strrpos(strtolower($str),strtolower($monthToFind))-5,5);
        return preg_replace('/[^0-9.]+/', '', $date);
    }

 

 function searchForId($id, $array) {
   foreach ($array as $key => $val) {
     if ($val['COTTAGE_URL'] === $id) {
       return $key;
     }
   }
   return null;
 }




####################################################################################################################
####################################################################################################################
####################################################################################################################
####################################################################################################################
    
 $originalPrice ="";
 $discountedPrice="";
 $calcPercentage="";
 $discountAmount="";
 $percentage = "";
 $i = 0;
 $blacklist = array( );
 
 $url = "http://www.coastandcountry.co.uk/cottage-details/";

 scraperwiki::attach("special_offers_coast_and_country_summary_delete");
 # get an array of the cottage data to scrape
 $cottData = scraperwiki::select("COTTAGE_URL from 'special_offers_coast_and_country_summary_delete'.SWDATA order by COTTAGE_URL");

 $placeholder = scraperwiki::get_var("cottID");

 if($placeholder != ""){
   $index = searchForId($placeholder ,$cottData);
   $cottData = array_splice($cottData,$index);  
 }

 require 'scraperwiki/simple_html_dom.php';
 $dom = new simple_html_dom();

 foreach($cottData as $value){

    scraperwiki::save_var("cottID",$value['COTTAGE_URL']);
    // check the cottage url against the blacklist
    foreach($blacklist as $blItem){
      if($value['COTTAGE_URL'] == $blItem)
        continue 2;
    }

    //load the page into the scraper
    $html = scraperWiki::scrape($url.$value['COTTAGE_URL']);
#$html = scraperWiki::scrape("http://www.coastandcountry.co.uk/cottage-details/21DART");

    $dom->load($html);
    $feature = "";
    $image = "";
    $imgURL = "";
    $xtraFeatures = "";

   $specialOffer = "";
   date_default_timezone_set('GMT');
   $date = new DateTime();
   ########################################
   #         get the special offer        #
   ########################################
   foreach($dom->find('div[id=property-specialoffers-box-content]') as $specialOffer){
    
     $dateFrom = "";
     $dateTo = "";
     $duration = "";
     $originalPrice = "";
     $discountedPrice = "";
     $percentage = "";  
     $discountAmount = "";

     $OK = true;
     $specialOffer = $specialOffer->plaintext;
echo "\nspecialOffer ".$specialOffer;
     $valid = isOffer($specialOffer);
     if($valid){        
        # break the text down into the deals available
       
       # $ok = false;
        $str = $specialOffer;
            
        // functions to parse the special offer string
        #### deal with price ####

        // initialise variables
        $discountAmount = "";
        $originalPrice = "";
        $calcPercentage = "";
                
        // Get discounted price
       $discountedPrice =  getDiscountedPrice($str);  
echo " discountedPrice ".$discountedPrice;
        // Get discount in £
        $discountAmount = getDiscountAmount($str);
echo "\ndiscountAmount ".$discountAmount;

        // get discount percentage
        $percentageArr = parseTextAround($specialOffer,"%");
        if(isset($percentageArr[0]))
           $percentage = $percentageArr[0];
        elseif($discountedPrice != ""){
           $originalPrice = $discountedPrice + $discountAmount;  
           $percentage= calcPercentage($discountAmount,$originalPrice);
        }
echo  "\n percentage ".$percentage;        
        // get original price from discount percentage and discounted price
         $originalPrice = $discountedPrice +  $discountAmount;


        if($originalPrice <=$discountedPrice || $discountedPrice == 0){
           $originalPrice= getOriginalPrice($specialOffer);
           if($originalPrice == "" || $originalPrice < $discountedPrice || $originalPrice == $discountedPrice || $originalPrice == $discountAmount)
              $originalPrice = calcOriginalPrice($discountedPrice,$percentage);
        }


echo "\n originalPrice ".$originalPrice;
                   
        # now fill in any missing values
        $discountAmount =  $originalPrice - $discountedPrice;
 
        if($discountedPrice == "")
        $discountedPrice = $originalPrice - $discountAmount;
           
echo "\n discountedPrice ".$discountedPrice;


        if($percentage == ""){
          // get discount percentage
          $percentageArr = parseTextAround($specialOffer,"%");
          if(isset($percentageArr[0]))
             $percentage = $percentageArr[0];
          elseif( $discountedPrice!=""){
             $originalPrice = $discountedPrice + $discountAmount;  
             $percentage= calcPercentage($discountAmount,$originalPrice);
           }
        }

echo "\n percentage ".$percentage;
                
        if($originalPrice == "")
           $originalPrice = calcOriginalPrice($discountedPrice,$percentage );
echo "\n originalPrice ".$originalPrice;      
echo "\n";


echo "foo";
echo "\n";
echo "bar";
echo "\n";

         
        #### deal with dates ####
        $getToMonth = getToMonth($specialOffer);  
        $getToDate= getToDate($specialOffer,$getToMonth);  

        $getToDate = getDaysinMonth($getToMonth);
        $dateTo = date('Y-m-d',  strtotime($getToDate." ".$getToMonth));  


        $getFromMonth = getFromMonth($specialOffer);
        $getFromDate= getFromDate($specialOffer,$getFromMonth);
        
        if($getFromDate == "")// the whole month is on offer as there is no set date
           $getFromDate = "1st";
        $dateFrom = date('Y-m-d',  strtotime($getFromDate." ".$getFromMonth));   
echo "start";                      
echo $dateFrom;
echo $dateTo;

        // Update dateFrom to saturdays date
        $tmptime = strtotime($getFromDate." ".$getFromMonth);

        $tme =  mktime(0, 0, 0, date('m',$tmptime),date('d',$tmptime),date('Y',$tmptime));

      
        echo date("D",$tme);

        while(date("D",$tme) != "Sat"){
            $tme =  mktime(0, 0, 0, date('m',$tmptime),date('d',$tmptime)+1,date('Y',$tmptime));
            echo date("D",$tme);
        }
        echo date("D",$tme);
    


        $datetime1 = new DateTime($dateFrom);
        $datetime2 = new DateTime($dateTo);
        $interval = $datetime1->diff($datetime2);
        $interval =  $interval->format('%a');

        if($interval == 0){
           $dealLength = 1;
        }else{
           $dealLength = ($interval / 7)+1;
        }
        $dealLength = intval($dealLength) ;
        # limit the deal length to 10 weeks
        if($dealLength > 10)
            $dealLength = 10;   
       
# need to increment the date to show
        for($j=0; $j<$dealLength; $j++){
           $currEndDate = date('Y-m-d',strtotime('+1 week',strtotime($dateFrom)));
            
           $id++;
           $originalPrice = round($originalPrice,0,PHP_ROUND_HALF_DOWN);
           $discountAmount = round($discountAmount,0,PHP_ROUND_HALF_DOWN);
          
           $duration = abs(strtotime($currEndDate) - strtotime($dateFrom ));
           $duration = $duration / (60*60*24);  // should result in 7
                   








           /*  Get the Data  */
           // get Cottage name
           $element = $dom->find('title');
           $text = $element[0]->plaintext;
           $cottageName = substr($text,0, strpos($text,"-"));
           $features = "";





           
           # rules for exceptions
           if($originalPrice == 0) $OK = 0;
           if($percentage == 0) $OK = 0;
           if($discountAmount == 0) $OK = 0;
           if($discountedPrice == 0) $OK = 0;
           if($duration == 0) $OK = 0;
           if(!$dateFrom == 0) $OK = 0;
           if(!$dateTo == 0) $OK = 0;
           if($discountAmount >= $originalPrice) $OK = 0;
          #if($discountAmount >= $discountedPrice) $OK = false;
            

                /*  save the data  */

           if($OK === 0){

                print_r("Not OK");
                try {
                    $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
                    $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
                   # format Date correctly for database
                    $dateFrom = date_create_from_format('jS F Y',$dateFrom);
                    $dateTo =   date_create_from_format('jS F Y',$dateTo);
                } catch (Exception $e) {
                   $dateFrom = "";
                   $dateTo = "";
                }


                 $record = array(
                      'ID'                => $i,
                      'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
                      'DATE_FROM'         => $dateFrom->format('Y-m-d'),
                      'DATE_TO'           => $dateTo->format('Y-m-d'),
                      'DURATION'          => $duration,
                      'WAS_PRICE'         => $originalPrice,
                      'NOW_PRICE'         => $discountedPrice,
                      'DISCOUNT_PERCENT'  => $percentage,
                      'DISCOUNT_AMOUNT'   => $discountAmount,
                      'Text'              => $specialOffer ,
                      'TIMESTAMP'         => $date->getTimestamp(),
                      'RTYPE'             => 'T',
                   );
                 $i++;
                 scraperwiki::save(array('ID'), $record);

           }else{

                try {
                    $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
                    $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
                   # format Date correctly for database
                    $dateFrom = date_create_from_format('jS F Y',$dateFrom);
                    $dateTo =   date_create_from_format('jS F Y',$dateTo);
                } catch (Exception $e) {
                   $dateFrom = "";
                   $dateTo = "";
                }
                $record = array(
                         'ID'                => $i,
                         'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
                         'DATE_FROM'         => $dateFrom->format('Y-m-d'),
                         'DATE_TO'           => $dateTo->format('Y-m-d'),
                         'DURATION'          => $duration,
                         'WAS_PRICE'         => $originalPrice,
                         'NOW_PRICE'         => $discountedPrice,
                         'DISCOUNT_PERCENT'  => $percentage,
                         'DISCOUNT_AMOUNT'   => $discountAmount,
                         'Text'              => $specialOffer ,
                         'TIMESTAMP'         => $date->getTimestamp(),
                         'RTYPE'             => 'P',
                 );



                 $i++;
                 scraperwiki::save(array('ID'), $record);     

           }
          
        }

       }else{ // if not valid
           try {
             $dateFrom = date('jS  F Y',strtotime('+1 week',strtotime($dateFrom)));
             $dateTo = date('jS  F Y',strtotime('+1 week',strtotime($dateTo)));
             # format Date correctly for database
             $dateFrom = date_create_from_format('jS F Y',$dateFrom);
             $dateTo =   date_create_from_format('jS F Y',$dateTo);
           } catch (Exception $e) {
              $dateFrom = "";
              $dateTo = "";
           }
           $record = array(
              'ID'                => $i,
              'COTTAGE_LINK'      => trim($url.$value['COTTAGE_URL']),
              'DATE_FROM'         => $dateFrom->format('Y-m-d'),
              'DATE_TO'           => $dateTo->format('Y-m-d'),
              'DURATION'          => $duration,
              'WAS_PRICE'         => $originalPrice,
              'NOW_PRICE'         => $discountedPrice,
              'DISCOUNT_PERCENT'  => $percentage,
              'DISCOUNT_AMOUNT'   => $discountAmount,
              'Text'              => $specialOffer ,
              'TIMESTAMP'         => $date->getTimestamp(),
              'RTYPE'             => 'T',
           );


           $i++;
           scraperwiki::save(array('ID'), $record);

}

}
}
?>
