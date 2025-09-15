<?php
namespace Mutasi\Bank;

class BCA
{
	private $day      = 7;
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

	/**
	 * INITIALIZATION
	 */
	function __construct()
	{
		return $this;
	}

	/**
	 * Set number date
	 * @param integer $date;
	 */
	public function set_day($day)
	{
		$day       = (int) $day;
		$this->day = ($day > 7) ? 7 : $day;

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
		$this->account 	= (int)	$account;
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
	public function set_credential($username,$password)
	{
		$this
			->set_username($username)
			->set_password($password);

		return $this;
	}

	/**
	 * Get date from
	 * @return date
	 */
	protected function get_date_from()
	{
		return date('Y-n-d',strtotime('-'.$this->day.' day'));
	}

	/**
	 * Get date to
	 * @return date
	 */
	protected function get_date_to()
	{
		return date('Y-n-d');
	}

	/**
	 * Strip unneeded html tags
	 * @since 	1.1.1
	 * @param  	string 	$content
	 * @return 	string
	 */
	protected function strip_content($content) {

		$content = preg_replace(
		        array(// Remove invisible content
		            '@<head[^>]*?>.*?</head>@siu',
		            '@<style[^>]*?>.*?</style>@siu',
		            '@<script[^>]*?.*?</script>@siu',
		            '@<noscript[^>]*?.*?</noscript>@siu',
		            ),
		        "", //replace above with nothing
		        $content );

		$content = strip_tags($content, '<select><option>');

		return $content;
	}

	/**
	 * Login to klikBCA
	 * @return void
	 */
	protected function login()
	{
		// ================================================================================
		// LOGIN BCA
		// ================================================================================

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookiejar');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookiejar');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 ( Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1 ) Gecko/20061204 Firefox/2.0.0.1');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com');
		$info = curl_exec($ch);

		$params = "value%28actions%29=login&value%28user_id%29=".$this->username."&value%28CurNum%29=GRnFs8tCLosKQrvfmgSD49GhqApzOK7t&value%28user_ip%29=".$_SERVER['SERVER_ADDR']."&value%28browser_info%29=Mozilla%2F5.0+%28Windows+NT+6.3%3B+WOW64%29+AppleWebKit%2F537.36+%28KHTML%2C+like+Gecko%29+Chrome%2F44.0.2403.125+Safari%2F537.36&value%28mobile%29=false&value%28pswd%29=".$this->password."&value%28Submit%29=LOGIN";

