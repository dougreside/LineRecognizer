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
$lnum = $_GET["lnum"];
$alg = $_GET["alg"];
$mdpr = $_GET["mdpr"];
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
if (!(isset($alg))){
$alg = "simple";
}
if (!(isset($mdpr))){
$mdpr = 0;
}


$iocr = new imageOCR();
$dims = array("top"=>$top,"bottom"=>$bottom,"right"=>$right,"left"=>$left);
$iocr->LoadImg($url,$threshold,$dims);
$iocr->targetsize = $lnum;
$iocr->mdpr = $mdpr;  
$targetsize = $lnum;
$gap = $lh;

$iocr->threshold=$threshold;
$iocr->binarize();
if ($alg=="min"){
$redlines = $iocr->getMins();
}
else if ($alg=="simple"){
$redlines = $iocr->getLines();
}
if ($as=="j"){
	
$iocr->printLines($redlines);
}
else if ($as="i"){

header('Content-type: image/jpeg');
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
	"<div>thresh: greyscale pixel color threshold for turning white or black [0-255]</div>".
	"<div>lh: minimum line height</div>".
	"<div>lnum: number of lines in transcript</div>".

	"<div>example: http://PATH/lineRecognizer.php?ct=i&url=./ham.jpg&t=200&b=1900&l=500&r=1500&lh=20&lnum=38</div>";
		"<hr/>".
		"<div>Experimental:</div>".
		"<div>alg: algorithm [simple|min] Simple algorithm or one based on local mins</div>".
		"<div>mdpr: minimum dots per row (if there are fewer black dots in a row than the given value, the entire line will be read a blank)</div>".
	"<div>code is at http://www.github.com/dougreside/LineRecognizer</div>".
	"</div></BODY></HTML>";
}
?>