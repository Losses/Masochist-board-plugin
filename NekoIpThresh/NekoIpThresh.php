<?php
/* class.ipthresh.php
.---------------------------------------------------------------------------.
|   Program: Project Oekakisoli                                             |
|    Author: Miz ( oekakisoli@gmail.com )                                   |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
| ------------------------------------------------------------------------- |
| APC required                                                              |
| for internal uses only, not very well tested, use at your own risk        |
'---------------------------------------------------------------------------'
*/
class Ipthresh
{
	//configurable vars 
	public $logpath='/logs'; //absolute path
	public $refuse_noua=false; //if no user-agent
	public $thresh_post_duration=25; //seconds
	public $thresh_post_threshold=10; //post times in thresh_post_duration
	public $thresh_get_duration=25; //seconds
	public $thresh_get_threshold=24; //post times in thresh_post_duration
	public $default_bantime=1800; //sec
	public $default_bantime_lite=65; //sec
	public $timeincrement=65; // sec
	public $default_mode=1;//  1=check only   2=403       3=display & die
	public $whiteip=array( //for AUTO only
		'127.0.0.1',
		'::1',
	);

	//vars
	private $prechecked=false;
	private $apc;
	private $maxbanposts=32; //prevent vul
	
	// constructor
	function __construct($auto=false) {
		$this->apc=new CacheAPC();
		if($auto)
		{
			if(in_array($_SERVER['REMOTE_ADDR'],$this->whiteip)) return 0;
			$this->default_mode=2;
			$this->filter_ua();
			$this->filter_uri();
			$this->filter_keyword();
			$this->filter_bulk_post();
			$this->filter_bulk_get();
		}
	}

  /**
   * ban or/and log
   *
   * @param dur : 0=once   >0 seconds
   * @param log : 0=no log   1=simple  2=verbose(unfinish)
   * @param txt : user defined text
   */
	function dealwithit($dur=0,$log=0,$txt='')
	{
		//dur
		if($dur>0)
		{
			$remoteaddr=isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
			$banlist=$this->apc->getData('Ipthresh');
			$banlist[$remoteaddr]=(!isset($banlist[$remoteaddr]))?(time()+$dur):max(time()+$dur,$banlist[$remoteaddr]);
			$this->apc->setData('Ipthresh',$banlist);
		}
		//log
		if($log)
		{
			$r=$_SERVER['REMOTE_ADDR'];
			$for=isset($_SERVER['HTTP_X_FORWARDED_FOR'])?'for:'.$_SERVER['HTTP_X_FORWARDED_FOR']:'';
			$ref=isset($_SERVER['HTTP_REFERER'])?'ref:'.$_SERVER['HTTP_REFERER']:'';
			$postdata=implode('&',array_values($_POST));
			if(!empty($postdata)) $postdata='post:'.$postdata;
			$cookie2=isset($_SERVER['HTTP_COOKIE'])?'cookie:'.$_SERVER['HTTP_COOKIE']:'';
			$d=date('Y-m-d G:i:s');
			if((time()%259200)>=(filemtime($this->logpath.'/ipthresh.log')%259200))
				file_put_contents($this->logpath.'/ipthresh.log',$d.' - '.$r.' - '.$_SERVER['HTTP_USER_AGENT'].' - '.$_SERVER['REQUEST_METHOD'].' - '.$_SERVER['REQUEST_URI'].' - '.$ref.' - '.$for.' - '.$postdata.' - '.$cookie2.' - '.$txt."\r\n",FILE_APPEND|LOCK_EX);
			else
				file_put_contents($this->logpath.'/ipthresh.log',$d.' - '.$r.' - '.$_SERVER['HTTP_USER_AGENT'].' - '.$_SERVER['REQUEST_METHOD'].' - '.$_SERVER['REQUEST_URI'].' - '.$ref.' - '.$for.' - '.$postdata.' - '.$cookie2.' - '.$txt."\r\n",LOCK_EX);
		}
		//mode
		if($this->default_mode==2) {header('Status: 403 Forbidden');exit();} //NGiNX
		else if($this->default_mode==3) die('your IP is considered unsafe, please try again later.');
		return -1;
	}

