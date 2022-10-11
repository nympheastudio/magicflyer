<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <style>

		body {margin: 0}

		a {max-width:100%; position:relative; display: inline-block; float: left}

				.bloc-instagram {
				  width:350px; 
				  display: inline-block; 
				  margin: 5px;
					max-width:100%;
					position: relative
				}
				.twitter-rwdgang {font-family: Arial,sans-serif;border-bottom: 1px solid #e9e9e9;padding: 10px 0;font-size: 11px;color: #777}
				.img-instagram {
				  width: 100%;
					display: inline-block;
					position: relative;
				  float: left; 
				}

				.img-instagram img {width: 100%}

				.text-instagram {
					position: relative;
					z-index: 999;
					font-size: 14px;
					font-family: Arial,sans-serif;
					position: absolute;
					height:80%;
					overflow: hidden;
					background: rgba(0,0,0,0.5);
					margin: 5%;
					padding: 5%;
					color: #fff;
					max-width: 80%;
				}
				.bloc-instagram .text-instagram {
					visibility: hidden; opacity: 0
				}

				.bloc-instagram:hover .text-instagram {opacity: 1; visibility: visible}
				@media (max-width: 380px) {
					.bloc-instagram {  width: 100%;margin: 0 }
		}

    
    
    
    </style>
</head>

<body><?php
ini_set('display_errors',1);
//date_default_timezone_set('Europe/London');
//session_start();

$compteur_de_news = 0;

//
// INSTAGRAM
//token généré via http://instagram.pixelunion.net
$otk = '1096624979.1677ed0.b95e9fc4cacc4d639a6365822b81be35';

$url = 'https://api.instagram.com/v1/users/self/media/recent/?count=100&access_token='.$otk;
//$url = 'insta.json';
//echo $url;
$insta = file_get_contents("$url" , true);
$json = json_decode($insta, true);

foreach ($json['data'] as $item) {
    $compteur_de_news++;
	
	/*$in_feed[] = array(
        "source" => "instagram",
        "date" => isset($item['created_time']) ? $item['created_time'] : NULL,
        "main_text" => isset($item['caption']['text']) ? $item['caption']['text'] : NULL,
        "large_image" => isset($item['images']['standard_resolution']['url']) ? $item['images']['standard_resolution']['url'] : NULL,
        "thumbnail" => isset($item['images']['thumbnail']['url']) ? $item['images']['thumbnail']['url'] : NULL,
        "link" => isset($item['link']) ? $item['link'] : NULL,
        "likes" => isset($item['comments']['count']) ? $item['likes']['count'] : NULL,
        "comments" => isset($item['comments']['count']) ? $item['comments']['count'] : NULL,
        "video" => isset($item['videos']['standard_resolution']['url']) ? $item['videos']['standard_resolution']['url'] : NULL,
        "type" => isset($item['videos']) ? "video" : "photo",
    );*/
	$html_.= '<a href="'.$item['link'].'" target="_blank"><div class="bloc-instagram"><div class="img-instagram" ><img style="  object-fit: cover;  width:350px;  height:350px;" src="' .$item['images']['standard_resolution']['url'].'" /></div><div class="text-instagram">' . $item['caption']['text'] . '</div></div></a>';
	//var_dump($item);
}

echo $html_;

?></body></html>
