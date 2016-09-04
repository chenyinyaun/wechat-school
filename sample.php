<?php
/**
 * wechat php test
 */

//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
// $wechatObj->valid();
$wechatObj -> responseMsg();
class wechatCallbackapiTest {
	private $fromUsername;
	private $toUsername;
	private function getsql() {
		$mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
	}

	public function valid() {
		$echoStr = $_GET["echostr"];

		//valid signature , option
		if ($this -> checkSignature()) {
			echo $echoStr;
			exit ;
		}
	}

	public function responseMsg() {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		//extract post data
		if (!empty($postStr)) {
			/* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
			 the best way is to check the validity of xml by yourself */
			libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$this -> fromUsername = $postObj -> FromUserName;
			$this -> toUsername = $postObj -> ToUserName;
			$MsgType = $postObj -> MsgType;
		if ($MsgType == 'text') {
			$keyword = trim($postObj -> Content);

			if ($keyword == "课表") {
				$content = $this -> kebiao();
				$this -> news("您的课表为",$content);

			} else if ($keyword == "绑定") {
				$openid = $this -> fromUsername;
				$content = "<a href=\"http://www.rooroor.com/bangding.php?openid={$openid}\">点击绑定学号</a> ";
				$this -> text($content);

			} else if ($keyword == "取消绑定") {
				$content = $this -> bangoff();
				$this -> text($content);

			} else if ($keyword == "成绩") {
				$content = $this -> grade();
				$this -> news("您的学期成绩",$content);

			} else if ($keyword == "所有成绩") {
				$content = $this -> allgrade();
				$this -> news("您的所有成绩",$content);

			} else if ($keyword == "考试安排"|| $keyword == "考试") {
				$content = $this -> kaoshi();
				$this -> news("考试安排",$content);

			} else if ($keyword == "补考") {
				$content = $this -> bukao();
				$this -> news("补考安排",$content);

			} else if ($keyword == "补考成绩") {
				$content = $this -> bukaochengji();
				$this -> news("补考成绩",$content);

			} else if ($keyword == "四级" || $keyword == "六级") {
				$content = $this -> CET();
				$this -> news("四六级成绩",$content);

			} else if ($keyword == "公交") {
				$content = $this -> gongjiao();
				$this -> news("部分公交车站",$content);

			} else if ($keyword == "四级查询" || $keyword == "六级查询") {
				$content = "<a href=\"http://cet.99sushe.com\">点此进行 四级查询、六级查询!</a>";
				$this -> text($content);

			} else {
				$input = "第一次查询请先输入“绑定”,关键字有“课表”，“成绩”，“所有成绩”，“补考成绩”，“补考”，“四六级”，“四级查询”，“公交”，“考试”。如果帮别人查询一定要先输入“取消绑定”，再重新“绑定”";
				$this -> news("关键字输入不正确请参考这里：",$input);
				// echo "Input something...";
			}
		}elseif ($MsgType == 'event') {
				$MsgEvent = $postObj -> Event;
				//获取事件类型
				if ($MsgEvent == 'subscribe') {//订阅事件
					$arr[] = "第一次查询请先输入“绑定”,关键字有“课表”，“成绩”，“所有成绩”，“补考成绩”，“补考”，“四六级”，“四级查询”，“公交”，“考试”。如果帮别人查询一定要先输入“取消绑定”，再重新“绑定";
					$this -> news("首次关注须知", $arr);
					exit ;
				} elseif ($MsgEvent == 'CLICK') {//点击事件
					$EventKey = $postObj -> EventKey;
					//菜单的自定义的key值，可以根据此值判断用户点击了什么内容，从而推送不同信息
					// $arr[] = $EventKey;
					if ($EventKey =="grade") {
						$content = $this -> grade();
						$this -> news("您的学期成绩",$content);
					}elseif ($EventKey =="allgrade") {
						$content = $this -> allgrade();
						$this -> news("您的所有成绩",$content);
					}elseif ($EventKey =="kaoshi") {
						$content = $this -> kaoshi();
						$this -> news("考试安排",$content);
					}elseif ($EventKey =="kebiao") {
						$content = $this -> kebiao();
						$this -> news("您的课表为",$content);
					}elseif ($EventKey =="cet") {
						$content = $this -> CET();
						$this -> news("您的四六级成绩为",$content);
					}elseif ($EventKey =="bukao") {
						$content = $this -> bukao();
						$this -> news("补考安排",$content);
					}elseif ($EventKey =="lose") {
						$this -> news("补考安排","面对全校同学的开放的失物招领服务正在开发中，亲情期待");
					}
					exit ;
				}
			}

		} else {
			echo "";
			exit ;
		}
	}

