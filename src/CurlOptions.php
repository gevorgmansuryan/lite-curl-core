<?php

namespace Gevman\CurlLite;

class CurlOptions extends CurlOptionAbstract
{
	private static $defaultOptions;

	private $selectedOptions = [];

	private function setDefaultOptions()
	{
		$constants = get_defined_constants(true);
		$curlOpt = array_flip(array_filter($constants['curl'], function($v, $k) {
			return strpos($k, 'CURLOPT_') !== false;
		}, ARRAY_FILTER_USE_BOTH));
		$options = [];
		foreach ($curlOpt as $opt => $value) {
			$val = str_replace('_', '', lcfirst(ucwords(strtolower(str_replace('CURLOPT_', '', $value)), '_')));
			$options[$val] = $opt;
		}
		self::$defaultOptions = $options;
	}

	public function __call($name, $arguments)
	{
		$value = $arguments[0];
		switch ($name) {
			case 'postfields':
				if (is_array($value)) {
					$value = http_build_query($value);
				}
				break;
		}
		if (!self::$defaultOptions) {
			$this->setDefaultOptions();
		}
		if (isset(self::$defaultOptions[$name])) {
			$this->selectedOptions[self::$defaultOptions[$name]] = $value;
		}
		return $this;
	}

	public function getOptions()
	{
		return $this->selectedOptions;
	}

	/**
	 * @method mergeWith
	 * @param CurlOptions $_ [optional]
	 *
	 * @return CurlOptions
	 */
	public function mergeWith(CurlOptions $_)
	{
		/**
		 * @var CurlOptions $options
		 */
		foreach (func_get_args() as $options) {
			foreach ($options->getOptions() as $option => $value) {
				$this->selectedOptions[$option] = $value;
			}
		}
		return $this;
	}
}