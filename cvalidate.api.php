<?php
/**
 * @file cvalidate.api.php
 */

/**
 * Validating name format.
 * ex. if input is "Albert Liu" or "Liu, Albert"
 * it will returnt a array like:
 * array("Albert", "Liu")
 */
function cvalidate_name($str) {
  $str = _cvalidate_filter($str, 'trim');
  $str = str_replace(array("\r","\n"),'',$str);
  if (empty($str)) {
    return FALSE;
  }
  if (preg_match("/[a-zA-Z]/", $str)) { // check for english name
    if (preg_match("/[,]/", $str)) { // has comma will be reverse
      // $name = array_reverse(preg_split("/[\s,]+/", $str));
      $name = explode(',', $str);
    }
    else { // has space
      $name = array_reverse(preg_split("/[\s,]+/", $str));
    }

  }
  else { // check for chinese name
    $str = _cvalidate_filter($str);
    $len = mb_strlen($str, 'UTF-8');

    if ($len == 2) {
      return array(mb_substr($str, 0, 1, 'UTF-8'), mb_substr($str, 1, 1, 'UTF-8'));
    }
    else if ($len == 3 || $len == 4) {
      $l_name = mb_substr($str, -2, 2, 'UTF-8');
      $f_name = preg_split('/'.$l_name.'/', $str);
      $name[] = $f_name[0];
      $name[] = $l_name;
    }
    else {
      return FALSE;
    }

  }
  foreach ($name as $key => $value) {
    $name[$key] = trim($value);
  }
  return $name;
}

/**
 * Validating email format.
 * @see http://php.net/manual/en/function.checkdnsrr.php
 */
function cvalidate_email($input, $checkDNS = FALSE) {
  $email = _cvalidate_filter($input);
  if (empty($email) || !preg_match("/@/", $email)) {
    return FALSE;
  }
  $email = explode('@', $email, 2);
  $user_id = $email[0];
  $user_domain = $email[1];
  $total_domain = array(
    'yahoo.com.tw',
    'yahoo.com',
    'gmail.com',
    'hotmail.com',
    'msn.com'
  );
  $similar = array();
  foreach ($total_domain as $domain) {
    similar_text($domain, $user_domain, $percent);
    array_push($similar, array('domain' => $domain, 'percent' => $percent));
  }
  $token = NULL;
  $count = count($similar);
  for ($i = 0; $i < $count; $i++) {
    if ($count && $similar[$i]['percent'] > $similar[$i + 1]['percent']) {
      $similar[$i + 1] = $similar[$i];
      $token = $similar[$i + 1];
    }
  }
  return $token['percent'] > 90 ? $user_id . '@' . $token['domain'] : $input;
}

/**
 * Validating birthday format.
 */
function cvalidate_birthday($str) {
  $str = _cvalidate_filter($str);
  if (empty($str)) {
    return FALSE;
  }
  if (!strtotime($str)) {
    return FALSE;
  }
  if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s*(\d+:\d+:\d+)?$/",$str)){
    return FALSE;
  }
  $bir = date("Y-m-d", strtotime($str));
  return $bir;
}

/**
 * Validating mobile number.
 */
function cvalidate_mobile($str) {
  $str = _cvalidate_filter($str);
  if (empty($str)) {
    return FALSE;
  }
  $append = '';
  if (strstr($str, ',')) {
    $str = str_replace(',', '#', $str);
  }
  $number = $str;
  if (strstr($str, '#')) {
    list($number, $append) = explode('#', $str, 2);
    $append = '#'. $append;
  }
  if (preg_match("/^9/", $number)) {
    // missing leading zero
    $number = '0'.$number;
  }
  if (preg_match("/^0/", $number)) {
    $num = preg_replace('/[^0-9-]/', '', $number);
    if ($num != $number) {
      return FALSE;
    }
    $num = preg_replace('/[^0-9]/', '', $number);
    if (strlen($num) < 10) {
      return FALSE;
    }
    $phone = $number; 
  }
  elseif (preg_match("/^+/", $number)) {
    if (preg_match("/^+886-?/", $number)) {
      $number = str_replace('+886', '0', $number);
      $number = preg_replace('/[^0-9-]/', '', $number);
      $phone = $number;
    }
    else {
      $phone = $number;
    }
  }
  else {
    return FALSE;
  }
  $phone .= $append;
  if (!preg_match('/^[0+][0-9-]*(#.*)?$/u', $phone)) {
    return FALSE;
  }
  return $phone;
}

