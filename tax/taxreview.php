<?php
include 'reader.php';
$excel = new Spreadsheet_Excel_Reader();

function PA($PAData) {
  $taxPA1 = $taxPA2 =$taxPA3 = $taxPA4 = $totalPA = "0";
  $ratePA1 = "0.02";
  $ratePA2 = "0.07";
  $ratePA3 = "0.12";
  $ratePA4 = "0.17";

  if ($PAData<="45000"){
    $taxPA1 = $PAData*$ratePA1;
  }elseif ($PAData<="90000") {
    $taxPA1 = "45000"*$ratePA1;
    $taxPA2 = ($PAData-"45000")*$ratePA2;
  }elseif ($PAData<="135000") {
    $taxPA1 = "45000"*$ratePA1;
    $taxPA2 = "45000"*$ratePA2;
    $taxPA3 = ($PAData-"90000")*$ratePA3;
  }elseif ($PAData>"135000") {
    $taxPA1 = "45000"*$ratePA1;
    $taxPA2 = "45000"*$ratePA2;
    $taxPA3 = "45000"*$ratePA3;
    $taxPA4 = ($PAData-"135000")*$ratePA4;
  }
  $totalPA = $taxPA1 + $taxPA2 + $taxPA3 + $taxPA4;
  return $totalPA;
}

$selfincome = $spouseincome = $totalincome="0";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$excel->read($_FILES["file"]["tmp_name"]);
$x=2;
while($x<=$excel->sheets[0]['numRows']) {
    $selfincome = isset($excel->sheets[0]['cells'][$x]['1']) ? $excel->sheets[0]['cells'][$x]['1'] : '';
    $spouseincome = isset($excel->sheets[0]['cells'][$x]['2']) ? $excel->sheets[0]['cells'][$x]['2'] : '';

      $totalincome = $selfincome+$spouseincome;

      //MPF
      $MPF = "85200";
      $MPFpercent = "0.05";
      $MPFover = "300000";
      $MPFoverpay = "15000";
      $selfmpf = $spousempf = $totalmpf = "0";

      if ($selfincome >= $MPF) {
        if ($selfincome >= $MPFover) {
          $selfmpf = $MPFoverpay;
        }else {
          $selfmpf = $selfincome * $MPFpercent;
        }
      }else{
        $selfmpf = "0";
      }

      if ($spouseincome >= $MPF) {
        if ($spouseincome >= $MPFover) {
          $spousempf = $MPFoverpay;
        }else{
          $spousempf = $spouseincome * $MPFpercent;
        }
      }else{
        $spousempf = "0";
      }

      $totalmpf=$selfmpf+$spousempf;
      //Net Total income
      $selfNTI = $spouseNTI = $totalNTI= "0";

      $selfNTI = $selfincome - $selfmpf;
      $spouseNTI = $spouseincome - $spousempf;
      $totalNTI = $selfNTI+$spouseNTI;
      //Allowances-HKGOV
      $basic1="132000";
      $basic2="264000";
      //Net Chargeable Income
      $NCI=$NCIof1=$NCIof2="0";
      $NCIof1 = $selfNTI-$basic1;
      $NCIof2 = $spouseNTI-$basic1;

      if ($NCIof1 <"0"){
       $NCIof1 = "0";
      }
      if ($NCIof2 <"0"){
        $NCIof2 = "0";
      }


      if ($spouseincome > "0"){
        $NCI=$totalNTI-$basic2;
      }else {
        $NCI=$totalNTI-$basic1;
      }
      if($NCI <"0"){
        $NCI = "0";
      }

      //Personal Assessment(PA)
      $PAData = $totalPAall = "0";

      $totalPAall = PA($NCIof1) + PA($NCIof2);

      //Tax Payable
      $selfTP = $spouseTP = $totalTP = "0";
      if (PA($NCIof1)>="20000"){
      $selfTP = PA($NCIof1)-"20000";
      }else{
        $selfTP = PA($NCIof1)*"0.75";
      }
      if (PA($NCIof2)>="20000"){
      $spouseTP = PA($NCIof2)-"20000";
      }else{
        $spouseTP = PA($NCIof2)*"0.75";
      }
      $totalTP = $selfTP + $spouseTP;
      $case = ($x-"1");

      echo "<h3>Case " . $case . "</h3><br>";
      echo "Your income is " . $selfincome . " and your wife or husband income is " . $spouseincome . ".";
      echo "<br>So, total income is " . $totalincome . ". <br><br>";

      echo "Can deduct the MPF(Self) is " . $selfmpf . " and deduct the MPF(Spouse) is " . $spousempf . ".";
      echo "<br>So, total MPF is " . $totalmpf . ".<br><br>";

      echo "Can deduct the Net Total Income(Self) is " . $selfNTI . " and deduct the Net Total Income(Spouse) is " . $spouseNTI . ".";
      echo "<br>So, total Net Total Income is " . $totalNTI . ".<br><br>";

      echo "Net Chargeable Income(Self) is " . $NCIof1 . " and Net Chargeable Income(Spouse) is " . $NCIof2 . ".<br>";
      echo "So, total Net Chargeable Income is " . $NCI. ".<br><br>";

      echo "Personal Assessment Tax(Self) is " . PA($NCIof1) . " and Personal Assessment Tax(Spouse) is " . PA($NCIof2) . ".<br>";
      echo "So, total Personal Assessment Tax(Personal) is " . $totalPAall . " and Joint Personal Assessment Tax " . PA($NCI).".<br><br>";

      echo "Tax Payable(Self) is " . $selfTP . " and Tax Payable(Spouse) is " . $spouseTP . ".<br>";
      echo "So, total Tax Payable is " . $totalTP . ".<br><br>";

      //Recommended to joint the tax?
      $JPA = PA($NCI);
      if ($JPA >= "20000"){
        $JPApable = $JPA-"20000";
      }else{
      $JPApable = $JPA*"0.75";
}

      if ($JPApable>=$totalTP){
        //echo $totalPAall . ">" . $totalTP;
          echo "Joint Assessment Recommednded is No.<br><br>";
      }else{
                //echo $totalPAall . "<" . $totalTP;
          echo "Joint Assessment Recommednded is Yes.<br><br>";
      }

  $x++;
        echo "Recalculate the Tax file -> <a href='index.php'> Here </a><br><br>";
  }
}




?>
