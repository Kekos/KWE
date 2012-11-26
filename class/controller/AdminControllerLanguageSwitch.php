<?php
/**
 * KWF Controller: AdminControllerLanguageSwitch
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-11-26
 * @version 1.0
 */

class AdminControllerLanguageSwitch extends Controller
  {
  private $db = null;
  private $model_language = null;

  public function before()
    {
    if (!Access::$is_logged_in || !Access::$is_administrator || !Access::hasControllerPermission('LanguageSwitch'))
      {
      $this->response->redirect(urlModr());
      }

    loadFallbackLanguage('LanguageSwitch');

    $this->db = DbMysqli::getInstance();
    $this->model_language = new LanguageModel($this->db);
    $this->response->title = __('LANGSWITCH_HEADER');
    }

  public function _default()
    {
    if ($this->request->post('save_language'))
      {
      $this->saveDefaultLanguage();

      if ($this->request->ajax_request)
        {
        return;
        }
      }

    // Read the config file to find the selected default language
    $config = file_get_contents(BASE . 'config.php');
    preg_match("/LANGUAGE_DEFAULT', '([a-zA-Z]{2})'/", $config, $selected);

    $data['languages'] = $this->model_language->fetchAll();
    $data['selected'] = (isset($selected[1]) ? $selected[1] : '');
    $this->view = new View('admin/edit-language-switch', $data);
    }

  private function saveDefaultLanguage()
    {
    $language = $this->request->post('language');
    $language = $this->model_language->fetch($language);

    // Read the config file to find the selected default language and change it
    $config = file_get_contents(BASE . 'config.php');
    $config = preg_replace("/LANGUAGE_DEFAULT', '[a-zA-Z]{2}'/", "LANGUAGE_DEFAULT', '" . $language->code . "'", $config);
    file_put_contents(BASE . 'config.php', $config);

    $this->response->addInfo(__('LANGSWITCH_INFO_SAVED'));
    }

  static function initLanguageSwitch($event)
    {
    Language::configure($event['request'], LANGUAGE_SESSION, LANGUAGE_DEFAULT);
    Language::acceptHeader();
    loadFallbackLanguage('LanguageSwitch');
    }

  static function loadNavigation($event)
    {
    $page_model = $event['page_model'];
    $page = $event['page'];

    $event['navigation'] = $page_model->fetchPageList(1, 0, VisitorLanguage::getLanguage()->id);
    if ($page->page->parent == 0)
      $subid = $page->page->id;
    else
      $subid = $page->page->parent;

    $event['subnavigation'] = $page_model->fetchSubPageList($subid);
    }

  static function onGetPage($event)
    {
    $event['sql_where'] .= " AND `language` = ? OR `language` = 0";
    $event['sql_types'] .= 'i';
    $event['sql_args'][] .= VisitorLanguage::getLanguage()->id;
    }

  static function install()
    {
    HookManager::add('BootStrap', 'beforerouter', 'AdminControllerLanguageSwitch::initLanguageSwitch');
    HookManager::add('BootStrap', 'beforenavigationload', 'AdminControllerLanguageSwitch::loadNavigation');
    HookManager::save();

    // Inject language settings to config file
    $config = file_get_contents(BASE . 'config.php');
    if (strpos($config, "LANGUAGE_DEFAULT") === false)
      {
      $config = substr($config, 0, -2);
      $config .= "\ndefine('LANGUAGE_SESSION', 1);  // True if the language selection should be auto-saved in session
define('LANGUAGE_DEFAULT', 'en');  // The language code of default language to use
?>";
      file_put_contents(BASE . 'config.php', $config);
      }
    }

  static function uninstall()
    {
    HookManager::remove('BootStrap', 'beforerouter', 'AdminControllerLanguageSwitch::initLanguageSwitch');
    HookManager::save();

    $files = array('../class/controller/AdminControllerLanguageSwitch.php',
      '../class/controller/LanguageSwitch.php',
      '../language/en/LanguageSwitch.lang.php',
      '../language/sv/LanguageSwitch.lang.php');

    foreach ($files as $file)
      {
      if (file_exists($file))
        {
        unlink($file);
        }
      }

    return false;
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