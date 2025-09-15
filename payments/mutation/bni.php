<?php
namespace Mutasi\Bank;

class BNI
{
	private $day            = 30;
	private $username       = '';
	private $password       = '';
	private $account        = 0;
	private $type           = '';
    private $date_format = 'd-M-Y';

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
			'cookie'	=> $upload_dir['basedir'] . '/cookie-bni.txt',
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
	protected function get_date_from()
	{
		return date($this->date_format,strtotime('-'.$this->day.' day'));
	}

	/**
	 * Get date to
	 * @return date
	 */
	protected function get_date_to()
	{
		return date($this->date_format);
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
        $string = substr($string, 4, -3);
        $string = safe_str_replace('.', '', $string);
        return (int)$string;
    }

	/**
	 * Check mutasi
	 * @return void
	 */
	public function check_mutasi()
	{
        require_once('simple_html_dom_parser.php');
		set_time_limit(300);

        $invoices = [];
        $lunas    = [];

        $username = $this->username;
        $password = $this->password;
        $account  = $this->account;

        if(!$username || !$password || !$account) :
            exit();
        endif;

        $ua         = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.75 Safari/537.36";
        $cookie     = $this->file['cookie'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_URL, 'https://ibank.bni.co.id/MBAWeb/FMB;jsessionid=0000gsadMFnW4TJnYCFiblgmvcx:1a1li5jho?page=Thin_SignOnRetRq.xml&MBLocale=bh');
        $result = curl_exec($ch);

        require_once(dirname(dirname(__FILE__)).'/simple_html_dom_parser.php');


        $dom    = str_get_html($result);

        $form     = $dom->find('form', 0);

        $postdata = 'Num_Field_Err=%22Please+enter+digits+only%21%22&Mand_Field_Err=%22Mandatory+field+is+empty%21%22&CorpId=' .
                    urlencode($username) . '&PassWord=' . urlencode($password) .
                    '&__AUTHENTICATE__=Login&CancelPage=HomePage.xml&USER_TYPE=1&MBLocale=bh&language=bh&AUTHENTICATION_REQUEST=True&__JS_ENCRYPT_KEY__=&JavaScriptEnabled=N&deviceID=&machineFingerPrint=&deviceType=&browserType=&uniqueURLStatus=disabled&imc_service_page=SignOnRetRq&Alignment=LEFT&page=SignOnRetRq&locale=en&PageName=Thin_SignOnRetRq.xml&formAction=https%3A%2F%2Fibank.bni.co.id%2FMBAWeb%2FFMB%3Bjsessionid%3D0000gsadMFnW4TJnYCFiblgmvcx%3A1a1li5jho&mConnectUrl=FMB&serviceType=Dynamic';

        $form_action = $form->action;

        curl_setopt($ch, CURLOPT_URL, $form_action);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_POST, 1);

        $login = curl_exec($ch);

        $dom->clear();
        $dom->load($login);

        $link = $dom->find("#MBMenuList", 0);

        parse_str(@$link->href, $params);

        $date_from = $this->get_date_from();
        $date_to   = $this->get_date_to();

        // $date_from   = '01-Aug-2017';
        // $date_to     = '31-Aug-2017';

        $postdata = 'Num_Field_Err=%22Please+enter+digits+only%21%22&Mand_Field_Err=%22Mandatory+field+is+empty%21%22&acc1=OPR%7C0000000' .
                    $account . '%7CBNI+TAPLUS&TxnPeriod=-1&Search_Option=Date&txnSrcFromDate=' . $date_from .
                    '&txnSrcToDate=' . $date_to .
                    '&FullStmtInqRq=Lanjut&MAIN_ACCOUNT_TYPE=OPR&mbparam=' . urlencode(@$params['mbparam']) .
                    '&uniqueURLStatus=disabled&imc_service_page=AccountIDSelectRq&Alignment=LEFT&page=AccountIDSelectRq&locale=bh&PageName=AccountTypeSelectRq&formAction=' .
                    urlencode($form_action) . '&mConnectUrl=FMB&serviceType=Dynamic';

        curl_setopt($ch, CURLOPT_URL, $form_action);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_REFERER, $form_action);

        $data = curl_exec($ch);

        if (stripos($data, 'Tanggal Awal harus menggunakan format yang telah ditentukan') !== false) :

            $postdata   = 'Num_Field_Err=%22Please+enter+digits+only%21%22&Mand_Field_Err=%22Mandatory+field+is+empty%21%22&acc1=OPR%7C0000000' .
                        $account . '%7CBNI+TAPLUS&TxnPeriod=-1&Search_Option=Date&txnSrcFromDate=' . $date_from .
                        '&txnSrcToDate=' . $date_to .
                        '&FullStmtInqRq=Lanjut&MAIN_ACCOUNT_TYPE=OPR&mbparam=' . urlencode($params['mbparam']) .
                        '&uniqueURLStatus=disabled&imc_service_page=AccountIDSelectRq&Alignment=LEFT&page=AccountIDSelectRq&locale=bh&PageName=AccountTypeSelectRq&formAction=' .
                        urlencode($form_action) . '&mConnectUrl=FMB&serviceType=Dynamic';

            curl_setopt($ch, CURLOPT_URL, $form_action);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_REFERER, $form_action);
            $data = curl_exec($ch);

        endif;

        $postdata = 'Num_Field_Err=%22Please+enter+digits+only%21%22&Mand_Field_Err=%22Mandatory+field+is+empty%21%22&__LOGOUT__=Keluar&mbparam=' .
            urlencode(@$params['mbparam']) .
            '&uniqueURLStatus=disabled&imc_service_page=SignOffUrlRq&Alignment=LEFT&page=SignOffUrlRq&locale=bh&PageName=LoginRs&formAction=' .
            urlencode($form_action) . '&mConnectUrl=FMB&serviceType=Dynamic';

        curl_setopt($ch, CURLOPT_URL, $form_action);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_REFERER, $form_action);
        $result = curl_exec($ch);
        curl_close($ch);

        $dom->clear();
        $dom->load($data);
        $dom->find("#TitleBar", 1);

        $result = [];

        if (!is_null($dom)) :
            $tables = $dom->find('#H');

            $histories_tmp = array();
            for ($i = 2; $i < count($tables) - 1; $i++) :

                $table = $tables[$i];
                if (trim($table->innertext) == '') :
                    continue;
                endif;

                $histories_tmp[] = $table->innertext;
            endfor;

            foreach (array_chunk($histories_tmp, 5) as $history) :
				$type 	= strtoupper($history[2]);

				if(
					'' === $this->type ||
					('debet' === $this->type && 'DB' === $type) ||
					('kredit' === $this->type && 'CR' === $type)
				) :
	                $result[] = [
	                    'waktu'         => $this->date_filter($history[0]),
	                    'keterangan'    => $history[1],
	                    'jumlah'        => $this->number_filter($history[3]),
	                    'jenis'         => strtoupper($history[2])
	                ];
				endif;
            endforeach;
        endif;

		if(0 < count($result)) :

			$this->respond['valid']     = true;
			$this->resond['messages'][] = 'Data BNI found.';
			$this->respond['data']      = $result;

		else :

			$this->respond['valid']     = false;
			$this->resond['messages'][] = __('Tidak ditemukan data. Kemungkinan server hosting anda tidak bisa terkoneksi dengan server BNI', 'sejoli');
			$this->respond['data']      = array();

		endif;

		return $this;
	}

	/**
	 * Return respond from this class
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
