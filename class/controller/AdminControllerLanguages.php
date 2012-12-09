<?php
/**
 * KWF Controller: AdminControllerLanguages
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-12-09
 * @version 1.0
 */

/* Recursely removes all files and directories in a directory to remove */
function rrmdir($dir)
  {
  if (is_dir($dir))
    {
    $objects = scandir($dir);
    foreach ($objects as $object)
      {
      if ($object != '.' && $object != '..')
        {
        if (is_dir($dir . '/' . $object))
          {
          rrmdir($dir."/".$object);
          }
        else
          {
          unlink($dir."/".$object);
          }
        }
      }

    reset($objects);
    return rmdir($dir);
    }
  }

class AdminControllerLanguages extends Controller
  {
  private $db = null;
  private $model_language = null;
  private $language = false;

  public function before($action = false, $language_id = false)
    {
    if (!Access::$is_logged_in || !Access::$is_administrator || !Access::hasControllerPermission('Languages'))
      {
      $this->response->redirect(urlModr());
      }

    loadFallbackLanguage('Languages');

    $this->db = DbMysqli::getInstance();
    $this->model_language = new LanguageModel($this->db);
    $this->response->title = __('MODULE_DEFAULT_LANGUAGES');

    if ($action && $language_id)
      {
      $language_id = intval($language_id);
      if (!$this->language = $this->model_language->fetch($language_id))
        return $this->response->addInfo(__('LANGUAGES_INFO_NOT_FOUND') . $language_id);
      }
    }

  public function _default()
    {
    if ($this->request->post('install_language'))
      {
      $this->installLanguage();
      }

    $this->listLanguage();
    }

  public function delete()
    {
    if (!$this->language)
      return;

    if ($this->request->post('delete_language_yes'))
      {
      $this->deleteLanguage();
      $this->listLanguage();
      }
    else if ($this->request->post('delete_language_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new View('admin/delete-language', array('language' => $this->language));
      }
    }

  public function js_browse()
    {
    $json = array();
    $json['languages'] = $this->model_language->fetchAll();

    $this->response->setContentType('json');
    $this->response->addContent(json_encode($json));
    }

  private function listLanguage()
    {
    $data['languages'] = $this->model_language->fetchAll();
    $this->view = new View('admin/list-languages', $data);
    }

  private function installLanguage()
    {
    $errors = array();
    $file = $this->request->file('file');
    $filename = $file['name'];

    if (empty($filename))
      $errors[] = __('LANGUAGES_ERROR_NO_FILE');
    else if (substr($filename, -5)  != '.kwel')
      $errors[] = __('LANGUAGES_ERROR_WRONG_TYPE');

    if (!count($errors))
      {
      move_uploaded_file($file['tmp_name'], $filename);

      $zip = new ZipArchive();
      if ($zip->open($filename, ZIPARCHIVE::CREATE) !== true)
        return $this->response->addError(__('MODULES_ERROR_INVALID_FILE') . $var);

      /* First extract the Language info file */
      if ($zip->extractTo('../', 'info.xml'))
        {
        /* Read the Language info file (as XML) */
        $fileinfo = '../info.xml';
        libxml_use_internal_errors(false);
        $fileinfo_obj = simplexml_load_file($fileinfo);
        if (is_object($fileinfo_obj))
          {
          $errors = array();

          $this->language = new Klanguage($this->model_language);

          if (!$this->language->setName((string) $fileinfo_obj->name))
            $errors[] = __('LANGUAGES_ERROR_INF_NAME_SHORT');
          if (!$this->language->setCode((string) $fileinfo_obj->code))
            $errors[] = __('LANGUAGES_ERROR_INF_CODE_SHORT');

          if (!count($errors))
            {
            $this->language->save();
            if ($this->language->id)
              {
              /* Then extract the rest of controller files */
              $zip->extractTo(BASE . 'language/');
              $this->response->addInfo(__('LANGUAGES_INFO_INSTALLED'));
              }
            else
              {
              $this->response->addError(__('LANGUAGES_ERROR_REGISTER_INSTALL'));
              }
            }
          else
            {
            $this->response->addError($errors);
            }
          }
        else
          {
          $this->response->addError(__('LANGUAGES_ERROR_CORRUPT_INFO'));
          }

        unlink($fileinfo);
        unlink(BASE . 'language/info.xml');
        }
      else
        {
        $this->response->addError(__('LANGUAGES_ERROR_NO_INFO'));
        }

      $zip->close();
      unlink($filename);
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function deleteLanguage()
    {
    if (rrmdir(BASE . 'language/' . $this->language->code))
      {
      $this->response->addInfo(__('LANGUAGES_INFO_UNINSTALLED'));
      $this->language->delete();
      $this->language = false;
      }
    else
      {
      $this->response->addError(__('LANGUAGES_ERROR_UNINSTALL'));
      }
    }

  static function uninstall()
    {
    /*$files = array('../class/controller/AdminControllerLanguage.php');
    foreach ($files as $file)
      {
      if (file_exists($file))
        {
        unlink($file);
        }
      }*/
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