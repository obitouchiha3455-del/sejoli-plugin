<?php
namespace Mutasi\Bank;

class BRI
{
	private $day            = 7;
	private $username       = '';
	private $password       = '';
	private $account        = 0;
	private $type           = '';
    private $date_format = 'Y-m-d';

    private $ch;
    private $cookie;
    private $useragent;

	public $table     = '';
	public $respond	  = [
		'valid'		=> false,
		'messages'	=> [],
		'data'		=> []
	];

	public $file = [];

	/**
	 * INITIALIZATION
	 */
	function __construct()
	{
		$this->create_files();

		return $this;
	}

    /**
	 * Create needed files to store dat
	 * @return void
	 */
	protected function create_files()
	{
        $upload_dir = wp_upload_dir();

		$this->file = [
			'cookie'	=> $upload_dir['basedir'] . '/cookie-bri.txt',
		];

		foreach((array) $this->file as $key => $file) :
			if(!file_exists($file)) :
				fopen($file,'w');
				chmod($file,0600);
			endif;
		endforeach;
	}

	/**
	 * Set number date
	 * @param integer $date;
	 */
	public function set_day($day)
	{
		$day       = (int) $day;
		$this->day = ($day > 30) ? 30 : $day;

		return $this;
	}

	/**
	 * Set username
	 * @param string $username [username of BCA Klikbank]
	 */
	public function set_username($username)
	{
		try {
			if('' === $username) :
				throw new \Exception('Username is empty');
			else :
				$this->username = $username;
			endif;
		}

		catch(\Exception $e) {
			echo $e->getMessage();
		}

		return $this;
	}

	/**
	 * Set password
	 * @param string password [password of BCA Klikbank]
	 */
	public function set_password($password)
	{
		try {
			if('' === $password) :
				throw new \Exception('Password is empty');
			else :
				$this->password = $password;
			endif;
		}

		catch(\Exception $e) {
			echo $e->getMessage();
		}

		return $this;
	}

	/**
	 * Set account ID
	 * @param integer $account account ID
	 */
	public function set_account($account)
	{
		$this->account 	=  preg_replace("/[^a-zA-Z0-9]+/", "", $account);
		return $this;
	}

	/**
	 * Set transfer type data
	 * @param string $type [only if debete or kredit]
	 */
	public function set_type($type)
	{
		$this->type 	= (in_array($type,['debet','kredit'])) ? $type : '';
		return $this;
	}

	/**
	 * Set credential
	 * @param [type] $username [description]
	 * @param [type] $password [description]
	 */
	public function set_credential($username,$password,$account)
	{
		$this
			->set_username($username)
			->set_password($password)
            ->set_account($account);

		return $this;
	}

	/**
	 * Get date from
	 * @return date
	 */
	protected function get_date_from($format = '')
	{
        $format  = ('' === $format) ? $this->date_format : $format;

		return date($format,strtotime('-'.$this->day.' day'));
	}

	/**
	 * Get date to
	 * @return date
	 */
	protected function get_date_to($format = '')
	{
        $format  = ('' === $format) ? $this->date_format : $format;
		return date($format);
	}

    /**
     * Filter date format
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    protected function date_filter($date)
    {
        $bulan  = [
                    'Jan'   => '01',
                    'Feb'   => '02',
                    'Mar'   => '03',
                    'Apr'   => '04',
                    'Mei'   => '05',
                    'Jun'   => '06',
                    'Jul'   => '07',
                    'Agu'   => '08',
                    'Aug'   => '08',
                    'Sep'   => '09',
                    'Oct'   => '10',
                    'Nov'   => '11',
                    'Dec'   => '12'
                ];

        $exp    = explode('-', $date);
        $date    = $exp[0] . '/';
        $date    .= ($bulan[$exp[1]]) . '/';
        $date    .= $exp[2];
        return $date;
    }

    protected function number_filter($string)
    {
        $string = safe_str_replace(array('.', ','), '', $string);
        $string = substr($string, 0, strlen($string) - 2);
        return $string;
    }

    /**
     * Scrape data between given start and end
     * @param  [type] $data  [description]
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    protected function scrape_get_between($data,$start,$end)
    {
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
        return $data;   // Returning the scraped data from the function
    }

    /**
     * Get the index page
     * @return [type] [description]
     */
    protected function get_index($ch)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://ib.bri.co.id/ib-bri');
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		$proses 	= curl_exec($ch);

