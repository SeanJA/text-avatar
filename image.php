<?php
$username = 'seanja';

if (!file_exists('cache')) {
    mkdir('cache', $mode = 0777);
}

if (!file_exists('cache')) {
    die('couldn\'t make a cache folder, make one yourself called "cache" in the same dir as this file.');
}

//get the latest ten statuses for $user (cache the results... twitter rate limited me while I was playing with it...)
if (file_exists('cache/' . md5($username) . '.json')) {
    $statuses = file_get_contents('cache/' . md5($username) . '.json');
} else {
    $statuses = file_get_contents('https://api.twitter.com/1/statuses/user_timeline.json?include_rts=true&screen_name=' . $username . '&count=10');
    if ($statuses) {
        file_put_contents('cache/' . md5($username) . '.json', $statuses);
    }
}

if (!$statuses) {
    die('hrm... something went wrong.');
}
$statuses = json_decode($statuses);

$user = $statuses[0]->user;

//var_dump($statuses);

$text = '';

foreach ($statuses as $s) {
    $text .= $s->text;
}

$image = $user->profile_image_url;

/**
* generates a image with chars instead of pixels
*
* @param string $url Filepath or url
* @param string $chars The chars which should replace the pixels
* @param int $shrpns Sharpness (2 = every second pixel, 1 = every pixel ... )
* @param int $size
* @param int $weight font-weight/size
* @return resource
*/
function pixels_to_text($url, $chars = 'you should have entered something here...', $shrpns = 1, $size = 4, $weight = 2) {
    list($w, $h, $type) = getimagesize($url);
    $resource = imagecreatefromstring(file_get_contents($url));
    $img = imagecreatetruecolor($w * $size, $h * $size);

    $cc = strlen($chars);
    for ($y = 0; $y < $h; $y+=$shrpns) {
        for ($x = 0; $x < $w; $x+=$shrpns) {
            imagestring($img, $weight, $x * $size, $y * $size, $chars{@++$p % $cc}, imagecolorat($resource, $x, $y));
        }
    }
    return $img;
}

imagepng(pixels_to_text($image, $text, $shrpns = 1, $size = 6, $weight = 1), 'cache/' . md5($username) . '.png');
?>


<img src="cache/<?php echo md5($username); ?>.png" />