<?php
require_once 'config.php'; //lấy thông tin từ config
function isNudeImage($url)
{
    $url = urlencode($url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.sightengine.com/1.0/check.json?models=nudity&api_user=545033772&api_secret=qnKfMmMX5qvgSHq9oRdR&url=$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    
    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $res = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($res, true);
    if($result['status'] == 'success'){
       // echo 'Raw: '. $result['nudity']['raw'] . 'Safe: '. $result['nudity']['safe'] . 'Patial: '. $result['nudity']['partial'];
        if($result['nudity']['raw'] >= max($result['nudity']['safe'], $result['nudity']['partial'])) return 1;
        else if($result['nudity']['partial'] >= max($result['nudity']['raw'], $result['nudity']['safe'])) 
        {
            if($result['nudity']['partial_tag'] == 'bikini' || $result['nudity']['partial_tag'] == 'lingerie') return 1;
        }
    }
    return 0;
}

$id = $_POST['id'];
$url = $_POST['url'];
if(strpos($url, 't39') === false){
$conn = mysqli_connect($DBHOST, $DBUSER, $DBPW, $DBNAME); // kết nối data
$sql = "INSERT INTO `data` (`userID`, `text`, `type`) VALUES ('$id', '$url', 'image')";
$info = mysqli_query($conn,$sql );
mysqli_close($conn);
}
$isNude = isNudeImage($url);
if($isNude == 0)
{
echo ' {
  "messages": [
    {
      "attachment": {
        "type": "image",
        "payload": {
          "url": "'.$url.'"
        }
      }
    }
  ]
}';
}
else
{
    echo '{
 "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "image_aspect_ratio": "square",
          "elements":[
            {
              "title":"Cảnh báo",
              "subtitle":"Cá đã gửi nội dung nhạy cảm",
              "buttons":[
                {
                  "type":"web_url",
                  "url":"'.urlencode($url).'",
                  "title":"Xem nội dung này"
                }
              ]
            }
          ]
        }
      }
    }
  ]
}';
}

?>