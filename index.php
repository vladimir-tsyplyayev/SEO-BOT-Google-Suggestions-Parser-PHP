<?php

// Suggestions parser v1.3
// (c) digg 2014

// http://127.0.0.1/path_to_parser/index.php

set_time_limit(0);
ini_set('display_errors', 1);
ini_set('memory_limit', '12156M');

$lang_tmp = file("settings/language.txt");
$lang = trim($lang_tmp[0]);
$timeout_tmp = file("settings/timeout.txt");
$timeout = trim($timeout_tmp[0]);
$keys_add = file("settings/keys_add.txt");

if(!isset($_GET['s'])) {$s=0;} else {$s=trim($_GET['s'])*1;}

    $key_adds2 = array();
    foreach($keys_add as $ik => $ka){
    	$key_adds2[$ik] = trim($ka).' ';

    }

if(!file_exists('temp/temp_keywords.txt') || $s==0){
	$file = file("settings/keywords.txt");
	$fil = fopen('temp/temp_keywords.txt',"w");
	foreach ($file as $keyword) {
		fputs ($fil, trim($keyword).'||'.trim($keyword).'||
');
		foreach ($key_adds2 as $v) {
			fputs ($fil, $v.trim($keyword).'||'.trim($keyword).'||'.trim($v).'
');
			$words = split(' ', trim($keyword));
			for ($iw = 0; $iw < substr_count(trim($keyword), ' '); $iw++) {
				$new_key = '';
				foreach ($words as $iww => $word) {
					$new_key .= $word.' ';
					if($iw == $iww){
						$new_key .= $v;
					}
				}				
				fputs ($fil, trim($new_key).'||'.trim($keyword).'||'.trim($v).'
');
			}
			fputs ($fil, trim($keyword).' '.trim($v).'||'.trim($keyword).'||'.trim($v).'
');
		}
	}
	fclose($fil);
	copy('temp/temp_keywords.txt','temp/temp_keywords2.txt');

}
$file = file("temp/temp_keywords.txt");
$file2 = file("temp/temp_keywords2.txt");

$treads_tmp = file("settings/treads.txt");
$treads = trim($treads_tmp[0]);
$treads2 = $treads;
if($treads>count($file)){
	$treads=count($file);
}
if($treads2>count($file2)){
	$treads2=count($file2);
}

$proxy = file("settings/proxy.txt");
if(!file_exists('temp/temp_proxy.txt') || $s==0){
	copy('settings/proxy.txt','temp/temp_proxy.txt');
}else{
	$proxy = file("temp/temp_proxy.txt");
}
if(!file_exists('temp/temp_proxy2.txt') || $s==0){
	copy('settings/proxy.txt','temp/temp_proxy2.txt');
}else{
	$proxy2 = file("temp/temp_proxy2.txt");
}

//////////////////////////////////
// FUNCTIONS >>>>>>>>>>>>>>>>>>>>>----------------------------------------------

function multi_take_html($url, $proxy, $treads)
{
	global $timeout;

	$result = array();

$multi_handle = null;
$curl_handles = array();
$options = array();

$multi_handle = curl_multi_init();

$q=0;
$prs=0;
for($key=0;$key<=$treads;$key++){
	if(isset($url[$key])){
		if(strlen(trim($url[$key]))>0){
         	$curl_handles[$q] = curl_init($url[$key]);
	    	if(preg_match('/https\:\/\//i',$url[$key])){
	    		$options[$q] = array (
					CURLOPT_TIMEOUT => $timeout,
					CURLOPT_FOLLOWLOCATION => TRUE,
	     			CURLOPT_RETURNTRANSFER => TRUE,	
	     			CURLOPT_SSL_VERIFYPEER => 0,
	     			CURLOPT_PROXY => trim($proxy[$key])
                    );
	    	}else{
	    		$options[$q] = array (
	    			CURLOPT_HEADER => 1,
					CURLOPT_TIMEOUT => $timeout,
					CURLOPT_FOLLOWLOCATION => TRUE,
	     			CURLOPT_RETURNTRANSFER => TRUE,	     			
	     			CURLOPT_PROXY => trim($proxy[$key])
                    );
    		}
    		curl_setopt_array($curl_handles[$q], $options[$q]);
	    	curl_multi_add_handle($multi_handle, $curl_handles[$q]);
	    	$q++;
    	}
	}
}

do { curl_multi_exec($multi_handle, $running);
} while ($running > 0);

for ($n = 0; $n < count($curl_handles); $n++){
    $result[$n] = curl_multi_getcontent($curl_handles[$n]);
}

for($key=0;$key<count($curl_handles);$key++){
    curl_multi_remove_handle($multi_handle,$curl_handles[$key]);
    curl_close($curl_handles[$key]);
}
curl_multi_close($multi_handle);

return $result;
}

// END FUNCTIONS >>>>>>>>>>>>>>>>>>>>>------------------------------------------
//////////////////////////////////////


if(count($file)>0){

    $url2google = 'http://google.com/complete/search?client=youtube&hl='.$lang.'&callback=callback&q=';

    $url = array();
    for($i=0;$i<=$treads;$i++){
    	if(isset($file[$i])){
			$url_parts = explode('||',trim($file[$i]));
    	 	$url[$i] = $url2google.urlencode($url_parts[0]);  	 	
    	}
    }
    $proxy1 = array();
    for($i=0;$i<=$treads;$i++){
    	 $proxy1[$i]=trim($proxy[$i]);
    }

    $result = multi_take_html($url, $proxy1, $treads);

    $sugg_data = '';
    $good_proxy = 0;
    $fil = fopen('temp/temp_keywords.txt',"w");
    $fil2 = fopen('temp/temp_proxy.txt',"w");
    $filok = fopen('results/result.txt',"a");
    foreach($file as $i => $v){
    	if($i<$treads){
    		if(strlen(trim($result[$i]))>0){
  
		    	if(preg_match('/sorry/i',$result[$i]) || preg_match('/404/i',$result[$i]) 
		    		|| preg_match('/unavailable/i',$result[$i]) || preg_match('/error/i',$result[$i])
		    		|| preg_match('/403/i',$result[$i]) || preg_match('/forbidden/i',$result[$i])
		    	){
		        	fputs ($fil, trim($file[$i]).'
');
		        }else{
					
					preg_match_all('#\"\,\[(.*?)\]\]#i',$result[$i],$res_tmp2);
					if(strlen($res_tmp2[1][0])>0){
						preg_match_all('#\[\"(.*?)\"\,#i',$res_tmp2[1][0],$sugg_tmp);
		        		foreach($sugg_tmp[1] as $sugg_tmp_1){
		        			if(strlen(trim($sugg_tmp_1))>0){
		        				$here=0;    					
		        				$words = explode(' ', trim($sugg_tmp_1));
		        				$url_parts = explode('||',trim($file[$i]));
	    	    				if(strlen($url_parts[2])==1){
	        						foreach ($words as $wrds) {
       									if(strcmp(trim($wrds), $url_parts[2])==0){
       										$here=1;
	        							}
	        						}
	        					}
		        				if($here==0){		        					
		        					if(!strcmp(trim($sugg_tmp_1), trim($v))==0){
		        						fputs ($filok, trim($sugg_tmp_1).'||'.$url_parts[1].'||'.$url_parts[2].'
');
		        					}
		        				}
		        			}
		        		}
		        	}
		        	
		        	$good_proxy++;
		        	if(strlen(trim($proxy[$i]))>0){
		        		fputs ($fil2, trim($proxy[$i]).'
');
		        	}
		        }
	        }else{
	        	fputs ($fil, trim($file[$i]).'
');
	        }
	    }else{
	        fputs ($fil, trim($file[$i]).'
');
	        if(strlen(trim($proxy[$i]))>0){
	        	fputs ($fil2, trim($proxy[$i]).'
');
	        }
	    }
    }

    fclose($fil);
    fclose($filok);

    for($j=$i;$j<=count($proxy);$j++){
    	if(isset($proxy[$j])){
			if(strlen(trim($proxy[$j]))>0){
				fputs ($fil2, trim($proxy[$j]).'
');
			}
		}
    }
    fclose($fil2);

	if(count($proxy)==0){
		copy('settings/proxy.txt','temp/temp_proxy.txt');
		print('All proxy died :-(');
	}



}


if(count($file2)>0){

/*

https://www.google.com.ua/s?espv=2&biw=1920&bih=965&sclient=psy-ab&q=php%20%252&pbx=1&bav=on.2,or.r_qf.&fp=b37d9247ec40ba36&ion=1&pf=p&bs=1&gs_rn=54&gs_ri=psy-ab&pq=php%20%252&cp=6&gs_id=1n&xhr=t&es_nrs=true&oq=&gs_l=&tch=1&ech=3&psi=s8UrVOOFJ4XQygOepoKoBA.1412159419670.3

*/


	//$url2google = 'http://suggestqueries.google.com/complete/search?output=firefox&client=firefox&hl='.$lang.'&q=';
//	$url2google = 'https://www.google.com.ua/s?espv=2&biw=1920&bih=965&sclient=psy-ab&pbx=1&bav=on.2,or.r_qf.&fp=b37d9247ec40ba36&ion=1&pf=p&bs=1&gs_rn=54&gs_ri=psy-ab&cp=6&gs_id=1n&xhr=t&es_nrs=true&oq=&gs_l=&tch=1&ech=3&psi=s8UrVOOFJ4XQygOepoKoBA.1412159419670.3&q=';

$url2google = 'https://www.google.com/s?es_sm=93&biw=1920&bih=965&sclient=psy-ab&pbx=1&bav=on.2,or.r_qf.&bvm=bv.76477589,d.bGQ&fp=b37d9247ec40ba36&pf=p&gs_rn=54&gs_ri=psy-ab&pq=[parent_url]&cp=29&gs_id=d0&xhr=t&es_nrs=true&oq=&gs_l=&tch=1&ech=56&psi=D7orVOqGMcG5ygOxl4CgCQ.1412151795908.1&q=';

//$url2google = 'https://www.google.com.ua/s?es_sm=93&sclient=psy-ab&pbx=1&bav=on.2,or.r_qf.&pf=p&gs_rn=54&gs_ri=psy-ab&pq=[parent_url]&cp=29&gs_id=d0&xhr=t&es_nrs=true&oq=&gs_l=&tch=1&ech=56&q=';

	//$url2google = 'https://www.google.com/s?sclient=psy-ab&q=';

    $url = array();
    $proxy1 = array();
    for($i=0;$i<=$treads;$i++){
    	if(isset($file2[$i])){
    		$url_parts = explode('||',trim($file2[$i]));
    	 	$url[$i]=str_replace('[parent_url]',urlencode($url_parts[1]),$url2google).urlencode($url_parts[0]);
    	}
    }
    for($i=0;$i<=$treads;$i++){
    	 $proxy3[$i]=trim($proxy2[$i]);
    }

    $result = multi_take_html($url, $proxy3, $treads2);

    $sugg_data = '';
    $good_proxy2 = 0;
    $fil = fopen('temp/temp_keywords2.txt',"w");
    $fil2 = fopen('temp/temp_proxy2.txt',"w");
    $filok = fopen('results/result.txt',"a");
    foreach($file as $i => $v){
    	if($i<$treads){
    		if(strlen(trim($result[$i]))>0){
//print('<pre>'.$url[$i].'<br>'.$result[$i].'<Br></pre>');
		    	if(preg_match('/sorry/i',$result[$i]) || preg_match('/404/i',$result[$i]) || preg_match('/unavailable/i',$result[$i])
		    	 || preg_match('/error/i',$result[$i])
		    	 || preg_match('/400 Bad Request/i',$result[$i])	
		    	 || preg_match('/503 Too many open connections/i',$result[$i])			    	 	    	  
		    	 || preg_match('/403/i',$result[$i]) || preg_match('/forbidden/i',$result[$i])
		    	){
		        	fputs ($fil, trim($file2[$i]).'
');
		        }else{					

					preg_match_all('#\[\[(.*?)\]\]#i',$result[$i],$res_tmp2);
					if(strlen($res_tmp2[1][0])>0){
						preg_match_all('#"(.*?)\\,#i',$res_tmp2[1][0],$sugg_tmp);
		        		foreach($sugg_tmp[1] as $sugg_tmp_1){
		        			$sugg_tmp_1 = str_replace('\"', '', $sugg_tmp_1);		        			
		        			if(strlen(trim($sugg_tmp_1))>0){
		        				$here=0;    					
		        				$words = explode(' ', trim($sugg_tmp_1));
		        				$url_parts = explode('||',trim($file2[$i]));
	    	    				if(strlen($url_parts[2])==1){
	        						foreach ($words as $wrds) {
       									if(strcmp(trim($wrds), $url_parts[2])==0){
       										$here=1;
	        							}
	        						}
	        					}
	        					if(preg_match('/\\\\u00/i',trim($sugg_tmp_1))){
		        					$here=1;
		        				}
		        				if($here==0){		        					
		        					if(!strcmp(trim($sugg_tmp_1), trim($v))==0){
		        						fputs ($filok, trim($sugg_tmp_1).'||'.$url_parts[1].'||'.$url_parts[2].'
');
		        					}
		        				}
		        			}
		        		}
		        	}
		        	
		        	$good_proxy2++;
		        	if(strlen(trim($proxy2[$i]))>0){
		        		fputs ($fil2, trim($proxy2[$i]).'
');
		        	}
		        }
	        }else{
	        	fputs ($fil, trim($file2[$i]).'
');
	        }
	    }else{
	        fputs ($fil, trim($file2[$i]).'
');
	        if(strlen(trim($proxy2[$i]))>0){
	        	fputs ($fil2, trim($proxy2[$i]).'
');
	        }
	    }
    }
    fclose($fil);
    fclose($filok);

    for($j=$i;$j<=count($proxy2);$j++){
    	if(isset($proxy2[$j])){
			if(strlen(trim($proxy2[$j]))>0){
				fputs ($fil2, trim($proxy2[$j]).'
');
			}
		}
    }
    fclose($fil2);

	if(count($proxy2)==0){
		copy('settings/proxy.txt','temp/temp_proxy2.txt');
		print('All proxy2 died :-(');
	}


}



/*
$proxy = file('temp/temp_proxy.txt');
shuffle($proxy);
$fil = fopen('temp/temp_proxy.txt',"w");
foreach ($proxy as $v) {
	if(strlen(trim($v))>0){
		fputs ($fil, trim($v).'
');
	}
}
fclose($fil);

$proxy2 = file('temp/temp_proxy2.txt');
shuffle($proxy2);
$fil = fopen('temp/temp_proxy2.txt',"w");
foreach ($proxy2 as $v) {
	if(strlen(trim($v))>0){
		fputs ($fil, trim($v).'
');
	}
}
fclose($fil);
$proxy2 = file('temp/temp_proxy2.txt');
if(!count($proxy2)>0){
	copy('settings/proxy.txt','temp/temp_proxy2.txt');
}

$file = file('temp/temp_keywords.txt');
shuffle($file);
$fil = fopen('temp/temp_keywords.txt',"w");
foreach ($file as $v) {
	if(strlen(trim($v))>0){
		fputs ($fil, trim($v).'
');
	}
}
fclose($fil);

$file2 = file('temp/temp_keywords2.txt');
shuffle($file2);
$fil = fopen('temp/temp_keywords2.txt',"w");
foreach ($file2 as $v) {
	if(strlen(trim($v))>0){
		fputs ($fil, trim($v).'
');
	}
}
fclose($fil);
*/


	if(file_exists('results/result.txt')){
		$result1 = file('results/result.txt');
	}else{
		$result1 = array();
	}
	print('<title>Google suggestions parser</title><h1>Google suggestions parser</h1>		
		Keywords to check: '.(count($file)+count($file2)).'<br>
		New keywords found: '.count($result1).'<br>
		Live proxy: '.count($proxy).', '.count($proxy2).'<br>
		Language: '.$lang.'<br>
		Treads: '.$treads.', '.$treads.'<br>
		Good proxy: '.$good_proxy.', '.$good_proxy2.'<br><br>Status: 
		');

	$s++;
	if(count($file)>0 || count($file2)>0){
		print('Parser working...<br><br>
			<a href="index.php">Restart</a>
			<script>
		window.location.replace("index.php?s='.$s.'");
		</script>');
	}else{
		if(file_exists('results/result.txt')){
			$res = file('results/result.txt');
			foreach ($res as $i => $v) {
				$url_parts = explode('||',$v);
				$res2[$i] = $url_parts[0];
			}
			$result = array_unique($res2);
			$result_file_name = 'results/result_unic_'.date("d_m_Y_h_i_s").'.txt';
			if(count($result)>0){
				if(strlen(trim($result[0]))){
					$fil = fopen($result_file_name,"w");
					foreach ($result as $v) {
						if(strlen(trim($v))>0){
							fputs ($fil, trim($v).'
');
						}
					}
				}
			}
			fclose($fil);
			$new_keywords_file_name = 'temp/keywords/keywords_bc_'.date("d_m_Y_h_i_s").'.txt';
			if(file_exists($new_keywords_file_name)){
				$new_keywords_file_name = 'temp/keywords/keywords_bc_'.date("d_m_Y_h_i_s").'_2.txt';
			}
			copy('settings/keywords.txt',$new_keywords_file_name);
			if(count($result)>0){
				if(strlen(trim($result[0]))){			
					//copy($result_file_name,'settings/keywords.txt');
					copy('settings/penegra.txt','settings/keywords.txt');					
				}
			}
			unlink('temp/temp_keywords.txt');
		}		
		print('DONE!');
		print('<script>
		window.location.replace("index.php?s=0");
		</script>');
	}


?>
