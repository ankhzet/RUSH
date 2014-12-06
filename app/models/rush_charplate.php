<?php
	$_SERVER[DOCUMENT_ROOT] = dirname(dirname(__FILE__));
	$w     = 160;
	$h     = 64;

	$id = intval($_REQUEST['id']);

	if (!$id) {
		require_once 'rush.php';
		$id = intval(RUSH::oGet('char'));
	}

	if ($id <= 0) {
		$img   = ImageCreateTrueColor($w, $h);
		$logo = @ImageCreateFromPNG($_SERVER[DOCUMENT_ROOT] . '/theme/img/logo.png');
		ImageCopyResampled($img, $logo, 0, 0, 0, 0, $w, $h, ImageSX($logo), ImageSY($logo));
		out($img, 'logo');
		die();
	}

	function out($img, $filename) {
		header('Content-disposition: inline; filename=/theme/img/' . $filename . '.png');
		header('content-type: image/png');
		header('cache-control: no-cache');
		header('cache-control: max-age=1');
		imagepng($img);
	}

	set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER[DOCUMENT_ROOT] . '/_engine');

	require_once 'dbengine.php';
	require_once 'rush_charachter.php';

	$char = Char::get($id);
	if (!$char->id)
		die();


	$font  = $_SERVER[DOCUMENT_ROOT] . '/theme/img/friztrus.ttf';
	$fontSize = 12 + rand(0, 5);

	$img   = ImageCreateTrueColor($w, $h);
	$black = ImageColorAllocate($img, 0, 0, 0);
	$back  = ImageColorAllocate($img, 255, 205, 94);
	$white = ImageColorAllocate($img, 255, 255, 255);
	$green = ImageColorAllocate($img,   0, 255,   0);
	$blue  = ImageColorAllocate($img,   0,   0, 255);
	$maroon= ImageColorAllocate($img, 200,   0, 255);

	ImageFill($img, 0, 0, $back);
//      if ($noize) ImageCopyResampled($img, $noize, 0, 0, 0, 0, 64, 64, ImageSX($noize), ImageSY($noize));
	ImageRectangle($img, 0, 0, $w - 1, $h - 1, $white);

	$ptx = 60;
	$portrait = @ImageCreateFromGif($_SERVER[DOCUMENT_ROOT] . '/theme/img/icons/race/' . $char->pget('race') . '.gif');
	ImageCopyResampled($img, $portrait, 2, 2, 0, 0, 64 - 4, 64 - 4, 64, 64);

	$spec = @ImageCreateFromPNG($_SERVER[DOCUMENT_ROOT] . '/theme/img/icons/spec/' . $char->pget('spec') . '.png');
	ImageCopyResampled($img, $spec, 62, 4, 0, 0, 18, 16, ImageSX($spec), ImageSY($spec));

	function pushText($fontSize, $text, $x, $y, $center = true) {
		global $img, $font, $w, $h, $black, $white;
		if ($center) {
			$i = ImageTTFBBox($fontSize, 0, $font, $text);
			$tw = $i[2] - $i[0];
			$tx = $x + ($w - $x - $tw) / 2;
		} else
			$tx = $x;
		$ty = $y + $fontSize;
		ImageTTFText($img, $fontSize, 0, $tx + 1, $ty + 1, $black, $font, $text);
		ImageTTFText($img, $fontSize, 0, $tx + 1, $ty + 1, $black, $font, $text);
		ImageTTFText($img, $fontSize, 0, $tx    , $ty, $white, $font, $text);
		ImageTTFText($img, $fontSize, 0, $tx    , $ty, $white, $font, $text);
	}
	$plate = @ImageCreateFromPNG($_SERVER[DOCUMENT_ROOT] . '/theme/img/plate.png');
	$px = ImageSX($plate);
	$py = ImageSY($plate);

	function statPlate($stat, $max, $y, $ph, $color) {
		global $img, $w, $black, $plate, $px, $py, $ptx;
		if ($stat > $max) $stat = $max;
		$denom = $stat / ($max ? $max : 1);
		$iw = $w - 7 - $ptx;
		$ww = $iw * $denom;
		$fw = $iw - $ww;
		ImageFilledRectangle($img, 3 + $ptx + 1, $y + 1, 2 + $ww + $ptx + 1, $y + $ph - 2, $color);
		if ($stat < $max)
		ImageFilledRectangle($img, 3 + $ptx + 1 + $ww, $y + 1, $w - 4, $y + $ph - 2, $black);

		ImageCopyResampled($img, $plate, 2 + $ptx + 1      , $y, 0       , 0, 12, $ph, 12, $py);
		ImageCopyResampled($img, $plate, 14 + $ptx + 1     , $y, 12      , 0, $w - 28 - $ptx - 1, $ph, $px - 24, $py);
		ImageCopyResampled($img, $plate, $w - 14, $y, $px - 12, 0, 12, $ph, 12, $py);

		$text = normValue($stat) . '/' . normValue($max);
		$fs = 6;
		pushText($fs, $text, $ptx, $y + ($ph - $fs) / 2);
	}

	pushText(8, $char->nick . ' [' . $char->level . ']', 82, 20 - 9, false);
	statPlate($char->exp, Char::levelToExp($char->level), 52, 10, $maroon);
	statPlate($char->pget('curmp'), $char->pget('maxmp'), 39, 14, $blue);
	statPlate($char->pget('curhp'), $char->pget('maxhp'), 22, 18, $green);

	$filename = 'char' . $id;
	out($img, $filename);
?>
