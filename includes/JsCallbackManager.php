<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsCallbackManager
 *
 * @author lucio
 */
class JsCallbackManager
{

  private $script_handle;
  private $target;

  /**
   * @var $script_params_callbacks array
   */
  private $script_params_callbacks;


  public function __construct($target)
  {
    $this->target = $target;
    $this->script_handle = 'wpv_ajax_'.$target;
    $this->script_params_callbacks = array();
  }
  
  /**
   * This function calls the registered callbacks in the same order they were
   * registered. See registerScriptParamsCallback in this class.
   * This function is intended to be executed during the wp_enqueue_scripts hook.
   * This function calls each callback, it provides it with the registered
   * params and it expects a array as a result of calling it.
   * The resulting array must contain a JS event name (such as 'load', 'click'
   * and so on) and other parameters that the $(document).ready JS function will
   * receive, parse and execute accordingly. 
   * See wpvacancy-public.js for details.
   * jshooks_params is the vector that makes the communication possible: it
   * is the resulting array of arrays: actually it is just a pair whose key is
   * always 'events' and whose value is always the array of the callbacks results.
   * @param array $hooks_data_events
   */
  public function callRegisteredCallbacks()
  {
    $hooks_data_events = array();

    foreach ($this->script_params_callbacks as $callback_pack)
    {
      $callback = $callback_pack['call'];
      $params = $callback_pack['params'];
      $cbarr = $callback($params);
      array_unshift($cbarr, $this->target);
      $hooks_data_events[count($hooks_data_events)] = $cbarr;
    }

    $hooks_data = array('events' => $hooks_data_events);

    wp_localize_script($this->script_handle, "jshooks_params", $hooks_data);
    wp_enqueue_script($this->script_handle);
  }

  /**
   * This function saves the callable so that the wp_enqueue_scripts event will
   * call it afterwards (see callRegisteredCallbacks function in this class). 
   * This registerScriptParamsCallback function is intened to be used in 
   * constructors or other functions that are executed before said event takes
   * place and that need to hook that event, but in a guaranteed call order.
   * The callRegisteredCallbacks  function guaratees that the registered 
   * callbacks will be called in the same order they were registered (FIFO order).
   * Maybe WP code that handles hooks does just the same, but, since, even if it
   * did, that's not documented behavior, we cannot rely upon what it 
   * incidentally does as of today.
   * @param Callable $callable_arg The callback
   * @param array $params The params the callback will receive
   * @return boolean is_callable($callable_arg)
   */
  public function registerScriptParamsCallback($callable_arg, array $params = array())
  {
    if (is_callable($callable_arg))
      array_push($this->script_params_callbacks, array('call' => $callable_arg, 'params' => $params));
    return is_callable($callable_arg);
  }
  
  public function getScriptHandle()
  {
    return $this->script_handle;
  }
  
  public function enqueueWPApi()
  {
    $wpapifileurl = plugin_dir_url(__FILE__) . '../public/js/wpapi.min.js';
    $wpapihandle = "wpv-node-wpapi";
    wp_register_script($wpapihandle, $wpapifileurl);
    wp_enqueue_script($wpapihandle);    
  }
}