	private function text($content) {

		$contentStr = $content;
		$msgType = "text";
		$fromUsername = $this -> fromUsername;
		$toUsername = $this -> toUsername;
		$time = time();
		$textTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[%s]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			<FuncFlag>0</FuncFlag>
			</xml>";
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
		echo $resultStr;
	}
	/**
	 *@param type: text 文本类型, news 图文类型
	 *@param value_arr array(内容),array(ID)
	 *@param o_arr array(array(标题,介绍,图片,超链接),...小于10条),array(条数,ID)
	 */

	private function news($title,$description) {
		$imageTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[news]]></MsgType>//消息类型为news（图文）
						<ArticleCount>1</ArticleCount>//图文数量为1（单图文）
						<Articles>
						<item>//第一张图文消息
						<Title><![CDATA[%s]]></Title> //标题
						<Description><![CDATA[%s]]></Description>//描述
						<PicUrl><![CDATA[%s]]></PicUrl>//打开前的图片链接地址
						<Url><![CDATA[%s]]></Url>//点击进入后显示的图片链接地址
						</item>
						</Articles>
						</xml> ";

						$fromUsername = $this -> fromUsername;
						$toUsername = $this -> toUsername;
						$time = time();
			      $PicUrl = "http://mmbiz.qpic.cn/mmbiz/3qBsKnWywLPzLTyKMtgHRBN09YjNzgbJSdYwZKZLORcaBNNpKz3bDXGsEuke4cIW5nwXMsJQKeTY5kRMeFZtmQ/0";//图片链接
			      $resultStr = sprintf($imageTpl, $fromUsername, $toUsername, $time, $title, $description,$PicUrl,$Url);
		      	echo   $resultStr;

	}

	private function gongjiao(){
		$string = "BRT(快速公交)：线路：刘家堡广场→世纪大道→桃海市场→费家营→安宁区政府→<兰州交通大学>
		→政法学院→西北师大→培黎广场→十里店（乘客可免费换乘15路，72路，131路）→幸福巷(可免费换乘15路，72路，
		131路)→七里河黄河桥北(可免费换乘15路,131路)→七里河黄河桥南站→兰州四中站→兰州西站。票价：1元 时间：最
		迟末班在晚上10点。说明：BRT站台就在校门口，基本上所有出行都会先上BRT然后转车。";
		return $string;
	}


   private function CET(){

		 $openid = $this -> fromUsername;
		 // $this -> getsql();
		 $mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
		 $sql = "SELECT * FROM account WHERE openid='$openid' ";
		 $mysqli -> query("SET NAMES 'utf8'");
		 $result = $mysqli -> query($sql);
		 while ($row = $result -> fetch_assoc()) {
			 $xuehao = $row['xuehao'];
			 $mima = $row['mima'];
		 }
		 if (!empty($xuehao)) {

		 $cookie_file = tempnam('./temp', 'cookie');
$ch = curl_init("http://xuanke.lzjtu.edu.cn/default_ysdx.aspx");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
$str = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
//获取___viewstate的值,正则匹配上之后放在$matches[1]中的，不需要对其URL转码，主要是组合成post数据，它放在一个数组里面的
$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
preg_match($pattern, $str, $matches);
$login_url = $info['url'];
$url = str_replace("default_ysdx.aspx", "", $login_url);
//进入登录页面之后文件名改变所以要提前去掉目前的文件名，以方便后面的进行使用URL
$login = array("__VIEWSTATE" => $matches[1], "TextBox1" => $xuehao, "TextBox2" => $mima, "RadioButtonList1" => "学生", "Button1" => "登陆");
//进行post数据提交，带上cookie
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $login_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
$str = curl_exec($ch);
curl_close($ch);
//进入个人页面后，再次选菜单页时同样要模拟登录 到固定的页面，只不过它不需要post了，后期代码修改，同样要使用post，观察post的字段有哪些
$loginUrl = $url . 'xsdjkscx.aspx?xh='.$xuehao;
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$str = curl_exec($ch);
curl_close($ch);
$str = mb_convert_encoding($str, "utf-8", "gb2312");
$td = $this->get_td_array($str);
array_shift($td);
$kaoshi = "";
foreach ($td as $v) {
	if ($v[1]) {
		$kaoshi .= "学年：{$v[0]}\n学期：第{$v[1]}学期\n{$v[2]}\n考试日期：\n{$v[4]}\n总成绩：{$v[5]}\n听力成绩：{$v[6]}\n阅读成绩：{$v[7]}\n写作成绩：{$v[8]}\n----------------\n";
	}
}
if ($kaoshi == "") {$strings = "你是第一次考四级成绩没出来或者没考对吧，如果是第一次考的话成绩出来了之后要等到教务处上传了数据才能查到哈，想立即查询四级输入“四级查询”！";
	return $strings;
} else {
	return $kaoshi;
}
	 } else {
		 $string = "你还没有绑定学号吧，赶快输入’绑定’试试 ";
		 return $string;
	 }
 }
	private function kebiao() {
		$openid = $this -> fromUsername;
		// $this -> getsql();
		$mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
		$sql = "SELECT * FROM account WHERE openid='$openid' ";
		$mysqli -> query("SET NAMES 'utf8'");
		$result = $mysqli -> query($sql);
		while ($row = $result -> fetch_assoc()) {
			$xuehao = $row['xuehao'];
			$mima = $row['mima'];
		}

		if (!empty($xuehao)) {
			//学生课表查询。基本和补考查询一样，注意检查 文件名是否改变
			$cookie_file = tempnam('./temp', 'cookie');
			$ch = curl_init("http://xuanke.lzjtu.edu.cn/default_ysdx.aspx");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);

			//获取—_viewstate的值,组合成post数据
			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);
			$login_url = $info['url'];
			$url = str_replace("default_ysdx.aspx", "", $login_url);
			$login = array("__VIEWSTATE" => $matches[1], "TextBox1" => $xuehao, "TextBox2" => $mima, "RadioButtonList1" => "学生", "Button1" => "登陆");
			//进行post数据
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $login_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			//进入课表查询页面。
			$loginUrl = $url . 'xskbcx.aspx?xh=' . $xuehao;
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			//首先进行转码，调用格式处理函数进行处理。创建两个空数组 储存 循环出来的值 ，然后返回值。
			$str = mb_convert_encoding($str, "utf-8", "gb2312");

			$td = $this -> get_kb_array($str);

			$content = array();
			$content_2 = array();
			for ($i = 1, $j = 2; $i <= 7, $j <= 8; $i++, $j++) {
				$arr = array($td[2][$j], $td[4][$i], $td[6][$j], $td[8][$i], $td[10][$j]);
				foreach ($arr as $v) {
					if (!empty($v)) {
						$content[] = $v;
					}
				}
			}
			$key = count($content);
			for ($k = 0; $k < $key; $k++) {
				$content_2[] = $content[$k] . "\n------------------\n";
			}

			$content_2 = implode("", $content_2);
			//将数组 变为字符串 供输出。
			return $content_2;
			//将字符串变成数组，以“\n”为界；
			//for($k=0;$k<$key;$k++){
			//	$str = explode("\n", $content[$k]);
			//	echo '<br/><br/>课程名字:  '.$str[0].'<br/><br/>课程性质:  '.$str[1].'<br/><br/>几点上课:  '.$str[2].'<br/><br/>老师名字:  '.$str[3].'<br/><br/>在哪上课:  '.$str[4].'<br/><br/>-----------------------------';
			// }
		} else {
			$string = "你还没有绑定学号吧，赶快输入’绑定’试试 ";
			return $string;
		}
	}

	private function grade() {

		$openid = $this -> fromUsername;
		// $this -> getsql();
		$mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
		$sql = "SELECT * FROM account WHERE openid='$openid' ";
		$mysqli -> query("SET NAMES 'utf8'");
		$result = $mysqli -> query($sql);
		while ($row = $result -> fetch_assoc()) {
			$xuehao = $row['xuehao'];
			$mima = $row['mima'];
		}

		if (!empty($xuehao)) {

			$cookie_file = tempnam('./temp', 'cookie');
			$ch = curl_init("http://xuanke.lzjtu.edu.cn/default_ysdx.aspx");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			//获取—_viewstate的值,组合成post数据
			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);
			$login_url = $info['url'];
			$url = str_replace("default_ysdx.aspx", "", $login_url);
			$login = array("__VIEWSTATE" => $matches[1], "TextBox1" => $xuehao, "TextBox2" => $mima, "RadioButtonList1" => "学生", "Button1" => "登陆");
			//进行post数据
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $login_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);

			$loginUrl = $url . 'xscjcx.aspx?xh=' . $xuehao;
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			$pattern_new = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern_new, $str, $match);
			$newview = urlencode($match[1]);
			$button = iconv('utf-8', 'gb2312', '学期成绩');
			$loginn = array("__EVENTTARGET" => "", "__EVENTARGUMENT" => "", "__VIEWSTATE" => $match[1], "hidLanguage" => "", "ddlXN" => "2015-2016", "ddlXQ" => "2", "ddl_kcxz" => "", "btn_xq" => $button);
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $loginn);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);

			$str = mb_convert_encoding($str, "utf-8", "gb2312");
			$td = $this -> get_td_array($str);
			foreach ($td as $v) {
				if ($v[7]) {
					$grade .= "{$v[3]}-{$v[8]}-{$v[6]}\n\n";
				}

			}
			return $grade;
		} else {
			$string = "你还没有绑定学号吧，赶快输入’绑定’试试 ";
			return $string;
		}
	}
	private function bukaochengji() {

		$openid = $this -> fromUsername;
		// $this -> getsql();
		$mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
		$sql = "SELECT * FROM account WHERE openid='$openid' ";
		$mysqli -> query("SET NAMES 'utf8'");
		$result = $mysqli -> query($sql);
		while ($row = $result -> fetch_assoc()) {
			$xuehao = $row['xuehao'];
			$mima = $row['mima'];
		}

		if (!empty($xuehao)) {

			$cookie_file = tempnam('./temp', 'cookie');
			$ch = curl_init("http://xuanke.lzjtu.edu.cn/default_ysdx.aspx");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			//获取—_viewstate的值,组合成post数据
			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);
			$login_url = $info['url'];
			$url = str_replace("default_ysdx.aspx", "", $login_url);
			$login = array("__VIEWSTATE" => $matches[1], "TextBox1" => $xuehao, "TextBox2" => $mima, "RadioButtonList1" => "学生", "Button1" => "登陆");
			//进行post数据
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $login_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);

			$loginUrl = $url . 'xscjcx.aspx?xh=' . $xuehao;
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			$pattern_new = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern_new, $str, $match);
			$newview = urlencode($match[1]);
			$button = iconv('utf-8', 'gb2312', '学期成绩');
			$loginn = array("__EVENTTARGET" => "", "__EVENTARGUMENT" => "", "__VIEWSTATE" => $match[1], "hidLanguage" => "", "ddlXN" => "2015-2016", "ddlXQ" => "1", "ddl_kcxz" => "", "btn_xq" => $button);
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $loginn);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);

			$str = mb_convert_encoding($str, "utf-8", "gb2312");
			$td = $this -> get_td_array($str);
			foreach ($td as $v) {
				if ($v[10] != "") {
					$grade .= "{$v[3]}-{$v[10]}\n\n";
				}
			}
			return $grade;
		} else {
			$string = "<a href=\"http://www.rooroor.com/bangding.php?openid={$openid}\">你还没有绑定学号，赶紧［点击绑定学号］</a> ";
			return $string;
		}
	}

	private function allgrade() {
		$openid = $this -> fromUsername;
		// $this -> getsql();
		$mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
		$sql = "SELECT * FROM account WHERE openid='$openid' ";
		$mysqli -> query("SET NAMES 'utf8'");
		$result = $mysqli -> query($sql);
		while ($row = $result -> fetch_assoc()) {
			$xuehao = $row['xuehao'];
			$mima = $row['mima'];
		}

		if (!empty($xuehao)) {

			$cookie_file = tempnam('./temp', 'cookie');
			$ch = curl_init("http://xuanke.lzjtu.edu.cn/default_ysdx.aspx");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			//获取—_viewstate的值,组合成post数据
			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);
			$login_url = $info['url'];
			$url = str_replace("default_ysdx.aspx", "", $login_url);
			$login = array("__VIEWSTATE" => $matches[1], "TextBox1" => $xuehao, "TextBox2" => $mima, "RadioButtonList1" => "学生", "Button1" => "登陆");
			//进行post数据
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $login_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);

			$loginUrl = $url . 'xscjcx.aspx?xh=' . $xuehao;
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			$pattern_new = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern_new, $str, $match);
			$newview = urlencode($match[1]);
			$button = iconv('utf-8', 'gb2312', '历年成绩');

			$loginn = array("__EVENTTARGET" => "", "__EVENTARGUMENT" => "", "__VIEWSTATE" => $match[1], "hidLanguage" => "", "ddlXN" => "", "ddlXQ" => "", "ddl_kcxz" => "", "btn_zcj" => $button);
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $loginn);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);

			$str = mb_convert_encoding($str, "utf-8", "gb2312");
			$td = $this -> get_td_array($str);
			foreach ($td as $v) {
				if ($v[7]) {
					$grade .= "{$v[3]}-{$v[8]}-{$v[6]}\n";
				}

			}
			return $grade;
		} else {
			$string = "你还没有绑定学号吧，赶快输入’绑定’试试 ";
			return $string;
		}
	}

	private function kaoshi() {

		$openid = $this -> fromUsername;
		// $this -> getsql();
		$mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
		$sql = "SELECT * FROM account WHERE openid='$openid' ";
		$mysqli -> query("SET NAMES 'utf8'");
		$result = $mysqli -> query($sql);
		while ($row = $result -> fetch_assoc()) {
			$xuehao = $row['xuehao'];
			$mima = $row['mima'];
		}

		if (!empty($xuehao)) {

			$cookie_file = tempnam('./temp', 'cookie');
			$ch = curl_init("http://xuanke.lzjtu.edu.cn/default_ysdx.aspx");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			//获取___viewstate的值,正则匹配上之后放在$matches[1]中的，不需要对其URL转码，主要是组合成post数据，它放在一个数组里面的
			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);
			$login_url = $info['url'];
			$url = str_replace("default_ysdx.aspx", "", $login_url);
			//进入登录页面之后文件名改变所以要提前去掉目前的文件名，以方便后面的进行使用URL
			$login = array("__VIEWSTATE" => $matches[1], "TextBox1" => $xuehao, "TextBox2" => $mima, "RadioButtonList1" => "学生", "Button1" => "登陆");
			//进行post数据提交，带上cookie
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $login_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			//进入个人页面后，再次选菜单页时同样要模拟登录 到固定的页面，只不过它不需要post了，后期代码修改，同样要使用post，观察post的字段有哪些
			$loginUrl = $url . 'xskscx.aspx?xh=' . $xuehao;
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			$str = mb_convert_encoding($str, "utf-8", "gb2312");
			$td = $this -> get_td_array($str);
			array_shift($td);
			$kaoshi = "";
			foreach ($td as $v) {
				if ($v[1]) {
					$kaoshi .= "课程名称：{$v[1]}\n姓名：{$v[2]}\n考试时间：\n{$v[3]}\n考试地点：{$v[4]}\n座位号：{$v[6]}\n----------------------\n";
				}
			}
			if ($kaoshi == "") {$strings = "2015-2016第二学期考试安排还没有出来";
				return $strings;
			} else {
				return $kaoshi;
			}

		} else {
			$string = "你还没有绑定学号吧，赶快输入’绑定’试试 ";
			return $string;
		}
	}

	private function bukao() {

		$openid = $this -> fromUsername;
		// $this -> getsql();
		$mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
		$sql = "SELECT * FROM account WHERE openid='$openid' ";
		$mysqli -> query("SET NAMES 'utf8'");
		$result = $mysqli -> query($sql);
		while ($row = $result -> fetch_assoc()) {
			$xuehao = $row['xuehao'];
			$mima = $row['mima'];
		}

		if (!empty($xuehao)) {

			$cookie_file = tempnam('./temp', 'cookie');
			$ch = curl_init("http://xuanke.lzjtu.edu.cn/default_ysdx.aspx");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			//获取___viewstate的值,正则匹配上之后放在$matches[1]中的，不需要对其URL转码，主要是组合成post数据，它放在一个数组里面的
			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);
			$login_url = $info['url'];
			$url = str_replace("default_ysdx.aspx", "", $login_url);
			//进入登录页面之后文件名改变所以要提前去掉目前的文件名，以方便后面的进行使用URL
			$login = array("__VIEWSTATE" => $matches[1], "TextBox1" => $xuehao, "TextBox2" => $mima, "RadioButtonList1" => "学生", "Button1" => "登陆");
			//进行post数据提交，带上cookie
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $login_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			//进入个人页面后，再次选菜单页时同样要模拟登录 到固定的页面，只不过它不需要post了，后期代码修改，同样要使用post，观察post的字段有哪些
			$loginUrl = $url . 'XsBkKsCx.aspx?xh=' . $xuehao;
			$ch = curl_init($loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$str = curl_exec($ch);
			curl_close($ch);
			// 开始带上条件
			$pattern_new = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
preg_match($pattern_new, $str, $match);
$newview = urlencode($match[1]);
// $button = iconv('utf-8', 'gb2312', '学期成绩');
$loginn = array("__EVENTTARGET" => "", "__EVENTARGUMENT" => "", "__VIEWSTATE" => $match[1], "hidLanguage" => "", "xnd" => "2015-2016", "xqd" => "2");
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginn);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$str = curl_exec($ch);
curl_close($ch);

			// end
			$str = mb_convert_encoding($str, "utf-8", "gb2312");
			$td = $this -> get_td_array($str);
			array_shift($td);
			$kaoshi = "";
			foreach ($td as $v) {
				if ($v[1]) {
					$kaoshi .= "课程名称：{$v[1]}\n姓名：{$v[2]}\n考试时间：\n{$v[3]}\n考试地点：{$v[4]}\n座位号：{$v[5]}\n------------------\n";
				}
			}
			if ($kaoshi == "") {
				$string = "你在逗我么，这学期你根本就没有挂科。";
				return $string;
			} else {
				return $kaoshi;
			}
		} else {
			$string = "你还没有绑定学号吧，赶快输入’绑定’试试 ";
			return $string;
		}
	}

	private function bangoff() {
		$openid = $this -> fromUsername;
		// $this -> getsql();
		$sql = "DELETE FROM account WHERE openid ='$openid' ";
		$mysqli = new mysqli("qdm1535271.my3w.com", "qdm1535271", "WSCYY00435437", "qdm1535271_db");
		$mysqli -> query("SET NAMES 'utf8'");
		$mysqli -> query($sql);
		$content = "你已解除学号的绑定！";
		return $content;

	}

	private function get_td_array($table) {
		$table = preg_replace("/<table[^>]*?>/is", "", $table);
		$table = preg_replace("/<tr[^>]*?>/si", "", $table);
		$table = preg_replace("/<td[^>]*?>/si", "", $table);
		$table = str_replace("</tr>", "{tr}", $table);
		$table = str_replace("</td>", "{td}", $table);
		//去掉 HTML 标记
		$table = preg_replace("'<[/!]*?[^<>]*?>'si", "", $table);
		//去掉空白字符
		$table = preg_replace("'([rn])[s]+'", "", $table);
		$table = str_replace(" ", "", $table);
		$table = str_replace(" ", "", $table);
		$table = str_replace("&nbsp;", "", $table);

		$table = explode('{tr}', $table);
		array_pop($table);
		foreach ($table as $key => $tr) {
			$td = explode('{td}', $tr);
			$td = explode('{td}', $tr);
			array_pop($td);
			$td_array[] = $td;
		}
		return $td_array;
	}

	private function get_kb_array($table) {
		$table = preg_replace("/<table[^>]*?>/is", "", $table);
		$table = preg_replace("/<tr[^>]*?>/si", "", $table);
		$table = preg_replace("/<td[^>]*?>/si", "", $table);
		$table = str_replace("</tr>", "{tr}", $table);
		$table = str_replace("</td>", "{td}", $table);
		$table = str_replace("<br><br>", "\n\n", $table);
		$table = str_replace("<br>", "\n", $table);
		//去掉 HTML 标记
		$table = preg_replace("'<[/!]*?[^<>]*?>'si", "", $table);
		//去掉空白字符
		$table = preg_replace("'([rn])[s]+'", "", $table);
		$table = str_replace(" ", "", $table);
		$table = str_replace(" ", "", $table);
		$table = str_replace("&nbsp;", "", $table);

		$table = explode('{tr}', $table);
		array_pop($table);
		foreach ($table as $key => $tr) {
			$td = explode('{td}', $tr);
			$td = explode('{td}', $tr);
			array_pop($td);
			$td_array[] = $td;
		}
		return $td_array;
	}

	private function checkSignature() {
		// you must define TOKEN by yourself
		if (!defined("TOKEN")) {
			throw new Exception('TOKEN is not defined!');
		}

		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);

		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}

}
?>
