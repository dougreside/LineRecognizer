<?php 

include "ImageLayout.php";
include "BinaryImage.php";

set_time_limit ( 1000 );

$as = $_GET["as"];
$url = $_GET["url"];
$top = $_GET["t"];
$bottom = $_GET["b"];
$left = $_GET["l"];
$right = $_GET["r"];
$threshold = $_GET["thresh"];
$lh = $_GET["lh"];
$numLines = $_GET["lnum"];

if (isset($url)){

if (!(isset($top))){
$top = 200;
}
if (!(isset($bottom))){
$bottom = 1900;
}
if (!(isset($right))){
$right = 1500;
}
if (!(isset($left))){
$left = 500;
}
if (!(isset($threshold))){
$threshold = 90;
}
if (!(isset($lh))){
$lh = 20;
}
if (!(isset($lnum))){
$lnum = 38;
}




$iocr = new imageOCR();
$dims = array("top"=>$top,"bottom"=>$bottom,"right"=>$right,"left"=>$left);
$iocr->LoadImg($url,$threshold,$dims);

$targetsize = $lnum;
$gap = $lh;

if ($as=="d"){
$iocr->printLines();
}
else{

$iocr->threshold=70;
$iocr->binarize();
header('Content-type: image/jpeg');
$redlines = $iocr->getLines();
$iocr->displayLines($redlines);
$iocr->showImage();
}
}
else{
	echo "You must supply a url to an image in the url paramater [url=ham.jpg].";
}
?>