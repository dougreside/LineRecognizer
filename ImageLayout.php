<?php
ini_set("memory_limit", "512M");
class imageOCR {
	public $image;
	public $threshold = 4000000;
	private $top = 220;
	private $left = 300;
	private $bottom = 1800;
	private $right = 1450;
	private $minDotsPerRow = 25;
	private $minLineHeight = 20;
	private $numCols = 1;
	private $numRows = 1;
	private $width = 0;
	private $height = 0;
	private $scalewidth = null;
	private $scaleheight = null;
	private $imgArray = array ();
	public $targetsize = 38;
	public $rowDots = array ();
	public $lines = array ();
	public $gap = 30;
	public $mdpr = 5; // min dots per row;
	public function showImage() {
		imagejpeg($this->image);
	}
	public function configureOCR($args) {
		$this->threshold = isset ($args['thresh']) ? $args['thresh'] : $this->threshold;
		$this->top = isset ($args['top']) ? $args['top'] : $this->top;
		$this->left = isset ($args['left']) ? $args['left'] : $this->left;
		$this->bottom = isset ($args['bottom']) ? $args['bottom'] : $this->bottom;
		$this->right = isset ($args['right']) ? $args['right'] : $this->right;
		$this->minDotsPerRow = isset ($args['mindots']) ? $args['mindots'] : $this->minDotsPerRow;
		$this->minLineHeight = isset ($args['minline']) ? $args['minline'] : $this->minLineHeight;
		$this->numCols = isset ($args['cols']) ? $args['cols'] : $this->numCols;
		$this->numRows = isset ($args['rows']) ? $args['rows'] : $this->numRows;
		$this->scalewidth = isset ($args['scalewidth']) ? $args['scalewidth'] : null;
		$this->scaleheight = isset ($args['scaleheight']) ? $args['scaleheight'] : null;
	}
	
	public function binarize() {
		#imagefilter($this->image,IMG_FILTER_GRAYSCALE);
		for ($y = $this->top; $y < $this->bottom; $y++) {

			$this->rowDots[$y] = 0;
			// iterate through y axis
			for ($x = $this->left; $x < $this->right; $x++) {

				// look at current pixel
				$pixel_color = imagecolorat($this->image, $x, $y);

				$r = ($pixel_color >> 16) & 0xFF;
				$g = ($pixel_color >> 8) & 0xFF;
            	$b = $pixel_color & 0xFF;
            	$avg = ($r+$g+$b)/3;
            	
				// test threshold 8500000
				if (($avg <= $this->threshold) && ($avg >= 0)) {
					imagesetpixel($this->image, $x, $y, imagecolorexact($this->image, 0, 0, 0));
					$this->rowDots[$y]++;

				} else {
					imagesetpixel($this->image, $x, $y, imagecolorexact($this->image, 255, 255, 255));

				}

			}
			if (($this->mdpr>0)&&($this->rowDots[$y]<$this->mdpr)){ 
				$this->rowDots[$y]=0;		
			}
		}
		#var_dump($this->rowDots);

	}

	public function displayLines($lines) {
	

		if ($this->image) {
			$width = $this->right - $this->left;

			#for ($y = $this->top; $y < $this->bottom; $y++) {
			for ($y = 0; $y < count($lines); $y++) {
				
					imageline($this->image, $this->left, $lines[$y], $this->left + $width, $lines[$y], imagecolorexact($this->image, 255, 0, 0));

				
				
			}

		}
	}
	
	public function printLines($lines) {

		
		$ys = "";
		foreach ($lines as $key=>$val){
			$ys = $ys .",". $val;		
		}
		$ys = substr($ys,1);
		 $ys= "{'lines':[".$ys."]}";
		echo $ys;
	}

	public function close() {
		//destroy image and all variables
		imagedestroy($this->image);
		$this->threshold = null;
		$this->top = null;
		$this->left = null;
		$this->bottom = null;
		$this->right = null;
		$this->minDotsPerRow = null;
		$this->minLineHeight = null;
		$this->numCols = null;
		$this->numRows = null;
		$this->width = null;
		$this->height = null;
	}
	public function LoadImg($imgname, $thresh, $region) {
		//get image dimensions
		$this->threshold = $thresh;
		$this->top = $region["top"];
		$this->left = $region["left"];
		$this->right = $region["right"];
		$this->bottom = $region["bottom"];
		$info = getimagesize($imgname);
		$this->mime = $info['mime'];
		$this->width = $info[0];
		$this->height = $info[1];

		//calculate necessary memory
		/*
		$required_mem=Round($this->width*$this->height*$info['bits'])+100000;
		$new_limit=memory_get_usage()+$required_mem;
		
		*/
		/* Attempt to open */
		switch ($this->mime) {
			case 'image/png' :
				$this->image = imagecreatefrompng($imgname);
				break;
			case 'image/jpeg' :
				$this->image = imagecreatefromjpeg($imgname);
				break;
			case 'image/bmp' :
				$this->image = imagecreatefromwbmp($imgname);
				break;
			case 'image/gif' :
				$this->image = imagecreatefromgif($imgname);
				break;
		}

		if (!$this->image) { /* See if it failed */
			$this->image = imagecreatetruecolor(150, 30); /* Create a black image */
			$bgc = imagecolorallocate($this->image, 255, 255, 255);
			$tc = imagecolorallocate($this->image, 0, 0, 0);
			imagefilledrectangle($this->image, 0, 0, 150, 30, $bgc);
			/* Output an errmsg */
			imagestring($this->image, 1, 5, 5, "Error loading $imgname", $tc);
		}

	}


