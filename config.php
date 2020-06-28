<?php
//this file runs when the pos says: PLEASE WAIT
include '../driverssql.php';


$merchant = $_REQUEST['merchant'];
$pos_name = $_REQUEST['name'];

$pos_name = str_replace(":", "", $pos_name);

$amount = 0;
$block = 0;
$has_serial = 0;
$closed = 0;
//search for root,halfpay of the merchant
$result = mysqli_prepare($con, "SELECT child,halfpay,root,MerchantSetGifts FROM diafimisi where ID=?; ");
mysqli_stmt_bind_param($result, 'i', $merchant);
mysqli_stmt_execute($result);
mysqli_stmt_bind_result($result, $child, $has_amount, $closed, $serial);
while (mysqli_stmt_fetch($result)) {
    $umbrela = $child;
    $amount = $has_amount;
    $block = $closed;
    $has_serial = $serial;
}
mysqli_stmt_close($result);


//find the support phone of the business from the umbrella

$phone = "0";
$result = mysqli_prepare($con, "SELECT phone FROM diafimisi where ID=?; ");
mysqli_stmt_bind_param($result, 'i', $umbrela);
mysqli_stmt_execute($result);
mysqli_stmt_bind_result($result, $myphone);
while (mysqli_stmt_fetch($result)) {
    $phone = $myphone;

}
mysqli_stmt_close($result);

//find if the business has serial
$result = mysqli_prepare($con, "SELECT id FROM pontoi_proiontwn where custom=1 and id_etairias=? and onoma_proiontos='Default'; ");
mysqli_stmt_bind_param($result, 'i', $etairia);
mysqli_stmt_execute($result);
mysqli_stmt_bind_result($result, $pid);
while (mysqli_stmt_fetch($result)) {
    $product_id = $pid;
}
mysqli_stmt_close($result);


$nam = $pos_name; //prepare pos name for sql
$exists = 0;
$only_redemption = 0;
//check if pos is blocked from RoadCube at pos_stats
$result = mysqli_prepare($con, "SELECT block,only_redemption FROM pos_stats where POS_name=? ; ");
mysqli_stmt_bind_param($result, 's', $nam);
mysqli_stmt_execute($result);
mysqli_stmt_bind_result($result, $blk, $red);
while (mysqli_stmt_fetch($result)) {
    $exists = 1;
    $only_redemption = $red;
    if ($blk > 0)
        $block = 2;
}
mysqli_stmt_close($result);

//write stats about the pos
if ($exists == 0) { //insert in pos_stats

    $sql2 = "INSERT INTO `pos_stats`(`companyID`,`has_amount`,`support_phone`,`POS_name`) VALUES (?,?, ?,?) ; ";

    // echo"INSERT INTO `pos_stats`(`companyID`,`has_amount`,`support_phone`,`POS_name`) VALUES ($merchant,$amount,$phone,$nam) ; ";
    $result2 = mysqli_prepare($con, $sql2);

    mysqli_stmt_bind_param($result2, 'iiss', $merchant, $amount, $phone, $nam);

    mysqli_stmt_execute($result2);

    mysqli_stmt_close($result2);
} else//update pos_stats
{
    $sql2 = "UPDATE `pos_stats` SET last_call = now() WHERE companyID=? ;";

    $result2 = mysqli_prepare($con, $sql2);

    mysqli_stmt_bind_param($result2, 'i', $merchant);

    mysqli_stmt_execute($result2);

    mysqli_stmt_close($result2);
}

//send settings to pos

//echo "{\"only_serial\":" . $has_serial . ",\"has_amount\":" . $amount . ",\"support\":" . $phone . ",\"closed\":" . $closed . ",\"only_redemption\":" . $only_redemption . "}"; //closed=0 means not pos closed

echo json_encode(array(
    "only_serial" => "$has_serial",
    "has_amount" => "$amount",
    "support" => "$phone",
    "pos_enabled" => "$closed",
    "only_redemption" => "$only_redemption"
));
    