		if(!empty($proses)) :
			$csrf 	= $this->scrape_get_between($proses, 'name="csrf_token_newib" value="', '"');
			$result	= ['csrf' => $csrf];
			return $result;
		endif;

		return NULL;
    }

    /**
     * Get captcah data
     * @return [type] [description]
     */
    protected function get_captcha($ch)
    {
        require_once(dirname(dirname(__FILE__)).'/class-parse-captcha.php');

        $try_again = true;
		$captcha_crack   = '';
		$try             = 0;
        $storage   = dirname(__FILE__).'/storage';
        $sample    = dirname(__FILE__).'/sample-captcha';

		while($try_again) :

			if($try > 10) :
				$try_again = false;
			endif;

			curl_setopt($ch, CURLOPT_URL, 'https://ib.bri.co.id/ib-bri/login/captcha');
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
			curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_VERBOSE, true);

			$proses 	= curl_exec($ch);

			if(!empty($proses)) :

				$fp = fopen($storage.'/captcha.png', "w");

				fwrite($fp, $proses);
				fclose($fp);
				$parse_captcha 	= new \Parse_captcha();
				$captcha_crack 	= $parse_captcha->run($storage.'/captcha.png',$sample, 4, '000');

				if(strlen($captcha_crack) < 4) :
					$try_again = true;
					$try++;
				else :
					$try_again = false;
				endif;
			else :
				$try++;
			endif;
		endwhile;

		if(empty($captcha_crack)) :
			curl_close($ch);
			exit;
		else :
			return $captcha_crack;
		endif;
    }

    /**
     * Login
     * @return [type] [description]
     */
    protected function get_login($ch,$captcha,$index_data)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://ib.bri.co.id/ib-bri/Homepage.html');
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		$login_data		= [
            'csrf_token_newib'	=> $index_data['csrf'],
			'j_username'		=> $this->username,
			'j_plain_username'	=> $this->username,
			'j_password'		=> $this->password,
			'j_plain_password'	=> '',
			'preventAutoPass'	=> '',
			'j_language'		=> 'in_ID',
			'j_code'			=> $captcha,
        ];

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($login_data));
		$proses 	= curl_exec($ch);

		if(empty($proses)) :
			curl_close($ch);
		endif;
    }

    protected function get_logout($ch)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://ib.bri.co.id/ib-bri/Logout.html');
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		$proses 	= curl_exec($ch);

		if(empty($proses)) :
			curl_close($ch);
		endif;
    }

    /**
     * Get mutasi data
     * @return [type] [description]
     */
    protected function get_mutasi($ch,$index_data)
    {
		curl_setopt($ch, CURLOPT_URL, 'https://ib.bri.co.id/ib-bri/Br11600d.html');
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_VERBOSE, TRUE);

		$login_data	= [
            'csrf_token_newib'	=> $index_data['csrf'],
			'FROM_DATE' 		=> $this->get_date_from(),
			'TO_DATE'			=> $this->get_date_to(),
			'download'			=> '',
			'ACCOUNT_NO'		=> $this->account,
			'VIEW_TYPE'			=> '2',
			'DDAY1'				=> $this->get_date_from('d'),
			'DMON1'				=> $this->get_date_from('m'),
			'DYEAR1'			=> $this->get_date_from('Y'),
			'DDAY2'				=> $this->get_date_to('d'),
			'DMON2'				=> $this->get_date_to('m'),
			'DYEAR2'			=> $this->get_date_to('Y'),
			'MONTH'				=> $this->get_date_to('m'),
			'YEAR'				=> $this->get_date_to('Y'),
			'submitButton'		=> 'Tampilkan'
        ];

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($login_data));

		$proses 	= curl_exec($ch);
		$result = [];
		if(!empty($proses)) :

            $result 		= [];
			$content 		= $proses;
			$table_content 	= $this->scrape_get_between($content, '<table id="tabel-saldo" class="box" style="width: 97%; margin: 0 auto;">', '</table>');
			$table_content	= preg_replace('/\s+/', ' ', $table_content);
			$tbody_content	= $this->scrape_get_between($table_content, '<tbody>', '</tbody>');
			$tr_content		= explode('</tr>', $tbody_content);

			for($i = 1; $i < count($tr_content) - 4; $i++) :

				$c = $tr_content[$i];

				$tgl_mutasi 		= $this->scrape_get_between($c, '<td style="text-align: left;">', '</td>');
				$parse_tgl = explode('/', $tgl_mutasi);

				$tgl_mutasi			= date('d/m/Y', strtotime($parse_tgl[0] . '-' . $parse_tgl[1] . '-' . date('Y')));
				$keterangan_mutasi	= $this->scrape_get_between($c, 'id=desc-' . $i . ' style="text-align: left;">', '</br></td>');
				$debet				= $this->scrape_get_between($c, $keterangan_mutasi . '</br></td> <td style="text-align: right;">', '</td>');
				$kredit				= $this->scrape_get_between($c, $debet . '</td> <td style="text-align: right;">', '</td>');

				if(empty($kredit)) :
					$saldo 		= $this->scrape_get_between($c, $debet . '</td> <td style="text-align: right"></td> <td style="text-align: right;">', '</td>');
				else :
					$saldo 		= $this->scrape_get_between($c, $kredit . '</td> <td style="text-align: right;">', '</td>');
                endif;

				$type 	= (empty($debet)) ? 'CR' : 'DB';

				if(
					'' === $this->type ||
					('debet' === $this->type && 'DB' === $type) ||
					('kredit' === $this->type && 'CR' === $type)
				) :
					$result[] 	= [
	                    'waktu' => $tgl_mutasi,
						'keterangan'           => $keterangan_mutasi,
						'jenis'                => $type,
						'jumlah'               => empty($debet) ? $this->number_filter($kredit) : $this->number_filter($debet)
					];
				endif;
	        endfor;
		endif;
		return $result;
    }

	/**
	 * Check mutasi
	 * @return void
	 */
	public function check_mutasi()
	{
        require_once('simple_html_dom_parser.php');
        @ini_set('max_execution_time', 0);

		$username  = $this->username;
		$password  = $this->password;
		$account   = $this->account;

        if(empty($username) || empty($password) || empty($account)) :
            exit();
        endif;

        $ch           = curl_init();
        $mutasi       = [];
        $this->cookie = realpath($this->file['cookie']);

		$this->useragent = 'Mozilla/5.0 (Windows NT 10.0; rv:46.0) Gecko/20100101 Firefox/46.0';
		$get_index       = $this->get_index($ch);

		if(!empty($get_index)) :
            $get_logout  = $this->get_logout($ch);
			$get_captcha = $this->get_captcha($ch);
			$get_login   = $this->get_login($ch,$get_captcha, $get_index);
			$mutasi      = $this->get_mutasi($ch,$get_index);
			$get_logout  = $this->get_logout($ch);
		endif;

        curl_close($ch);

		if(0 < count($mutasi)) :

			$this->respond['valid']     = true;
			$this->respond['messages'][] = 'Data BRI found.';
			$this->respond['data']      = $mutasi;

		else :

			$this->respond['valid']     = false;
			$this->respond['messages'][] = __('Tidak ditemukan data. Kemungkinan server hosting anda tidak bisa terkoneksi dengan server BNI', 'sejoli');
			$this->respond['data']      = array();

		endif;

		return $this;
	}

	/**
	 * Return respond from this class
	 * @return json
	 */
	public function respond()
	{
		return  $this->respond;
	}

	/**
	 * Return respond as json data
	 */
	public function toJson()
	{
		header('Content-Type: application/json');
		echo json_encode($this->respond);
	}
}