	public function getMins(){
		ksort($this->rowDots);
		$redlines = array();
		$last = null;
		$plast = null;
		$mins = array();
		foreach ($this->rowDots as $key=>$value){
			if (isset($last)){
				
				
				if (isset($plast)){
				
				$pdiff = $last["value"]-$plast["value"];
				$ndiff = $last["value"]-$value;
				
				#echo $last["key"]." : ". $last["value"]." | ".$pdiff." \/ ".$ndiff;
						
				if (($pdiff>0)&&($ndiff>0)){
				#	echo " SAVED";
					$mkey = $last["key"];
					$mval = $last["value"];
					$mins[$mkey]=$mval;
					#$mins[]=$mkey;
				}	
				}
				$plast = $last;
				#echo "<br/>";
		}
			
			$last = array("key"=>$key,"value"=>$value);
			
			
		}
		asort($mins);
		
	
	
		$lastKey = 0;
		$redlines = array ();
		foreach ($mins as $key => $value) {
			if (count($redlines) >= $this->targetsize) {
				break;
			} else {
				$found = false;
				for ($i = 0; $i < count($redlines); $i++) {
					if ($key < $redlines[$i]) {
						if (($redlines[$i] - $key) < $this->gap) {
							if ($value < $mins[$redlines[$i]]) {
								$redlines[$i] = $key;
							}
						} else{
							if (($i>0)&&(($key - $redlines[$i - 1]) < $this->gap)) {
								if ($value < $mins[$redlines[$i -1]]) {
									$redlines[$i -1] = $key;
								}
							} else {
								array_splice($redlines, $i, 0, $key);

							}
						}	
						$found = true;

					}
				}
				if (!$found) {
					$i = count($redlines) - 1;
					if (($i>0)&&(($key - $redlines[$i]) < $this->gap)) {
						if ($value < $mins[$redlines[$i]]) {
							$redlines[$i -1] = $key;
						}
					} else {

						$redlines[] = $key;
					}
				}
			}
		
		}
		
		
		
		
		
		return $redlines;
		
	}
	public function getLines() {

		asort($this->rowDots);
		$lastKey = 0;
		$redlines = array ();
		foreach ($this->rowDots as $key => $value) {
			if (count($redlines) >= $this->targetsize) {
				break;
			} else {
				$found = false;
				for ($i = 0; $i < count($redlines); $i++) {
					if ($key < $redlines[$i]) {
						if (($redlines[$i] - $key) < $this->gap) {
							if ($value < $this->rowDots[$redlines[$i]]) {
								$redlines[$i] = $key;
							}
						} else
							if (($i>0)&&(($key - $redlines[$i - 1]) < $this->gap)) {
								if ($value < $this->rowDots[$redlines[$i -1]]) {
									$redlines[$i -1] = $key;
								}
							} else {
								array_splice($redlines, $i, 0, $key);

							}
						$found = true;

					}
				}
				if (!$found) {
					$i = count($redlines) - 1;
					if (($i>0)&&(($key - $redlines[$i]) < $this->gap)) {
						if ($value < $this->rowDots[$redlines[$i]]) {
							$redlines[$i -1] = $key;
						}
					} else {

						$redlines[] = $key;
					}
				}
			}
		
		}
		return $redlines;
	}
		private function getWords($image, $hrlines, $left, $right, $colsPerSpace, $minDotsPerCol, $ratioW, $ratioH, $getOrgL, $getOrgT) {
			$boxes = array ();
			for ($i = 1; $i < count($hrlines); $i++) {
				$inWord = 0;
				$blanklines = 0;
				$vtlines = array ();
				$vtlines[] = $this->left;

				for ($x = $this->left; $x < $this->right; $x++) {
					$blackdots = 0;
					for ($y = $hrlines[($i -1)]; $y < $hrlines[$i]; $y++) {

						$pixel_color = imagecolorat($this->image, $x, $y);
						if ($pixel_color == 0) {
							$blackdots = $blackdots +1;
							$lastBlackDot = $x;
						}
					}

					if ($blackdots < $this->minDotsPerCol) {
						$blanklines = $blanklines +1;
						//colsPerSpace originially 5
						if (($inWord == 1) && ($blanklines > $colsPerSpace)) {

							imageline($this->image, $x, $hrlines[($i -1)], $x, $hrlines[$i], 0);
							$vtlines[] = $x;
							$blanklines = 0;
							$inWord = 0;
						}

					} else {
						$inWord = 1;
						$blanklines = 0;
					}
				}
				for ($j = 1; $j < count($vtlines); $j++) {
					$box = array (
						(($vtlines[($j -1)] / $ratioH) - $getOrgL),
						 (($hrlines[($i -1)] / $ratioW) - $getOrgT),
						 ($vtlines[$j] / $ratioH),
						 ($hrlines[$i] / $ratioW)
					);

					$boxes[] = $box;

				}
			}

			return $boxes;
		}
	}
?>