/**
 * Validating telephone number.
 * @see http://countrycode.org/taiwan
 */
function cvalidate_telephone($str) {
  $str = _cvalidate_filter($str);
  if (empty($str)) {
    return FALSE;
  }
  if (strstr($str, ',')) {
    $str = str_replace(',', '#', $str);
  }
  $number = $str;
  if (strstr($str, '#')) {
    list($number, $append) = explode('#', $str, 2);
    $append = '#'. $append;
  }
  if (preg_match("/^0/", $number)) {
    $num = preg_replace('/[^0-9-]/', '', $number);
    if ($number != $num) {
      return FALSE;
    }
    $phone = $number; 
  }
  elseif (preg_match("/^+/", $number)) {
    if (preg_match("/^+886-?/", $number)) {
      $number = str_replace('+886', '0', $number);
      $number = preg_replace('/[^0-9-]/', '', $number);
      $phone = $number;
    }
    else {
      $phone = $number;
    }
  }
  else {
    return FALSE;
  }
  $phone .= $append;
  if (!preg_match('/^[0+][0-9-]*(#.*)?$/u', $phone)) {
    return FALSE;
  }
  return $phone;
}


/**
 * Validating personal identity.
 * @see http://home.csjh.tcc.edu.tw/phpbbinf/viewtopic.php?p=22564&sid=4b3146725041db9dcc43efe4cc821aae
 */
function cvalidate_pid($pid) {
  $pid = strtoupper(_cvalidate_filter($pid));
  if (empty($pid)) {
    return FALSE;
  }
  $ereg_pattern = "^[A-Z]{1}[12]{1}[[:digit:]]{8}$";
  if (!ereg($ereg_pattern, $pid)) {
    return false;
  }
  $wd_str = "BAKJHGFEDCNMLVUTSRQPZWYX0000OI";
  $d1 = strpos($wd_str, $pid[0]) % 10;
  $sum = 0;
  for($ii = 1; $ii < 9; $ii++) {
    $sum += (int)$pid[$ii]*(9-$ii);
  }
  $sum += $d1 + (int)$pid[9];
  if ($sum%10 != 0) {
    return false;
  }
  return $pid;
}

/**
 * Talk to Google Maps and return a json of address.
 * @see https://developers.google.com/maps/documentation/geocoding/#GeocodingResponses
 */
