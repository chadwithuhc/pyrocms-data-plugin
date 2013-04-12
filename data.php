<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * A template Data management plugin
 * 
 * Used for retrieving data from Streams and getting / setting data within the templating tags
 *
 * @author   cmfolio
 * @website  https://github.com/chadwithuhc/pyrocms-data-plugin
 * @package  Plugins
 * @license  MIT
 * @version  0.5
 */
class Plugin_Data extends Plugin
{

	/**
	 * The 'key' to store data in under cached vars
	 * This can be retrieved by calling `ci()->load->get_var(key)` which returns an array
	 * 
	 * @var string
	 */
	private $_data_array_id = 'data_plugin';


	/**
	 * Default options for getting data from Streams API
	 * Change to your liking
	 * 
	 * @var array
	 */
	private $_default_stream_options = array(
		'namespace' => 'streams',
		'date_by' => 'date',
		'order_by' => 'date',
		'sort' => 'desc',
		'show_upcoming' => 'no'
	);


	/**
	 * Contruct!
	 * 
	 * Create the array if it does not exist
	 */
	public function __construct() {
		if ( is_null($this->_get_all()) ) {
			$this->load->vars($this->_data_array_id, array());
		}
	}


	/**
	 * Get a collection of Stream data
	 *
	 * Gets all of the Streams data matching passed in params
	 * Functions the same as `{{ streams:cycle }}`
	 *
	 * @param string stream
	 * @return array
	 */
	public function stream() {
		if ( is_null($this->attribute('stream')) ) {
			return '';
		}

		# reserve underscore names for passing config options (future enhancement)
		$process = $this->attribute('_process');
		$helper = $this->attribute('_helper');

		$data = $this->streams->entries->get_entries(array_merge($this->_default_stream_options, $this->attributes()));

		return array($data);
	}


	/**
	 * Set a var
	 * 
	 * Set a custom var inside the view or template code that can be retrieved later
	 * Can be used as single tag or tag pair
	 * 
	 * Example (Single Tag):
	 *     {{ data:set key="post_id" value=id }}
	 *     ...
	 *     {{ data:get key="post_id" }}
	 * 
	 * Example (Tag Pair):
	 *     {{ data:set key="author_name" }}{{ author:display_name }}{{ /data:set }}
	 *     ...
	 *     {{ data:get key="author_name" }}
	 * 
	 * Including data from the plugin will pass the set vars to the LEX parser
	 * By default, LEX parser only has access to items in the `$this->load->_ci_cached_vars`
	 *   and does not include any vars in the current "scope".
	 * 
	 * @param string $key         Var name
	 * @param string $value       Var value (strings only)
	 * @param string include_data Do you want to include the data from the plugin in your parsing?
	 * @return string
	 */
	public function set($key = null, $value = null)
	{
		$key = $this->attribute('key', $key);
		$value = $this->attribute('value', $value);
		$include_data = str_to_bool($this->attribute('include_data', false));
		
		if (is_null($key)) { return; }
		# default to content if no value
		if (is_null($value)) { $value = (string) $this->content(); }
		
		# parse content as params?
		if ($this->attribute('parse_params')) {
			$value = $this->parse_parameter(trim($value), $this->_get_all());
		}
		# include our data plugin data
		if ($include_data) {
			$value = $this->parser->parse_string( // parse the blocks content
				trim($value), // content
				$this->_get_all(), // pass in the data from this plugin
				true); // return it
		}
		
		// set
		$this->_set($key, $value);
		
		return;
	}


	/**
	 * Set the actual var
	 *
	 * See `$this->set()` for usage
	 *
	 * @param string  $key    Var name
	 * @param string  $value  Var value
	 */
	private function _set($key, $value) {
		$data = $this->_get_all();
		$data[$key] = $value;
		$this->load->vars($this->_data_array_id, $data);
	}


	/**
	 * Get a var
	 * 
	 * Get a var set with `{{ data:set }}`
	 * Cannot be used as a Tag Pair
	 * 
	 * Example:
	 *     {{ data:set key="author_name" }}{{ author:display_name }}{{ /data:set }}
	 *     ...
	 *     {{ data:get key="author_name" }}
	 * 
	 * Note: You can also get shorthand with `{{ data:var_name }}`. See `$this->_get()` for more info.
	 * 
	 * @param string  key  Var name
	 * @return string
	 */
	public function get($key = null) {
		$key = $this->attribute('key', $key);
		return $this->_get($key);
	}


	/**
	 * Get the actual var
	 *
	 * See `$this->get()` for usage
	 *
	 * @param string  $key          Var name
	 * @param bool    $return_bool  Return bool to check if exists
	 * @return string
	 */
	private function _get($key, $return_bool = false) {
		$data = $this->_get_all();
		$result = isset($data[$key]) ? $data[$key] : null;

		return ($return_bool) ? !is_null($result) : $result;
	}


	/**
	 * Shortcut to get all vars set with `{{ data:set }}`
	 * 
	 * @return mixed
	 */
	private function _get_all(){
		return $this->load->get_var($this->_data_array_id);
	}


	/**
	 * Automatically get any var shorthand
	 *
	 * Equivalent of calling `{{ data:get key="key_name" }}`
	 * Cannot be used as a Tag Pair
	 *
	 * Example:
	 *     {{ data:key_name }}
	 *
	 * @param string $key  Var key
	 * @param array  $data Does nothing.
	 * @return string
	 */
	public function __call($key, $data) {
		return $this->_get($key);
	}


	/**
	 * Check if var exists
	 * 
	 * Only checks vars set with `{{ data:set }}`
	 * 
	 * Example:
	 *     {{ if {data:exists key="foobar"} }}
	 *       ... {{ data:foobar }} ...
	 *     {{ endif }}
	 * 
	 * @param string  key  Var name
	 * @return bool
	 */
	public function exists($key = null) {
		$key = $this->attribute('key', $key);
		return (!is_null($key) and $this->_get($key, true));
	}


	/**
	 * Get a snippet from PyroSnippets
	 * 
	 * Useful when you want to dynamically get a snippet
	 * 
	 * Example:
	 *     // dynamically load an article category intro snippet
	 *     {{ data:snippet id="article_intro_[[ segment_3 ]]" }}
	 * 
	 * @return string
	 */
	public function snippet()
	{
		$id = $this->attribute('id');
		
		$snippets = $this->load->get_var('snippet');
		
		if (is_null($id) or ! isset($snippets[$id]) ) {
			return;
		}
		
		return $snippets[$id];
	}

}

/* EOF */