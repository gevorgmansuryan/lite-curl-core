<?php

namespace Gevman\CurlLite;

class Curl
{
	function isSupported()
	{
		if (!function_exists("curl_init") && !function_exists("curl_setopt") && !function_exists("curl_exec") && !function_exists("curl_close")) {
			return false;
		} else {
			return true;
		}
	}


	private static function getDefaultOptions()
	{
		return [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0'
		];
	}

	private static function addOptions($params)
	{
		$options = [];
		foreach ($params as $option => $value) {
			switch ($option) {
				case CURLOPT_POSTFIELDS:
					if (is_array($value)) {
						$options[] = http_build_query($value);
					} else {
						$options[$option] = $value;
					}
					break;
				default:
					$options[$option] = $value;
			}
		}
		return $options;
	}

    /**
     * @param $from
     * @param $to
     * @param null $name
     * @param bool $keepExt
     * @param null $defaultExt
     * @return null|string
     */
    public static function downloadFile($from, $to, $name = null, $keepExt = true, $defaultExt = null)
    {
        $base = trim(basename(parse_url($from, PHP_URL_PATH)));
        if (!$name) {
            $name = $base;
        }
        if ($keepExt) {
            $ext = pathinfo($base, PATHINFO_EXTENSION);
            if (empty($ext) && $defaultExt) {
                $ext = $defaultExt;
            }
            $name = sprintf('%s.%s', $name, $ext);
        }
        $to = rtrim(trim($to), '/').DIRECTORY_SEPARATOR.$name;
        $ch = curl_init ($from);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $raw = curl_exec($ch);
        curl_close ($ch);
        if(file_exists($to)){
            unlink($to);
        }
        $fp = fopen($to,'x');
        fwrite($fp, $raw);
        fclose($fp);
        return $name;
    }

	/**
	 * @param array $urls
	 *
	 * @return CurlResponseAbstract[]
	 */
	public static function multi(array $urls)
	{
		if (empty($urls)) {
			return [];
		}
		$mh = curl_multi_init();
		foreach ($urls as $num => $url) {
			$params = [];
			$name = 'curl'.$num;
			if (isset($url[1]) && is_array($url[1])) {
				$params = $url[1];
			}
			if (is_array($url) && isset($url[0])) {
				$url = $url[0];
			}
			$$name = curl_init();
			$options = [CURLOPT_URL => $url] + self::getDefaultOptions() + self::addOptions($params);
			curl_setopt_array($$name, $options);
			curl_multi_add_handle($mh, $$name);
		}
		do {
			curl_multi_exec($mh, $running);
			curl_multi_select($mh);
		} while ($running > 0);
		$res = [];
		foreach ($urls as $num => $url) {
			$name = 'curl'.$num;
			$res[$num] = new CurlResponse(curl_multi_getcontent($$name), curl_getinfo($$name));
			curl_multi_remove_handle($mh, $$name);
		}
		curl_multi_close($mh);
		return $res;
	}

	/**
	 * @param $url
	 * @param array $params
	 *
	 * @return CurlResponseAbstract
	 */
	public static function single($url, $params = [])
	{
		$ch = curl_init();
		$options = [CURLOPT_URL => $url] + self::getDefaultOptions() + self::addOptions($params);
		curl_setopt_array($ch, $options);
		$res = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return new CurlResponse($res, $info);
	}
}