	function precheck()
	{
		$this->prechecked=true;
		
		//check banned ip list
		$banlist=$this->apc->getData('Ipthresh');
		if($banlist!=null)
		{
			$time=time();

			$remoteaddr=isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
			if(isset($banlist[$remoteaddr]))
			{
				if($banlist[$remoteaddr]>$time)
					return $this->dealwithit($this->timeincrement,1,'ip');
				else
				{
					unset($banlist[$remoteaddr]);
					$this->apc->setData('Ipthresh',$banlist);
					return 0;
				}
			}

			foreach($banlist as $key=>&$elem)
			{
				if($elem>$time)
				{
					unset($banlist[$key]);
					$this->apc->setData('Ipthresh',$banlist);
					break;
				}//not exhaustive but better
			}
			unset($elem);
		}
		
		//check banned post list
		$banlist=$this->apc->getData('Ipthresh_banpost');
		if($banlist!=null)
		{
			$time=time();
			foreach($banlist as $key=>&$elem)
			{
				if($elem>$time)
				{
					unset($banlist[$key]);
					if(count($banlist)==0)
					{
						$this->apc->delData('Ipthresh_banpost');
						return 0;
					}
					else
					{
						$this->apc->setData('Ipthresh_banpost',$banlist);
					}
					break;
				}//not exhaustive but better
			}
			unset($elem);
			
			if($_SERVER['REQUEST_METHOD']=='POST')
			{
				$postdata=$this->getposthash();
				if(isset($banlist[$postdata]))
				{
					if($banlist[$postdata]>$time)
						return $this->dealwithit($this->timeincrement,1,'ip');
					else
					{
						unset($banlist[$postdata]);
						if(count($banlist)==0)
							$this->apc->delData('Ipthresh_banpost');
						else
							$this->apc->setData('Ipthresh_banpost',$banlist);
						return 0;
					}
				}
			}
		}
		
		
		return 0;
	}
	
	//return true=safe, false=
	function threshcheck($obj,$key,$seconds,$quantity=0)
	{
		if(!($g=$this->apc->getData($obj))) $g=array();
		$groups=array();
		$time=time();
		$bsave=false;
		foreach($g as $tmpkey=>$val)
		{
			if($time-(int)$val[0]<=$seconds) $groups[$tmpkey]=$val; else $bsave=true;
		}
		unset($g);
		
		if(!isset($groups[$key]))
		{
			$groups[$key]=array($time,$quantity);
			$this->apc->setData($obj,$groups);
			return true;
		}
		else
		{
			if($groups[$key][1]>0)
			{
				--$groups[$key][1];
				$this->apc->setData($obj,$groups);
				return true;
			}
			else
			{
				if($bsave) $this->apc->setData($obj,$groups);
				return false;
			}
		}
	}

