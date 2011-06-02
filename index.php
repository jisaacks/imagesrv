<?php

require_once('image_srv.php');

$is = new ImageSrv();

$is->load($_GET['img']);

$width = empty($_GET['width']) ? NULL : $_GET['width'];
$height = empty($_GET['height']) ? NULL : $_GET['height'];

if(!empty($_GET['background']))
{
    $colors = explode(',',$_GET['background']);
    if(is_array($colors) && count($colors) == 3)
    {
        $is->setRGB($colors[0],$colors[1],$colors[2]);	
    }
}

$is->setSize( $width, $height );

$is->output();

?>