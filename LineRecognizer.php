<?php 

include "ImageLayout.php";
set_time_limit ( 1000 );
$as = $_GET["ct"];
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

if ($as=="j"){
$iocr->printLines();
}
else if ($as="i"){

$iocr->threshold=70;
$iocr->binarize();
header('Content-type: image/jpeg');
$redlines = $iocr->getLines();
$iocr->displayLines($redlines);
$iocr->showImage();
}
}
else{
	echo "<HTML><HEAD><TITLE>Line recognizer instructions</TITLE></HEAD><BODY><div><div>To use the image recognizer GET the following parameters:</div><br/>".
	"<div>ct: [i|j] return image (i) or json [j] </div>".
	"<div>url:  url of image</div>".
	"<div>t: top of region rectangle</div>".
	"<div>b: bottom of region rectangle</div>".
	"<div>l: left of region rectangle</div>".
	"<div>r: right of region rectangle</div>".
	"<div>thresh: greyscale pixel color threshold for turning white or black</div>".
	"<div>lh: minimum line height</div>".
	"<div>lnum: number of lines in transcript</div><br/>".
	"<div>example: http://PATH/lineRecognizer.php?ct=i&url:./ham.jpg&t=200&b=1900&l=500&r=1500&lh=20&lnum=38</div>";
	"<div>code is at http://www.github.com/dougreside/LineRecognizer</div>".
	"</div></BODY></HTML>";
}
?>