<?php
/**
 * KWF Controller: AdminSettings
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-10-07
 * @version 1.0
 */

class AdminSettings extends Controller
  {
  public function before()
    {
    if (!Access::$is_logged_in || Access::$user->rank != 1)
      {
      $this->response->redirect(urlModr());
      }
    }

  public function _default()
    {
    if ($this->request->post('save_settings'))
      {
      $this->saveSettings();
      }

    $this->view = new View('admin/settings');
    }

  private function saveSettings()
    {
    $errors = array();
    $settings = $this->request->post('setting');
    $config_filename = BASE . 'config.php';
    $admin_config_filename = BASE . 'admin/admin_config.php';

    $config_content = file_get_contents($config_filename);
    $admin_config_content = file_get_contents($admin_config_filename);

    foreach ($settings as $setting => &$sett_value)
      {
      // Some settings are different for admin config and site config
      switch ($setting)
        {
        /*case 'MINIFIED':
          $admin_value = MINIFIED;
          break;*/
        default:
          $admin_value = $sett_value;
        }

      // Add apostrophes around string values and escape apostrophes inside values
      if (!is_numeric($sett_value))
        {
        $sett_value = "'" . str_replace("'", "\\'", $sett_value) . "'";
        $admin_value = "'" . str_replace("'", "\\'", $admin_value) . "'";
        }

      $config_content = preg_replace("/define\('" . $setting . "', (.*?)\);/s", "define('" . $setting . "', " . $sett_value . ");", $config_content);
      $admin_config_content = preg_replace("/define\('" . $setting . "', (.*?)\);/s", "define('" . $setting . "', " . $admin_value . ");", $admin_config_content);
      }

    file_put_contents($config_filename,  $config_content);
    file_put_contents($admin_config_filename,  $admin_config_content);

    $this->response->addInfo(__('SETTINGS_INFO_SAVED'));
    }

  public function run()
    {
    if ($this->view != null)
      {
      $this->response->setContentType('html');
      $this->response->addContent($this->view->compile($this->route, $this->params));
      }
    }
  }
?>