<?php
class Parse_captcha
{
	function run($captcha_path, $sample_path, $str_num = 1, $target_color = '')
	{
		//Get Captcha Sample Info
		if($target_color == '')
		{
			$db_text = $sample_path . '/db_global.txt';
		}
		else
		{
			$db_text = $sample_path . '/db_' . $target_color . '.txt';
		}
		if(file_exists($db_text))
		{
			$db_file 		= fopen($db_text, 'r');
			$captcha_lib 	= fread($db_file, filesize($db_text));
			$captcha_crack 	= unserialize($captcha_lib);
		}
		else
		{
			$list_sample = scandir($sample_path);
			$index = 0;
			foreach($list_sample as $key => $c)
			{
				$str_explode = explode('.', $c);
				$ext 		= $str_explode[1];

				if($c == '.' || $c == '..')
				{
					continue;
				}
				elseif($ext == 'txt')
				{
					continue;
				}
				$captcha_sample_path 	= "$sample_path/$c";
				$captcha_crack[$index] 	= $this->get_info_sample_captcha($captcha_sample_path, $target_color);
				$index++;
			}
			$db_file = fopen($db_text, 'w');
			fwrite($db_file, serialize($captcha_crack));
		}

		//Get Captcha to cracked
		$captcha_get 	= imagecreatefrompng($captcha_path);
		$captcha_size	= getimagesize($captcha_path);
		$captcha_width	= $captcha_size[0];
		$captcha_height	= $captcha_size[1];


		//Run as captcha width
		$result = '';
		$string = 1;

		for($cw = 0; $cw < $captcha_width; $cw++)
		{
			//Run as captcha height
			for($ch = 0; $ch < $captcha_height; $ch++)
			{
				$xxx = 0;
				//Run Sample and get per pixel
				foreach($captcha_crack as $key => $c)
				{
					$target_width 	= $c['width'] + $cw;
					$target_height	= $c['height'] + $ch;

					if($target_width > $captcha_width)
					{
						continue;
					}
					if($target_height > $captcha_height)
					{
						continue;
					}

					$try_captcha 	= array();
					$cor_x = 0;
					for($x = $cw; $x < $target_width; $x++)
					{
						$cor_y = 0;
						for($y = $ch; $y < $target_height; $y++)
						{
							$rgb 	= @imagecolorat($captcha_get, $x, $y);
							$colors = @imagecolorsforindex($captcha_get, $rgb);
							if(!empty($colors))
							{
								$colors_res = $colors['red'] . $colors['green'] . $colors['blue'];
								if(!empty($target_color))
								{
									if($target_color != $colors_res)
									{
										$cor_y++;
										continue;
									}
								}
								$try_captcha[] = array('color'	=> $colors_res,
										  			  'x'		=> $cor_x,
										  			  'y'		=> $cor_y,
										  			  'r_x'		=> $x,
										  			  'r_y'		=> $y);
							}
							$cor_y++;
						}
						$cor_x++;
					}

					if(!empty($try_captcha))
					{
						$coordinate_captcha_count 	= count($try_captcha);
						$coordinate_sample_count	= count($c['color_coordinat']);
						$ketemu = 0;
						if($coordinate_sample_count == $coordinate_captcha_count)
						{

							foreach ($try_captcha as $kex => $x)
							{
								if($x['color']	== $c['color_coordinat'][$kex]['color'] &&
								   $x['x'] 		== $c['color_coordinat'][$kex]['x'] &&
								   $x['y'] 		== $c['color_coordinat'][$kex]['y'])
								{
									$ketemu++;
								}
							}
						}

						if($ketemu == $coordinate_sample_count)
						{
							$result .= $c['string'];
							$xxx 	= 1;
							$string++;
							break;
						}
					}
				}

				if($xxx == 1)
				{
					$cw 	+= $c['width'];
					break;
				}

				if(count($result) == $str_num)
				{
					break;
				}
			}
			if(count($result) == $str_num)
			{
				break;
			}
		}
		return $result;
	}

	function get_info_sample_captcha($file, $target_color = '')
	{
		$c = basename($file);
		$str_explode = explode('.', $c);

		$captcha_sample_get 	= imagecreatefrompng($file);
		$captcha_sample_size	= getimagesize($file);
		$captcha_sample_width	= $captcha_sample_size[0];
		$captcha_sample_height	= $captcha_sample_size[1];

		$no 	= $str_explode[0];
		if(strpos($no, '_') > 0);
		{
			$str_explode = explode('_', $no);
			$no = $no[0];
		}

		$captcha_crack['string']			= $no;
		$captcha_crack['width']				= $captcha_sample_width;
		$captcha_crack['height']			= $captcha_sample_height;
		$captcha_crack['color_coordinat']	= array();

		for($sw = 0; $sw < $captcha_sample_width; $sw++)
		{
			$i = 0;
			for($sh = 0; $sh < $captcha_sample_height; $sh++)
			{
				$rgb 	= @imagecolorat($captcha_sample_get, $sw, $sh);
				$colors = @imagecolorsforindex($captcha_sample_get, $rgb);
				if(!empty($colors))
				{
					$colors_res = $colors['red'] . $colors['green'] . $colors['blue'];
					if(!empty($target_color))
					{
						if($target_color != $colors_res)
						{
							continue;
						}
					}
					$captcha_crack['color_coordinat'][] = array('color'	=> $colors_res,
														  		'x'		=> $sw,
														  		'y'		=> $sh);
				}
			}
		}
		return $captcha_crack;
	}
}
