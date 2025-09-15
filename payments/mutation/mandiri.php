<?php

namespace Mutasi\Bank;

class Mandiri
{
	private $day      = 30;
	private $username = '';
	private $password = '';
	private $account  = 0;
	private $type 	  = '';

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
			'cookie'	=> $upload_dir['basedir'] . '/cookie-mandiri.txt',
			'request'	=> $upload_dir['basedir'] . '/request-mandiri.txt',
		];

		foreach((array) $this->file as $key => $file) :
			if(!file_exists($file)) :
				fopen($file,'w');
				chmod($file,0600);
			endif;
		endforeach;
	}

	/**
	 * Write content to selected file
	 * @param  string $file    key of file from $this->file
	 * @param  string $content the content to written
	 * @return void
	 */
	protected function write_to_files($file,$content)
	{

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
				throw new \Exception(__('Username is empty','aib'));
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
				throw new \Exception(__('Password is empty','aib'));
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
     * Set mandiri account
     * @param   string  $account
     */
	public function set_account($account)
	{
		$this->account 	= $account;
		return $this;
	}

    /**
     * Set mutation type
     * @param   string  $type
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
	public function set_credential($username,$password)
	{
		$this
			->set_username($username)
			->set_password($password);

		return $this;
	}

	/**
	 * Get date from
	 * @return string date with Y-m-d format
	 */
	protected function get_date_from()
	{
		return date('Y-m-d',strtotime('-'.$this->day.' day'));
	}

	/**
	 * Get date to
	 * @return string date with Y-m-d format
	 */
	protected function get_date_to()
	{
		return date('Y-m-d');
	}

	/**
	 * CHECK MANDIRI
	 * @return [type] [description]
	 */
	public function check_mutasi()
	{
        require_once('simple_html_dom_parser.php');
		set_time_limit(300);

    	$user 	= $this->username;
    	$pwd 	= $this->password;

    	$date1 	= explode('-', $this->get_date_from()); //  Tangal pengecekan dari
    	$date2 	= explode('-', $this->get_date_to()); // tanggal pengecekan ke

    	$curlHandle = curl_init();

    	chmod($this->file['request'],0755);
    	chmod($this->file['cookie'], 0755);

    	$f = fopen($this->file['request'], 'w');
    	$cookie = $this->file['cookie'];

    	curl_setopt($curlHandle, CURLOPT_COOKIEJAR, $cookie);
    	curl_setopt($curlHandle, CURLOPT_COOKIEFILE, $cookie);
    	curl_setopt($curlHandle, CURLOPT_USERAGENT, 'Mozilla/5.0 ( Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1 ) Gecko/20061204 Firefox/2.0.0.1');
    	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    	//curl_setopt($curlHandle, CURLOPT_SSL_CIPHER_LIST, "RC4-MD5");
    	curl_setopt($curlHandle, CURLOPT_ENCODING, 'gzip,deflate');
    	curl_setopt($curlHandle, CURLOPT_VERBOSE, true);
    	curl_setopt($curlHandle, CURLOPT_STDERR, $f);

    	curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
        	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        	'Host:ib.bankmandiri.co.id',
        	'Origin:https://ib.bankmandiri.co.id',
    	));



    	curl_setopt($curlHandle, CURLOPT_REFERER, 'http://www.bankmandiri.co.id/');
    	curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/Login.do?action=form&lang=in_ID');
    	$info = curl_exec($curlHandle);


    	$param = "action=result&userID=$user&password=$pwd&image.x=80&image.y=8";


    	curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $param);
    	curl_setopt($curlHandle, CURLOPT_POST, 1);
    	curl_setopt($curlHandle, CURLOPT_REFERER, 'https://ib.bankmandiri.co.id/retail/Login.do?action=form&lang=in_ID');
    	curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/Login.do');

    	$info = curl_exec($curlHandle);

  	  	curl_setopt($curlHandle, CURLOPT_POST, 0);
    	curl_setopt($curlHandle, CURLOPT_REFERER, 'https://ib.bankmandiri.co.id/retail/common/menu.jsp');
    	curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do?action=form');

    	$info = curl_exec($curlHandle);

    	$accountId = $this->account;


		$param = "action=result" .
			"&fromAccountID=$accountId" .
			"&searchType=R" .
			"&fromDay=" . $date1[2] .
			"&fromMonth=" . $date1[1] .
			"&fromYear=" . $date1[0] .
			"&toDay=" . $date2[2] .
			"&toMonth=" . $date2[1] .
			"&toYear=" . $date2[0] .
			"&sortType=Date" .
			"&orderBy=ASC";


		curl_setopt($curlHandle, CURLOPT_POST, 1);
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $param);
		curl_setopt($curlHandle, CURLOPT_REFERER, 'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do?action=form');
		curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do');

    	$info = curl_exec($curlHandle);

		curl_setopt($curlHandle, CURLOPT_REFERER, 'http://www.bankmandiri.co.id/');
		curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/Logout.do?action=result');
		$logout = curl_exec($curlHandle);

		$html 	= str_get_html($info);

		foreach($html->find('script') as $script) :
			$script->outertext = '';
		endforeach;

		$html->save();

    	$table = explode('<table border="0" cellpadding="2" cellspacing="1" width="100%">', $html);

		// MUTASI KOSONG
		if(!is_array($table) || !isset($table[1])) :
			$this->respond['valid']      = true;
			$this->respond['messages'][] = sprintf(__('No data found in account %s between %s and %s.','aib'),$this->account,$this->get_date_from(),$this->get_date_to());
			$this->respond['data']       = [];
			return $this;
		endif;

    	// $table2 = $table[2];
    	$table = $table[1];

		// $table2 = "<table>" . $table2;

    	$table = "<table>" . $table;


    	$html = str_get_html($table);

    	$data = $html->find('tbody tr[height=25]');

    	if ($data == null) {

    	}

    	$found = false;
    	$data_mutasi = array();

		foreach ($data as $el) :
			$note = $el->find('td', 1);

			if (NULL !== $note) :
				$note   = $el->find('td', 1)->plaintext;
				$date   = $el->find('td', 0)->plaintext;
				$note   = strip_tags($note);

				$debet  = $el->find('td', 2)->plaintext;

				$kredit = $el->find('td', 3)->plaintext;


				$kredit = safe_str_replace(".", "", $kredit);
				$kredit = explode(",", $kredit);
				$kredit = intval($kredit[0]);

				$debet  = safe_str_replace(".", "", $debet);
				$debet  = explode(",", $debet);
				$debet  = intval($debet[0]);
			endif;

			$type    = (0 === $debet) ? 'kredit' : 'debet';
			$nominal = (0 === $debet) ? $kredit : $debet;

			if('' === $this->type || $type === $this->type) :
				array_push($data_mutasi, array(
					'type'		=> $type,
					'date' 		=> $date,
					'note' 		=> $note,
					'nominal'	=> $nominal,
				));
			endif;


		endforeach;


    	$html->clear();
    	unset($html);

    	$data 	= array();

	    curl_close($curlHandle);
    	fclose($f);

    	chmod($this->file['request'],0600);
    	chmod($this->file['cookie'],0600);

		ksort($data_mutasi);

		$this->respond['valid']     = true;
		$this->respond['messages'][] = __('Data found.','aib');
		$this->respond['data']      = $data_mutasi;

		return $this;
	}

	/**
	 * Check list rekening
	 * @return [type] [description]
	 */
	public function check_account()
	{
        require_once('simple_html_dom_parser.php');
		set_time_limit(300);

		$user 	= $this->username;
    	$pwd 	= $this->password;

    	$date1 	= explode('-', $this->get_date_from()); //  Tangal pengecekan dari
    	$date2 	= explode('-', $this->get_date_to()); // tanggal pengecekan ke

    	$curlHandle = curl_init();

    	chmod($this->file['request'],0755);
    	chmod($this->file['cookie'],0755);

    	$f = fopen($this->file['request'], 'w');
    	$cookie = $this->file['cookie'];

    	curl_setopt($curlHandle, CURLOPT_COOKIEJAR, $cookie);
    	curl_setopt($curlHandle, CURLOPT_COOKIEFILE, $cookie);
    	curl_setopt($curlHandle, CURLOPT_USERAGENT, 'Mozilla/5.0 ( Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1 ) Gecko/20061204 Firefox/2.0.0.1');
    	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    	//curl_setopt($curlHandle, CURLOPT_SSL_CIPHER_LIST, "RC4-MD5");
    	curl_setopt($curlHandle, CURLOPT_ENCODING, 'gzip,deflate');
    	curl_setopt($curlHandle, CURLOPT_VERBOSE, true);
    	curl_setopt($curlHandle, CURLOPT_STDERR, $f);

    	curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
        	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        	'Host:ib.bankmandiri.co.id',
        	'Origin:https://ib.bankmandiri.co.id',
    	));



    	curl_setopt($curlHandle, CURLOPT_REFERER, 'http://www.bankmandiri.co.id/');
    	curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/Login.do?action=form&lang=in_ID');
    	$info = curl_exec($curlHandle);

    	$param = "action=result&userID=$user&password=$pwd&image.x=80&image.y=8";

    	curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $param);
    	curl_setopt($curlHandle, CURLOPT_POST, 1);
    	curl_setopt($curlHandle, CURLOPT_REFERER, 'https://ib.bankmandiri.co.id/retail/Login.do?action=form&lang=in_ID');
    	curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/Login.do');

    	$info = curl_exec($curlHandle);

  	  	curl_setopt($curlHandle, CURLOPT_POST, 0);
    	curl_setopt($curlHandle, CURLOPT_REFERER, 'https://ib.bankmandiri.co.id/retail/common/menu.jsp');
    	curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do?action=form');

    	$info = curl_exec($curlHandle);

    	$html = str_get_html($info);

		curl_setopt($curlHandle, CURLOPT_REFERER, 'http://www.bankmandiri.co.id/');
		curl_setopt($curlHandle, CURLOPT_URL, 'https://ib.bankmandiri.co.id/retail/Logout.do?action=result');
		$logout = curl_exec($curlHandle);

		curl_close($curlHandle);
    	fclose($f);

    	chmod($this->file['request'],0600);
    	chmod($this->file['cookie'],0600);

		$accounts 	= array();

		if(!empty($html) && $html->find("select[name=fromAccountID]",0)) :

	        $options = $html->find('select[name=fromAccountID]', 0)->find("option");

			foreach($options as $option) :
				if(!empty($option->value) && !empty($option->plaintext)) :
					$no_rekening              = preg_replace("/[^0-9]/", "", $option->plaintext);
					$accounts[$option->value] = $no_rekening;
				endif;

			endforeach;
		endif;

		if(0 < count($accounts)) :

			$this->respond['valid']      = true;
			$this->respond['messages'][] = __('Data found.','sejoli');
			$this->respond['data']       = $accounts;

		else :

			$this->respond['valid']     = false;
			$this->resond['messages'][] = __('Tidak ditemukan data. Kemungkinan server hosting anda tidak bisa terkoneksi dengan server BNI', 'sejoli');
			$this->respond['data']      = array();

		endif;

		return $this;
	}

	/**
	 * Return data to json format
	 * @return string in json format
	 */
	public function toJson()
	{
		header('Content-Type: application/json');
		echo json_encode($this->respond['data'],JSON_PRETTY_PRINT);
	}

	/**
	 * Return respond from this class
	 * @return json
	 */
	public function respond()
	{
		return  $this->respond;
	}
}

//require_once(plugin_dir_path(__FILE__) . "simple_html_dom.php");
