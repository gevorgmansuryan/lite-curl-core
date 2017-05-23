<?php

namespace Gevman\CurlLite;

class CurlResponse extends CurlResponseAbstract
{
	private $content;
	private $info;

	private static $infoOpt = [
		'getUrl' => 'url',
		'getContentType' => 'content_type',
		'getHttpCode' => 'http_code',
		'getHeaderSize' => 'header_size',
		'getRequestSize' => 'request_size',
		'getFiletime' => 'filetime',
		'getSslVerifyResult' => 'ssl_verify_result',
		'getRedirectCount' => 'redirect_count',
		'getTotalTime' => 'total_time',
		'getNameLookupTime' => 'namelookup_time',
		'getConnectTime' => 'connect_time',
		'getPretransferTime' => 'pretransfer_time',
		'getSizeUpload' => 'size_upload',
		'getSizeDownload' => 'size_download',
		'getSpeedUpload' => 'speed_upload',
		'getSpeedDownload' => 'speed_download',
		'getUploadContentLength' => 'upload_content_length',
		'getDownloadContentLength' => 'download_content_length',
		'getStarttransferTime' => 'starttransfer_time',
		'getRedirectTime' => 'redirect_time',
		'getCertinfo' => 'certinfo',
		'getPrimaryIp' => 'primary_ip',
		'getPrimaryPort' => 'primary_port',
		'getLocalIp' => 'local_ip',
		'getLocalPort' => 'local_port',
		'getRedirectUrl' => 'redirect_url'
	];

	public function __construct($content, $info)
	{
		$this->content = $content;
		$this->info = $info;
	}

	public function __toString()
	{
		return $this->getContent();
	}

	public function __call($name, $params)
	{
		if (array_key_exists($name, self::$infoOpt)) {
			return $this->info[self::$infoOpt[$name]];
		} else {
			throw new \Exception(sprintf('Call to undefined method %s:%s', __CLASS__, $name));
		}
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return array
	 */
	public function getInfo()
	{
		return $this->info;
	}
}