	//return 0=ok, otherwise banned
	public function filter_ua()
	{
		if(!$this->prechecked && $this->precheck()) return -2;
		//first blood
		$UA=$_SERVER['HTTP_USER_AGENT'];

		//no-ua
		if($this->refuse_noua && !$UA)
		{
			if($this->threshcheck('IPThresh_noua',$_SERVER['REMOTE_ADDR'],360,5))
				return $this->dealwithit(0,1,'no-ua');
			else
				return dealwithit($this->default_bantime,1,'no-ua');
		}
			
		//user-agent
		$dis_a_spam=array(
			'Mozilla/4.0 (compatible; Win32; WinHttp.WinHttpRequest.5)',//most used by CC attacker
			'Mozilla/4.0 (compatible; MSIE 8.0; Win32)',//attacker
			'Googlebot/2.1 (+http://www.googlebot.com/bot.html)',//attacker
			'Mozilla/5.0 ()',
			'Mozilla/5.0',
			'Mozilla',
			//'NativeHost',
			'Agent',
			'Mozilla/4.0 (compatible; ICS)',
			'Python-urllib/2.7',
			'Mozilla/4.0 (compatible; MSIE; Windows NT 6.1; Trident/4.0)',
			'User_Agent',
			'Mozilla/4.0 (compatible; ICS)',
			'New-Sogou-Spider/1.0 (compatible; MSIE 5.5; Windows 98)', // attacker?
			'xpymep.exe',
			'Apache-HttpClient/4.2.3 (java 1.5)',
			'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1;)', //99% hacker
			'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; TencentTravel', //hacker
		);
		if(in_array($UA,$dis_a_spam)) return $this->dealwithit($this->default_bantime,1,'ua');

		$dis_a_spam_b=array(
			'compatible ;',
			'libwww-perl',//HACKER?
			'\'',
		);
		foreach($dis_a_spam_b as $ag)
		{
			if(strpos($UA,$ag)!==false) return $this->dealwithit($this->default_bantime,1,'ua');
		}
		return 0;
	}
	//return 0=ok, otherwise banned
	public function filter_uri()
	{
		if(!$this->prechecked && $this->precheck()) return -2;
		$uri=$_SERVER['REQUEST_URI'];
		$banuri=array(
			'/signup',
			'/wp-login.php?action=register',
			'/?s=Register',
			'/tiki-register.php',
			'/user/register',
			//'/member.php?mod=logging&action=login',
			'/member.php?mod=register',
			'/modules.php?app=user_reg',
			//'/logging.php?action=login',
			'/CreateUser.asp',
			'/?T=reg',
			'/signup.php',
			'/index.php?do=/user/register/',
			'/index.php?page=en_Signup',
			'/ucp.php?mode=register',
			'/member/index_do.php?fmdo=user&dopost=regnew',
			'/index.php?app=core&module=global&section=login',
			'/index.php?act=Login&CODE=00',
			'/account/register.php',
			'/index.php?action=register',
			'/register.php?type=company',
			'/login.php',
			'/join.php',
			'/YaBB.cgi/',
			'/YaBB.pl/',
			'/forum/member/register',
			'/site/signup.php',
			'/index.php?page=join',
		);
		if(in_array($uri,$banuri)) return $this->dealwithit($this->default_bantime,1,'uri');
		
		$banurib=array(
			'/?=',
			'/?-',
			'allow_url',
			'auto_prepend',
			'php:',
		);
		foreach($banurib as $ag)
		{
			if(strpos($uri,$ag)!==false) return $this->dealwithit($this->default_bantime,1,'uri');
		}
		return 0;
	}
	public function filter_keyword()
	{
		//TODO
		return 0;
	}
	
	//return 0=ok, otherwise banned
	public function filter_bulk_post()
	{
		if(!$this->prechecked && $this->precheck()) return -2;

		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			$remoteaddr=isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
			if(!$this->threshcheck('IPThresh_bulkpost',$remoteaddr,$this->thresh_post_duration,$this->thresh_post_threshold))
			{
				//ban postdata
				$postdata=$this->getposthash();
				$banlist=$this->apc->getData('Ipthresh_banpost');
				if(!$banlist || count($banlist)<$this->maxbanposts)
				{
					$banlist[$postdata]=time()+$this->default_bantime;
					$this->apc->setData('Ipthresh_banpost',$banlist);
				}
				
				//deal with it
				return $this->dealwithit($this->default_bantime,1,'bulk_post');
			}
		}
		return 0;
	}
	//return 0=ok, otherwise banned
	public function filter_bulk_get()
	{
		if(!$this->prechecked && $this->precheck()) return -2;

		//whitelist
		if(isset($_SERVER['HTTP_FROM']))
		{
			$dis_f_white_b=array(
				'bingbot(at)microsoft.com',
				'Googlebot',
				//'bingbot',
			);
			if(in_array($_SERVER['HTTP_FROM'],$dis_f_white_b)) return 0;
		}
		
		$remoteaddr=isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
		if(!$this->threshcheck('IPThresh_bulkpost',$remoteaddr,$this->thresh_get_duration,$this->thresh_get_threshold))
		{
			return $this->dealwithit($this->default_bantime_lite,1,'cc attack');
		}
		return 0;
	}
	function getposthash()
	{
		return substr(implode('&',array_values($_POST)),0,256);//not well tested yet
	}
}
class CacheAPC {

	private $iTtl = 43200; // Time To Live
	private $bEnabled = false; // APC enabled?

	// constructor
	function __construct() {
		$this->bEnabled = extension_loaded('apc');
	}

	// get data from memory
	function getData($sKey) {
		$bRes = false;
		$vData = apc_fetch($sKey, $bRes);
		return ($bRes) ? $vData :null;
	}

	// save data to memory
	function setData($sKey, $vData) {
		return apc_store($sKey, $vData, $this->iTtl);
	}

	// delete data from memory
	function delData($sKey) {
		return (apc_exists($sKey)) ? apc_delete($sKey) : true;
	}
}
function ipthresh()
{
	new Ipthresh(true);
}
?>