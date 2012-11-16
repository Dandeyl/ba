<?php
// CAPTCHA Bypass Code (Works 99%)
// by Napalm
// 
// License: <a href="http://creativecommons.org/licenses/by-sa/2.0/uk/" class="bbc_url" title="External link" rel="nofollow external">http://creativecommo...s/by-sa/2.0/uk/</a>
//  
// CAPTCHA Location: <a href="http://www.xovie.us/captcha/captcha.php" class="bbc_url" title="External link" rel="nofollow external">http://www.xovie.us/...cha/captcha.php</a>
//

function  imageconverttruecolor(&$image)
{ 
	$width = imagesx($image); 
	$height = imagesy($image);    
	$final = imagecreatetruecolor($width, $height); 
	imagecopy($final, $image, 0, 0, 0, 0, $width, $height); 
	imagedestroy($image);   $image = $final;
}

// load images

$background = imagecreatefrompng("captcha.png");
$lettersimg = imagecreatefrompng("letters.png");
$captcha = imagecreatefrompng("http://www.xovie.us/captcha/captcha.php");
imageconverttruecolor($background);imageconverttruecolor($lettersimg);
imageconverttruecolor($captcha);
// first parse - difference test and pixel save
$pixels = array();
for($x = 0; $x < imagesx($captcha); $x++)
{    
	for($y = 0; $y < imagesy($captcha); $y++)
	{       
		$src = imagecolorat($background, $x, $y);       
		$dst = imagecolorat($captcha, $x, $y);      
		if(!($src == 0 && $dst == 0) && ($src == $dst))          
		$pixels[$x][$y] = 0xFFFFFF;        
		else $pixels[$x][$y] = 0x000000; 	   
	}
}
imagedestroy($background);

// second parse - filter out pixel noise

for($p = 0; $p < 3; $p++)
{ 
	// 3 noise parses 
	for($x = 0; $x <  imagesx($captcha); $x++)
	{       
		for($y = 0; $y < imagesy($captcha); $y++)
		{           
			$filterpixel = true;            
			if(!$pixels[$x-1][$y-1] && !$pixels[$x+0][$y-1] && !$pixels[$x+1][$y-1]) 
			$filterpixel = false; 
			// top           
			if(!$pixels[$x+1][$y-1] && !$pixels[$x+1][$y+0] && !$pixels[$x+1][$y+1]) 
			$filterpixel = false; 
			// right         
			if(!$pixels[$x-1][$y+1] && !$pixels[$x+0][$y+1] && !$pixels[$x+1][$y+1]) 
			$filterpixel = false; 
			// bottom            
			if(!$pixels[$x-1][$y-1] && !$pixels[$x-1][$y+0] && !$pixels[$x-1][$y+1]) 
			$filterpixel = false; 
			// left          
			if($filterpixel) $pixels[$x][$y] = 0xFFFFFF;        
		}   
	}
}
for($x = 0; $x < imagesx($captcha); $x++)
{ 
	$pixels[$x][0] = 0xFFFFFF;  
	$pixels[$x][1] = 0xFFFFFF;  
	$pixels[$x][imagesy($captcha) - 1] = 0xFFFFFF;  
	$pixels[$x][imagesy($captcha) - 2] = 0xFFFFFF;
}
for($y = 0; $y < imagesy($captcha); $y++)
{    
	$pixels[0][$y] = 0xFFFFFF;  $pixels[1][$y] = 0xFFFFFF;  
	$pixels[imagesx($captcha) - 1][$y] = 0xFFFFFF;  
	$pixels[imagesx($captcha) - 2][$y] = 0xFFFFFF;
}
for($x = 50; $x < 54; $x++)
{  
	for($y = 2; $y < 5; $y++)
	{       
		$pixels[$x][$y] = 0xFFFFFF; 
	}
}
// third parse - rebuild image (just for show)
for($x = 0; $x < imagesx($captcha); $x++)
{   
	for($y = 0; $y < imagesy($captcha); $y++)
	{       
		imagesetpixel($captcha, $x, $y, 0xFFFFFF);      
		if(($x % 3) == 0 && ($y % 3) == 0 && $pixels[$x][$y] == 0)
		{         
			imagesetpixel($captcha, $x, $y, 0xFF0000);      
		}   
	}
}
// fourth parse - basic ocr (slow) :-(
$letters = 'ABCE'; 
$ocr = '';
for($x1 = 0; $x1 < imagesx($captcha) - 25; $x1++)
{  
	for($y1 = 0; $y1 < imagesy($captcha) - 27; $y1++)
	{       
		$score = array();       
		for($letter = 0; $letter < 4; $letter++)
		{            
			for($x2 = 0; $x2 <  25; $x2++)
			{              
				for($y2 = 0; $y2 < 27; $y2++)
				{                   
					$color = imagecolorat($lettersimg, ($letter * 25) + $x2, $y2);                  
					if($pixels[$x1 + $x2][$y1 + $y2] == $color && $color == 0) 
					$score[$letter][0]++;                    
					if($color == 0) $score[$letter][1]++;               
				}           
			}       
		}       
		$found = -1; $highest = 0;      
		for($letter = 0; $letter < 4; $letter++)
		{            
			$result = (($score[$letter][1] / 100) * 90);           
			if($score[$letter][0] >= $result)
			{               
				$result = ($score[$letter][0] - $result);               
				if($result > $highest)
				{                  
					$highest = $result;                 
					$found = $letter;               
				}           
			}       
		}       
		if($found > -1)
		{         
			$ocr .= $letters[$found];           
			$x1  += 10;     
		}       
		unset($score);  
	}
}
imagedestroy($lettersimg);unset($pixels);
// last of all output 
header('Content-Type: image/png');
imagestring($captcha, 2, 0, 0, $ocr, 0);
imagepng($captcha);
imagedestroy($captcha);
?>