function cvalidate_address($full_address) {
  $full_address = _cvalidate_filter($full_address);
  if (empty($full_address)) {
    return FALSE;
  }
  $full_address = str_replace(
    //把台換成臺
    array('台北縣', '台北市','台中市','台中縣', '台南市', '台南縣', '台東市', '台東縣'),
    array('臺北縣', '臺北市','臺中市','臺中縣', '臺南市', '臺南縣', '臺東市', '臺東縣'),
    $full_address
  );
  //把五都的鄉鎮市換成區
  $full_address = preg_replace('/((臺北縣|桃園縣|臺中縣|臺南縣|高雄縣).+)(市|鄉|鎮)/','$1區', $full_address);
  $full_address = str_replace(
    //把五都升格
    array('臺北縣','桃園縣','臺中縣','臺南縣','高雄縣'),
    array('新北市', '桃園市', '臺中市','臺南市','高雄市'),
    $full_address
  );
  $returns = array(
    'zip' => '',
    'city' => '',
    'region' => '',
    'street' => '',
  );
  // if count zip 5 will separate to 3 + 2
  $zip = $matches = array();
  if (preg_match("/^([0-9]{3})-?([0-9]{2})?/", $full_address, $matches)) {
    if(!empty($matches[1])){
      $zip[0] = $matches[1];
    }
    if(!empty($matches[2])){
      $zip[1] = $matches[2];
    }
    $returns['zip'] = $zip;
    $zip_orig = $matches[0];
  }

  // now check if we have zip
  if(!empty($zip[0])){
    $ary =  _cvalidate_zip($zip[0]);
    $returns['city'] = reset($ary);
    $returns['region'] = key($ary);
  }
  else{
    //切出三字的區，如：中正區，避免平鎮區、前鎮區被鎮切開
    $matches = array();
    preg_match('/^(.{2}(?:市|縣))(.{2}(?:區))/u', $full_address, $matches);
    if(!empty($matches[1])){
      $returns['city'] = $matches[1];
    }
    if(!empty($matches[2])){
      $returns['region'] = $matches[2];
    }else{
      //切出二字的鄉鎮市區，如：東區
      preg_match('/^(.{2}(?:市|縣))(.{1}(?:鄉|鎮|市|區))/u', $full_address, $matches);
      if(!empty($matches[1])){
        $returns['city'] = $matches[1];
      }
      if(!empty($matches[2])){
        $returns['region'] = $matches[2];
      }else{
        //切出三字的鄉鎮市區
        preg_match('/^(.{2}(?:市|縣))(.{2}(?:鄉|鎮|市|區))/u', $full_address, $matches);
        if(!empty($matches[1])){
          $returns['city'] = $matches[1];
        }
        if(!empty($matches[2])){
          $returns['region'] = $matches[2];
        }else{
          //切出四字的鄉鎮市區，如：那瑪夏區
          preg_match('/^(.{2}(?:市|縣))(.{3}(?:鄉|鎮|市|區))/u', $full_address, $matches);
          if(!empty($matches[1])){
            $returns['city'] = $matches[1];
          }
          if(!empty($matches[2])){
            $returns['region'] = $matches[2];
          }
        }
      }
    }
    if (empty($returns['zip'])) {
      $returns['zip'] = _cvalidate_zip(NULL, $returns['city'], $returns['region']);
    }
  }

  $street = $full_address;
  if(!empty($zip_orig)){
    $street = str_replace($zip_orig, '', $street);
  }
  $returns['street'] = str_replace(
    array($returns['city'], $returns['region']),
    array('', ''),
    $street
  );

  return $returns;
}

/**
 * Remove space.
 * @param $str
 *   input string
 * @param $op
 *   all  - strip all space
 *   trim - trim left and right space
 */
function _cvalidate_filter($str, $op = 'all') {
  if (empty($str)) {
    return FALSE;
  }
  switch ($op) {
    case 'all':
      $str = str_replace(' ', '', $str);
      $str = str_replace('　', '', $str);
      break;
    case 'trim':
      $str = trim($str);
      break;
  }
  return $str;
}

