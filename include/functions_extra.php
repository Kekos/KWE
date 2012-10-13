<?php
/**
 * KWF Functions: here you can add your own global functions
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-10-13
 * @version 2.1
 */

function file_get_remote_contents($url)
  {
  if (ini_get('allow_url_fopen'))
    {
    return file_get_contents($url);
    }
  else
    {
    $curl_handel = curl_init();
    curl_setopt($curl_handel, CURLOPT_URL, $url);
    curl_setopt($curl_handel, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl_handel, CURLOPT_RETURNTRANSFER, true);
    $contents = curl_exec($curl_handel);

    if (curl_errno($curl_handel))
      throw new exception('cURL error: ' . curl_error($ch));
    else
      curl_close($curl_handel);

    return $contents;
    }
  }

function urlModrSite()
  {
  $url = (MOD_REWRITE ? FULLPATH_SITE . '/' : BASE . 'index.php?r=');
  if (func_num_args() > 0)
    $params = (is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args());
  else
    $params = array();

  return $url . urlCreate($params);
  }

function setControllerMenuSession($controllers, $request)
  {
  $nav = array();
  foreach ($controllers as $controller)
    {
    if ($controller->configurable)
      {
      $nav[] = array(getControllerName($controller->name, $controller->class_name), $controller->class_name);
      }
    }

  $request->session->set('controllers', $nav);
  }

function getControllerName($controller_name, $class_name)
  {
  global $lang;
  $key = 'MODULE_DEFAULT_' . strtoupper($class_name);
  return (isset($lang[$key]) ? $lang[$key] : $controller_name);
  }

function loadFallbackLanguage($domain, $fallback_code = 'en')
  {
  try
    {
    Language::load($domain);
    }
  catch (Exception $ex)
    {
    Language::load($domain, $fallback_code);
    }
  }

function int2decimal($int)
  {
  $parts = explode('.', $int);
  if (count($parts) === 1)
    {
    $int = $int . '.00';
    }
  else if (strlen($parts[1]) === 1)
    {
    $int .= '0';
    }
  else if (strlen($parts[1]) !== 1)
    {
    $int = $parts[0] . '.' . substr($parts[1], 0, 2);
    }

  return $int;
  }

function padZero($int)
  {
  if ($int < 10)
    $int = '0' . $int;

  return $int;
  }

function tabpad($str, $column_len)
  {
  $str = trim($str);
  $strlen = strlen($str);
  if ($strlen > $column_len)
    return substr($str, 0, $column_len);
  else
    return $str . str_pad(' ', $column_len - $strlen);
  }
?>