		// $params = "value(actions)=login
		// &value(user_id)=$user
		// &value(CurNum)=GRnFs8tCLosKQrvfmgSD49GhqApzOK7t
		// &value(user_ip)=10.0.42.33
		// &value(browser_info)=Mozilla%2F5.0+(Windows+NT+6.3%3B+WOW64)+AppleWebKit%2F537.36+(KHTML%2C+like+Gecko)+Chrome%2F44.0.2403.125+Safari%2F537.36
		// &value(mobile)=false
		// &value(pswd)=$pass
		// &value(Submit)=LOGIN";

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_POST, 1);

		$info = curl_exec($ch);
	}

	/**
	 * Check mutasi
	 * @return void
	 */
	public function check_mutasi()
	{
		require_once('simple_html_dom_parser.php');
		set_time_limit(300);
		// ================================================================================
		// LOGIN BCA
		// ================================================================================

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookiejar');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookiejar');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 ( Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1 ) Gecko/20061204 Firefox/2.0.0.1');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com');
		$info = curl_exec($ch);
    	$params = "value%28actions%29=login&value%28user_id%29=".$this->username."&value%28CurNum%29=GRnFs8tCLosKQrvfmgSD49GhqApzOK7t&value%28user_ip%29=140.0.118.190&value%28browser_info%29=Mozilla%2F5.0+%28Windows+NT+6.3%3B+WOW64%29+AppleWebKit%2F537.36+%28KHTML%2C+like+Gecko%29+Chrome%2F44.0.2403.125+Safari%2F537.36&value%28mobile%29=false&value%28pswd%29=".$this->password."&value%28Submit%29=LOGIN";

	    // $params = "value(actions)=login
	    // &value(user_id)=$user
	    // &value(CurNum)=GRnFs8tCLosKQrvfmgSD49GhqApzOK7t
	    // &value(user_ip)=10.0.42.33
	    // &value(browser_info)=Mozilla%2F5.0+(Windows+NT+6.3%3B+WOW64)+AppleWebKit%2F537.36+(KHTML%2C+like+Gecko)+Chrome%2F44.0.2403.125+Safari%2F537.36
	    // &value(mobile)=false
	    // &value(pswd)=$pass
	    // &value(Submit)=LOGIN";

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_POST, 1);
		$info = curl_exec($ch);

		// ================================================================================
		// BUKA MENU
		// ================================================================================

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/menu_bar.htm');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
		$info = curl_exec($ch);

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do?value(actions)=welcome');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
		$info = curl_exec($ch);

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do?value(actions)=welcome');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "value%28actions%29=selecttransaction");
		$info = curl_exec($ch);

		// ================================================================================
		// BUKA INFORMASI REKENING
		// ================================================================================

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
		$info = curl_exec($ch);

		// ================================================================================
		// BUKA MUTASI REKENING
		// ================================================================================

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/accountstmt.do?value(actions)=acct_stmt');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
		curl_setopt($ch, CURLOPT_POST, 1);
		$info = curl_exec($ch);
		$source = curl_exec($ch);

		// ================================================================================
		// PARAMETER CEK MUTASI
		// ================================================================================

		$params = array();

		$t0 = explode('-',$this->get_date_from());
		$t1 = explode('-',$this->get_date_to());

		$params[] = 'value%28startDt%29=' . $t0[2];
		$params[] = 'value%28startMt%29=' . $t0[1];
		$params[] = 'value%28startYr%29=' . $t0[0];
		$params[] = 'value%28endDt%29=' . $t1[2];
		$params[] = 'value%28endMt%29=' . $t1[1];
		$params[] = 'value%28endYr%29=' . $t1[0];
		$params[] = 'value%28D1%29=0';
		// $params[] = 'value%28D1%29=' . $this->account;
		$params[] = 'value%28r1%29=1';
		$params[] = 'value%28fDt%29=';
		$params[] = 'value%28tDt%29=';
		$params[] = 'value%28submit1%29=Lihat+Mutasi+Rekening';

		$params = implode('&', $params);

		// ================================================================================
		// BUKA LIHAT MUTASI REKENING & SIMPAN HASILNYA DI $source
		// ================================================================================

		// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/accountstmt.do?value(actions)=acctstmtview');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_POST, 1);
	    $source = curl_exec($ch);


		// ================================================================================
		// LOGOUT, CURL CLOSE, HAPUS COOKIES
		// ================================================================================

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do?value(actions)=welcome');
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do?value(actions)=logout');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
		$info = curl_exec($ch);

		curl_close($ch);

		$source = explode('<b>Saldo</b></font></div></td>', $source);

		// ================================================================================
		// CEK APAKAH TIDAK ADA MUTASI ATAU BCA ERROR
		// ================================================================================

    	$result_array = array();

    	if (isset($source[1])) :

			$table 	= explode("</table>", $source[1]);
        	$tr 	= explode("<tr>", $table[0]);
			$x = 0;
	        for ($i = 1; $i < count($tr); $i++)
	        {
	            $str = str_ireplace('</font>', '#~#~#</font>', $tr[$i]);
	            $str = str_ireplace('<br>', '<br> ', $str);
	            $str = preg_replace('!\s+!', ' ', trim(strip_tags($str)));

	            $arr = array_map('trim', explode("#~#~#", $str));

	            $result_array[$x]['date']   		= strip_tags($arr[0]);
	            $result_array[$x]['note']   		= strip_tags($arr[1]);
	            $result_array[$x]['cab']   			= strip_tags($arr[2]);
	            $result_array[$x]['nominal']   		= (float) preg_replace('/\D/', '', $arr[3]) / 100;
	            $result_array[$x]['type']   		= strip_tags($arr[4]);
	            $result_array[$x]['saldo']   		= (float)preg_replace('/\D/', '', $arr[5]) / 100;

	            if($result_array[$x]['nominal'] == 0)
	            {
	                unset($result_array[$x]);
	                continue;
	            }

	            $x++;
	        }
        endif;

    	if (count($result_array) <= 0) :
			$this->respond['valid']      = false;
			$this->respond['messages'][] = 'Data not found. Refresh again';

			return $this;
    	else :

			$mutasi = $data = [];
			$i      = 0;

        	foreach($result_array as $key => $detail) :

				$type 	= ('CR' === $detail['type']) ? 'kredit' : 'debet';

				$mutasi = [
					'type'		=> $type,
					'date' 		=> $detail['date'],
					'note' 		=> $detail['note'],
					'nominal'	=> $detail['nominal'],
				];

				if('' !== $this->type) :
					if($this->type == $type) :
						array_push($data,$mutasi);
					endif;
				else :
					array_push($data,$mutasi);
				endif;

        	endforeach;
        endif;

		ksort($data);

		$this->respond['valid']     = true;
		$this->respond['messages'][] = 'Data BCA found.';
		$this->respond['data']      = $data;

		return $this;
	}

	/**
	 * Check BCA account
	 * @return [type] [description]
	 */
	function check_account()
	{
		require_once('simple_html_dom_parser.php');
		set_time_limit(300);

		// ================================================================================
		// LOGIN BCA
		// ================================================================================

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookiejar');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookiejar');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 ( Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1 ) Gecko/20061204 Firefox/2.0.0.1');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com');
		$info = curl_exec($ch);

    	$params = "value%28actions%29=login&value%28user_id%29=".$this->username."&value%28CurNum%29=GRnFs8tCLosKQrvfmgSD49GhqApzOK7t&value%28user_ip%29=140.0.118.190&value%28browser_info%29=Mozilla%2F5.0+%28Windows+NT+6.3%3B+WOW64%29+AppleWebKit%2F537.36+%28KHTML%2C+like+Gecko%29+Chrome%2F44.0.2403.125+Safari%2F537.36&value%28mobile%29=false&value%28pswd%29=".$this->password."&value%28Submit%29=LOGIN";

	    // $params = "value(actions)=login
	    // &value(user_id)=$user
	    // &value(CurNum)=GRnFs8tCLosKQrvfmgSD49GhqApzOK7t
	    // &value(user_ip)=10.0.42.33
	    // &value(browser_info)=Mozilla%2F5.0+(Windows+NT+6.3%3B+WOW64)+AppleWebKit%2F537.36+(KHTML%2C+like+Gecko)+Chrome%2F44.0.2403.125+Safari%2F537.36
	    // &value(mobile)=false
	    // &value(pswd)=$pass
	    // &value(Submit)=LOGIN";

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_POST, 1);
		$info = curl_exec($ch);

		// ================================================================================
		// BUKA MENU
		// ================================================================================

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/menu_bar.htm');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
		$info = curl_exec($ch);

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do?value(actions)=welcome');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
		$info = curl_exec($ch);

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do?value(actions)=welcome');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "value%28actions%29=selecttransaction");
		$info = curl_exec($ch);

		// ================================================================================
		// BUKA INFORMASI REKENING
		// ================================================================================

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
		$info = curl_exec($ch);

		// ================================================================================
		// BUKA MUTASI REKENING
		// ================================================================================

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/accountstmt.do?value(actions)=acct_stmt');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
		curl_setopt($ch, CURLOPT_POST, 1);
		$info = curl_exec($ch);
		$content = $this->strip_content($info);
		$html = str_get_html($content);

		// ================================================================================
		// LOGOUT, CURL CLOSE, HAPUS COOKIES
		// ================================================================================

		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do?value(actions)=welcome');
		curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do?value(actions)=logout');
		curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
		$info = curl_exec($ch);

		curl_close($ch);

		$accounts 	= array();

		if($html->find("select[id=D1]",0)) :

	        $options = $html->find('select[id=D1]', 0)->find("option");

			foreach($options as $option) :
				if(!is_null($option->value) && !is_null($option->plaintext)) :
					$no_rekening 	=  preg_replace("/[^0-9]/", "", $option->plaintext);
					$accounts[$option->value]	= $no_rekening;
				endif;

			endforeach;
		endif;

		if(0 < count($accounts)) :

			$this->respond['valid']      = true;
			$this->respond['messages'][] = 'Data BCA found.';
			$this->respond['data']       = $accounts;

		else :

			$this->respond['messages'][] = __('Nomor rekening tidak diketemukan. Kemungkinan server anda tidak bisa terkoneksi dengan server BCA', 'sejoli');

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