function _cvalidate_zip($code = NULL, $city = NULL, $region = NULL){
  static $zip;
  $zip = array(
200=>array('仁愛區'=>'基隆市',),
201=>array('信義區'=>'基隆市',),
202=>array('中正區'=>'基隆市',),
203=>array('中山區'=>'基隆市',),
204=>array('安樂區'=>'基隆市',),
205=>array('暖暖區'=>'基隆市',),
206=>array('七堵區'=>'基隆市',),
100=>array('中正區'=>'臺北市',),
103=>array('大同區'=>'臺北市',),
104=>array('中山區'=>'臺北市',),
105=>array('松山區'=>'臺北市',),
106=>array('大安區'=>'臺北市',),
108=>array('萬華區'=>'臺北市',),
110=>array('信義區'=>'臺北市',),
111=>array('士林區'=>'臺北市',),
112=>array('北投區'=>'臺北市',),
114=>array('內湖區'=>'臺北市',),
115=>array('南港區'=>'臺北市',),
116=>array('文山區'=>'臺北市',),
207=>array('萬里區'=>'新北市',),
208=>array('金山區'=>'新北市',),
220=>array('板橋區'=>'新北市',),
221=>array('汐止區'=>'新北市',),
222=>array('深坑區'=>'新北市',),
223=>array('石碇區'=>'新北市',),
224=>array('瑞芳區'=>'新北市',),
226=>array('平溪區'=>'新北市',),
227=>array('雙溪區'=>'新北市',),
228=>array('貢寮區'=>'新北市',),
231=>array('新店區'=>'新北市',),
232=>array('坪林區'=>'新北市',),
233=>array('烏來區'=>'新北市',),
234=>array('永和區'=>'新北市',),
235=>array('中和區'=>'新北市',),
236=>array('土城區'=>'新北市',),
237=>array('三峽區'=>'新北市',),
238=>array('樹林區'=>'新北市',),
239=>array('鶯歌區'=>'新北市',),
241=>array('三重區'=>'新北市',),
242=>array('新莊區'=>'新北市',),
243=>array('泰山區'=>'新北市',),
244=>array('林口區'=>'新北市',),
247=>array('蘆洲區'=>'新北市',),
248=>array('五股區'=>'新北市',),
249=>array('八里區'=>'新北市',),
251=>array('淡水區'=>'新北市',),
252=>array('三芝區'=>'新北市',),
253=>array('石門區'=>'新北市',),
260=>array('宜蘭市'=>'宜蘭縣',),
261=>array('頭城鎮'=>'宜蘭縣',),
262=>array('礁溪鄉'=>'宜蘭縣',),
263=>array('壯圍鄉'=>'宜蘭縣',),
264=>array('員山鄉'=>'宜蘭縣',),
265=>array('羅東鎮'=>'宜蘭縣',),
266=>array('三星鄉'=>'宜蘭縣',),
267=>array('大同鄉'=>'宜蘭縣',),
268=>array('五結鄉'=>'宜蘭縣',),
269=>array('冬山鄉'=>'宜蘭縣',),
270=>array('蘇澳鎮'=>'宜蘭縣',),
272=>array('南澳鄉'=>'宜蘭縣',),
290=>array('釣魚台列嶼'=>'宜蘭縣',),
300=>array('新竹市'=>'新竹市', '香山區'=>'新竹市','東區'=>'新竹市', '北區'=>'新竹市',),
302=>array('竹北市'=>'新竹縣',),
303=>array('湖口鄉'=>'新竹縣',),
304=>array('新豐鄉'=>'新竹縣',),
305=>array('新埔鎮'=>'新竹縣',),
306=>array('關西鎮'=>'新竹縣',),
307=>array('芎林鄉'=>'新竹縣',),
308=>array('寶山鄉'=>'新竹縣',),
310=>array('竹東鎮'=>'新竹縣',),
311=>array('五峰鄉'=>'新竹縣',),
312=>array('橫山鄉'=>'新竹縣',),
313=>array('尖石鄉'=>'新竹縣',),
314=>array('北埔鄉'=>'新竹縣',),
315=>array('峨嵋鄉'=>'新竹縣',),
320=>array('中壢區'=>'桃園市',),
324=>array('平鎮區'=>'桃園市',),
325=>array('龍潭區'=>'桃園市',),
326=>array('楊梅區'=>'桃園市',),
327=>array('新屋區'=>'桃園市',),
328=>array('觀音區'=>'桃園市',),
330=>array('桃園區'=>'桃園市',),
333=>array('龜山區'=>'桃園市',),
334=>array('八德區'=>'桃園市',),
335=>array('大溪區'=>'桃園市',),
336=>array('復興區'=>'桃園市',),
337=>array('大園區'=>'桃園市',),
338=>array('蘆竹區'=>'桃園市',),
350=>array('竹南鎮'=>'苗栗縣',),
351=>array('頭份鎮'=>'苗栗縣',),
352=>array('三灣鄉'=>'苗栗縣',),
353=>array('南庄鄉'=>'苗栗縣',),
354=>array('獅潭鄉'=>'苗栗縣',),
356=>array('後龍鎮'=>'苗栗縣',),
357=>array('通霄鎮'=>'苗栗縣',),
358=>array('苑裡鎮'=>'苗栗縣',),
360=>array('苗栗市'=>'苗栗縣',),
361=>array('造橋鄉'=>'苗栗縣',),
362=>array('頭屋鄉'=>'苗栗縣',),
363=>array('公館鄉'=>'苗栗縣',),
364=>array('大湖鄉'=>'苗栗縣',),
365=>array('泰安鄉'=>'苗栗縣',),
366=>array('銅鑼鄉'=>'苗栗縣',),
367=>array('三義鄉'=>'苗栗縣',),
368=>array('西湖鄉'=>'苗栗縣',),
369=>array('卓蘭鎮'=>'苗栗縣',),
400=>array('中區'=>'臺中市',),
401=>array('東區'=>'臺中市',),
402=>array('南區'=>'臺中市',),
403=>array('西區'=>'臺中市',),
404=>array('北區'=>'臺中市',),
406=>array('北屯區'=>'臺中市',),
407=>array('西屯區'=>'臺中市',),
408=>array('南屯區'=>'臺中市',),
411=>array('太平區'=>'臺中市',),
412=>array('大里區'=>'臺中市',),
413=>array('霧峰區'=>'臺中市',),
414=>array('烏日區'=>'臺中市',),
420=>array('豐原區'=>'臺中市',),
421=>array('后里區'=>'臺中市',),
422=>array('石岡區'=>'臺中市',),
423=>array('東勢區'=>'臺中市',),
424=>array('和平區'=>'臺中市',),
426=>array('新社區'=>'臺中市',),
427=>array('潭子區'=>'臺中市',),
428=>array('大雅區'=>'臺中市',),
429=>array('神岡區'=>'臺中市',),
432=>array('大肚區'=>'臺中市',),
433=>array('沙鹿區'=>'臺中市',),
434=>array('龍井區'=>'臺中市',),
435=>array('梧棲區'=>'臺中市',),
436=>array('清水區'=>'臺中市',),
437=>array('大甲區'=>'臺中市',),
438=>array('外埔區'=>'臺中市',),
439=>array('大安區'=>'臺中市',),
500=>array('彰化市'=>'彰化縣',),
502=>array('芬園鄉'=>'彰化縣',),
503=>array('花壇鄉'=>'彰化縣',),
504=>array('秀水鄉'=>'彰化縣',),
505=>array('鹿港鎮'=>'彰化縣',),
506=>array('福興鄉'=>'彰化縣',),
507=>array('線西鄉'=>'彰化縣',),
508=>array('和美鎮'=>'彰化縣',),
509=>array('伸港鄉'=>'彰化縣',),
510=>array('員林市'=>'彰化縣',),
511=>array('社頭鄉'=>'彰化縣',),
512=>array('永靖鄉'=>'彰化縣',),
513=>array('埔心鄉'=>'彰化縣',),
514=>array('溪湖鎮'=>'彰化縣',),
515=>array('大村鄉'=>'彰化縣',),
516=>array('埔鹽鄉'=>'彰化縣',),
520=>array('田中鎮'=>'彰化縣',),
521=>array('北斗鎮'=>'彰化縣',),
522=>array('田尾鄉'=>'彰化縣',),
523=>array('埤頭鄉'=>'彰化縣',),
524=>array('溪州鄉'=>'彰化縣',),
525=>array('竹塘鄉'=>'彰化縣',),
526=>array('二林鎮'=>'彰化縣',),
527=>array('大城鄉'=>'彰化縣',),
528=>array('芳苑鄉'=>'彰化縣',),
530=>array('二水鄉'=>'彰化縣',),
540=>array('南投市'=>'南投縣',),
541=>array('中寮鄉'=>'南投縣',),
542=>array('草屯鎮'=>'南投縣',),
544=>array('國姓鄉'=>'南投縣',),
545=>array('埔里鎮'=>'南投縣',),
546=>array('仁愛鄉'=>'南投縣',),
551=>array('名間鄉'=>'南投縣',),
552=>array('集集鎮'=>'南投縣',),
553=>array('水里鄉'=>'南投縣',),
555=>array('魚池鄉'=>'南投縣',),
556=>array('信義鄉'=>'南投縣',),
557=>array('竹山鎮'=>'南投縣',),
558=>array('鹿谷鄉'=>'南投縣',),
600=>array('嘉義市'=>'嘉義市','東區'=>'嘉義市','西區'=>'嘉義市',),
602=>array('番路鄉'=>'嘉義縣',),
603=>array('梅山鄉'=>'嘉義縣',),
604=>array('竹崎鄉'=>'嘉義縣',),
605=>array('阿里山'=>'嘉義縣',),
606=>array('中埔鄉'=>'嘉義縣',),
607=>array('大埔鄉'=>'嘉義縣',),
608=>array('水上鄉'=>'嘉義縣',),
611=>array('鹿草鄉'=>'嘉義縣',),
612=>array('太保市'=>'嘉義縣',),
613=>array('朴子市'=>'嘉義縣',),
614=>array('東石鄉'=>'嘉義縣',),
615=>array('六腳鄉'=>'嘉義縣',),
616=>array('新港鄉'=>'嘉義縣',),
621=>array('民雄鄉'=>'嘉義縣',),
622=>array('大林鎮'=>'嘉義縣',),
623=>array('溪口鄉'=>'嘉義縣',),
624=>array('義竹鄉'=>'嘉義縣',),
625=>array('布袋鎮'=>'嘉義縣',),
630=>array('斗南鎮'=>'雲林縣',),
631=>array('大埤鄉'=>'雲林縣',),
632=>array('虎尾鎮'=>'雲林縣',),
633=>array('土庫鎮'=>'雲林縣',),
634=>array('褒忠鄉'=>'雲林縣',),
635=>array('東勢鄉'=>'雲林縣',),
636=>array('臺西鄉'=>'雲林縣',),
637=>array('崙背鄉'=>'雲林縣',),
638=>array('麥寮鄉'=>'雲林縣',),
640=>array('斗六市'=>'雲林縣',),
643=>array('林內鄉'=>'雲林縣',),
646=>array('古坑鄉'=>'雲林縣',),
647=>array('莿桐鄉'=>'雲林縣',),
648=>array('西螺鎮'=>'雲林縣',),
649=>array('二崙鄉'=>'雲林縣',),
651=>array('北港鎮'=>'雲林縣',),
652=>array('水林鄉'=>'雲林縣',),
653=>array('口湖鄉'=>'雲林縣',),
654=>array('四湖鄉'=>'雲林縣',),
655=>array('元長鄉'=>'雲林縣',),
700=>array('中西區'=>'臺南市',),
701=>array('東區'=>'臺南市',),
702=>array('南區'=>'臺南市',),
704=>array('北區'=>'臺南市',),
708=>array('安平區'=>'臺南市',),
709=>array('安南區'=>'臺南市',),
710=>array('永康區'=>'臺南市',),
711=>array('歸仁區'=>'臺南市',),
712=>array('新化區'=>'臺南市',),
713=>array('左鎮區'=>'臺南市',),
714=>array('玉井區'=>'臺南市',),
715=>array('楠西區'=>'臺南市',),
716=>array('南化區'=>'臺南市',),
717=>array('仁德區'=>'臺南市',),
718=>array('關廟區'=>'臺南市',),
719=>array('龍崎區'=>'臺南市',),
720=>array('官田區'=>'臺南市',),
721=>array('麻豆區'=>'臺南市',),
722=>array('佳里區'=>'臺南市',),
723=>array('西港區'=>'臺南市',),
724=>array('七股區'=>'臺南市',),
725=>array('將軍區'=>'臺南市',),
726=>array('學甲區'=>'臺南市',),
727=>array('北門區'=>'臺南市',),
730=>array('新營區'=>'臺南市',),
731=>array('後壁區'=>'臺南市',),
732=>array('白河區'=>'臺南市',),
733=>array('東山區'=>'臺南市',),
734=>array('六甲區'=>'臺南市',),
735=>array('下營區'=>'臺南市',),
736=>array('柳營區'=>'臺南市',),
737=>array('鹽水區'=>'臺南市',),
741=>array('善化區'=>'臺南市',),
742=>array('大內區'=>'臺南市',),
743=>array('山上區'=>'臺南市',),
744=>array('新市區'=>'臺南市',),
745=>array('安定區'=>'臺南市',),
800=>array('新興區'=>'高雄市',),
801=>array('前金區'=>'高雄市',),
802=>array('苓雅區'=>'高雄市',),
803=>array('鹽埕區'=>'高雄市',),
804=>array('鼓山區'=>'高雄市',),
805=>array('旗津區'=>'高雄市',),
806=>array('前鎮區'=>'高雄市',),
807=>array('三民區'=>'高雄市',),
811=>array('楠梓區'=>'高雄市',),
812=>array('小港區'=>'高雄市',),
813=>array('左營區'=>'高雄市',),
814=>array('仁武區'=>'高雄市',),
815=>array('大社區'=>'高雄市',),
820=>array('岡山區'=>'高雄市',),
821=>array('路竹區'=>'高雄市',),
822=>array('阿蓮區'=>'高雄市',),
823=>array('田寮鄉'=>'高雄市',),
824=>array('燕巢區'=>'高雄市',),
825=>array('橋頭區'=>'高雄市',),
826=>array('梓官區'=>'高雄市',),
827=>array('彌陀區'=>'高雄市',),
828=>array('永安區'=>'高雄市',),
829=>array('湖內區'=>'高雄市',),
830=>array('鳳山區'=>'高雄市',),
831=>array('大寮區'=>'高雄市',),
832=>array('林園區'=>'高雄市',),
833=>array('鳥松區'=>'高雄市',),
840=>array('大樹區'=>'高雄市',),
842=>array('旗山區'=>'高雄市',),
843=>array('美濃區'=>'高雄市',),
844=>array('六龜區'=>'高雄市',),
845=>array('內門區'=>'高雄市',),
846=>array('杉林區'=>'高雄市',),
847=>array('甲仙區'=>'高雄市',),
848=>array('桃源區'=>'高雄市',),
849=>array('那瑪夏區'=>'高雄市',),
851=>array('茂林區'=>'高雄市',),
852=>array('茄萣區'=>'高雄市',),
900=>array('屏東市'=>'屏東縣',),
901=>array('三地門'=>'屏東縣',),
902=>array('霧臺鄉'=>'屏東縣',),
903=>array('瑪家鄉'=>'屏東縣',),
904=>array('九如鄉'=>'屏東縣',),
905=>array('里港鄉'=>'屏東縣',),
906=>array('高樹鄉'=>'屏東縣',),
907=>array('鹽埔鄉'=>'屏東縣',),
908=>array('長治鄉'=>'屏東縣',),
909=>array('麟洛鄉'=>'屏東縣',),
911=>array('竹田鄉'=>'屏東縣',),
912=>array('內埔鄉'=>'屏東縣',),
913=>array('萬丹鄉'=>'屏東縣',),
920=>array('潮州鎮'=>'屏東縣',),
921=>array('泰武鄉'=>'屏東縣',),
922=>array('來義鄉'=>'屏東縣',),
923=>array('萬巒鄉'=>'屏東縣',),
924=>array('崁頂鄉'=>'屏東縣',),
925=>array('新埤鄉'=>'屏東縣',),
926=>array('南州鄉'=>'屏東縣',),
927=>array('林邊鄉'=>'屏東縣',),
928=>array('東港鎮'=>'屏東縣',),
929=>array('琉球鄉'=>'屏東縣',),
931=>array('佳冬鄉'=>'屏東縣',),
932=>array('新園鄉'=>'屏東縣',),
940=>array('枋寮鄉'=>'屏東縣',),
941=>array('枋山鄉'=>'屏東縣',),
942=>array('春日鄉'=>'屏東縣',),
943=>array('獅子鄉'=>'屏東縣',),
944=>array('車城鄉'=>'屏東縣',),
945=>array('牡丹鄉'=>'屏東縣',),
946=>array('恆春鎮'=>'屏東縣',),
947=>array('滿州鄉'=>'屏東縣',),
950=>array('臺東市'=>'臺東縣',),
951=>array('綠島鄉'=>'臺東縣',),
952=>array('蘭嶼鄉'=>'臺東縣',),
953=>array('延平鄉'=>'臺東縣',),
954=>array('卑南鄉'=>'臺東縣',),
955=>array('鹿野鄉'=>'臺東縣',),
956=>array('關山鎮'=>'臺東縣',),
957=>array('海端鄉'=>'臺東縣',),
958=>array('池上鄉'=>'臺東縣',),
959=>array('東河鄉'=>'臺東縣',),
961=>array('成功鎮'=>'臺東縣',),
962=>array('長濱鄉'=>'臺東縣',),
963=>array('太麻里鄉'=>'臺東縣',),
964=>array('金峰鄉'=>'臺東縣',),
965=>array('大武鄉'=>'臺東縣',),
966=>array('達仁鄉'=>'臺東縣',),
970=>array('花蓮市'=>'花蓮縣',),
971=>array('新城鄉'=>'花蓮縣',),
972=>array('秀林鄉'=>'花蓮縣',),
973=>array('吉安鄉'=>'花蓮縣',),
974=>array('壽豐鄉'=>'花蓮縣',),
975=>array('鳳林鎮'=>'花蓮縣',),
976=>array('光復鄉'=>'花蓮縣',),
977=>array('豐濱鄉'=>'花蓮縣',),
978=>array('瑞穗鄉'=>'花蓮縣',),
979=>array('萬榮鄉'=>'花蓮縣',),
981=>array('玉里鎮'=>'花蓮縣',),
982=>array('卓溪鄉'=>'花蓮縣',),
983=>array('富里鄉'=>'花蓮縣',),
890=>array('金沙鎮'=>'金門縣',),
891=>array('金湖鎮'=>'金門縣',),
892=>array('金寧鄉'=>'金門縣',),
893=>array('金城鎮'=>'金門縣',),
894=>array('烈嶼鄉'=>'金門縣',),
896=>array('烏坵鄉'=>'金門縣',),
209=>array('南竿鄉'=>'連江縣',),
210=>array('北竿鄉'=>'連江縣',),
211=>array('莒光鄉'=>'連江縣',),
212=>array('東引鄉'=>'連江縣',),
880=>array('馬公市'=>'澎湖縣',),
881=>array('西嶼鄉'=>'澎湖縣',),
882=>array('望安鄉'=>'澎湖縣',),
883=>array('七美鄉'=>'澎湖縣',),
884=>array('白沙鄉'=>'澎湖縣',),
885=>array('湖西鄉'=>'澎湖縣',),
817=>array('東沙'=>'南海諸島',),
819=>array('南沙'=>'南海諸島',),
);

  if($code){
    return $zip[$code];
  }
  elseif (!empty($region)) {
    foreach($zip as $z => $area) {
      if (isset($area[$region])) {
        if (!empty($city)) {
          if ($city == $area[$region]) {
            return $z;
          }
        }
        else {
          return $z;
        }
      }
    }
  }
}
