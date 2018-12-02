<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// include database and object files
include_once '../config/database.php';
include_once '../objects/product.php';
 
// instantiate database and product object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$product = new Product($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

//Bin Packing offline best fit algorith
function binPacking($items, $size, $n) {
        
$res = 0; 

// Create an array to store remaining space in bins 
// there can be at most n bins 
$bin_rem = array(); 
$arr = array();


for ($i=0; $i<$n; $i++) 
{ 
    // Find the first bin that can accommodate 
    // $items[$i]["price"] 
    for ($j=0; $j<$res; $j++) 
    { 
        if ($bin_rem[$j] >= $items[$i]["price"] ) 
        { 
            $bin_rem[$j] = $bin_rem[$j] - $items[$i]["price"] ; 
            $arr[$j][] = $items[$i];
            break; 
        } 
    } 

    // If no bin could accommodate $items[$i]["price"]  
    if ($j==$res) 
    { 

        $bin_rem[$res] = $size - $items[$i]["price"] ;
        $arr[$j][] = $items[$i];
        $res++; 
       
    } 
}
return $arr;

}

function getCouriorChargers($weights) {
    if($weights > 0 && $weights <= 200) {
        return 5;
    }
    else if($weights > 200 && $weights <= 500) {
        return 10;
    }
    else if($weights > 500 && $weights <= 1000) {
        return 15;
    }
    else if($weights > 1000 && $weights <= 5000) {
        return 20;
    }
    else {
        return 0;
    }
    
}

function getPackageInfo($buckets) {
    $packageInfo=array();
    $packageInfo["records"]=array(); 
    foreach ($buckets as $key => $package) {
        $totalprice = 0;
        $totalweight = 0;
        $itemname = array();
        foreach ($package as $i => $items) {
            $itemname[] = $items["name"];
            $totalprice += $items["price"];
            $totalweight += $items["weight"];
        }
        $ccharge = getCouriorChargers($totalweight);
        sort($itemname);
        $strItems = implode(",", $itemname);
        $package = array(
            "pid" => $key,
            "pktname" => "Package ". ($key+1),
            "items" => $strItems,
            "totalweight" => $totalweight,
            "totalprice" => $totalprice,
            "ccharge" => $ccharge
        );
        array_push($packageInfo["records"], $package);
    }
   
    return $packageInfo;

}
 
// make sure data is not empty
if(
    !empty($data->listids)
){
    $product->listids = $data->listids;
 
// query products
$stmt = $product->readByIds();
$num = $stmt->rowCount();
 
// check if more than 0 record found
if($num>0){
 
    // products array
    $products_arr=array();
    $products_arr["records"]=array();
 
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
 
        $product_item=array(
            "id" => $id,
            "name" => $name,
            "price" => $price,
            "weight" => $weight
        );
 
        array_push($products_arr["records"], $product_item);
    }
    // set response code - 200 OK
    
    http_response_code(200);


    $n = count($products_arr["records"]);
    $a = $products_arr["records"];
    $size = 250;
   $buckets = binPacking($a, $size, $n);
   $packagesinfos = getPackageInfo($buckets);

 
    // show products data in json format
    echo json_encode($packagesinfos);
}
else{
 
    // set response code - 404 Not found
    http_response_code(404);

    echo json_encode(
        array("message" => "No products found.")
    );
}
}
else{
 
    // set response code - 400 bad request
    http_response_code(400);
 
    // tell the user
    echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
}
?>