<?php
/**
 * KWF Controller: AdminControllerUpload
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-08-02
 * @version 1.0
 */

class AdminControllerUpload extends Controller
  {
  private $upload_dir;
  private $path = '';
  private $real_path = '';

  public function before($action = false)
    {
    if ((!Access::$is_logged_in || !Access::$is_administrator) || ($action != 'js_browse' && !Access::hasControllerPermission('Upload')))
      {
      $this->response->redirect(urlModr());
      }

    loadFallbackLangugage('Upload');

    $this->response->title = __('MODULE_DEFAULT_UPLOAD');
    $this->upload_dir = BASE . 'upload/';

    if (count($this->request->params) > 2)
      {
      $this->path = implode('/', array_splice($this->request->params, 2));
      $this->path = str_replace('..', '', $this->path);
      }

    $this->real_path = $this->upload_dir . $this->path;

    if ($action && !file_exists($this->real_path))
      {
      $this->path = false;
      $this->response->addInfo(__('UPLOAD_INFO_PATH_NOT_FOUND') . htmlspecialchars($this->path));
      }
    }

  public function js_browse()
    {
    $json = array();
    $json['cd'] = $this->path;
    $json['up_path'] = $this->lvlUpFolder($this->path);
    $json['files'] = scandir($this->real_path);

    foreach ($json['files'] as $key => &$file)
      {
      if ($file == '.' || $file == '..')
        array_splice($json['files'], $key, 1);
      else
        $file = array('folder' => is_dir($this->real_path . '/' . $file), 'url' => $file, 'name' => $file);
      }

    $this->response->setContentType('application/json');
    $this->response->addContent(json_encode($json));
    }

  public function _default()
    {
    if (is_dir($this->real_path))
      {
      $data['path'] = $this->path;
      $data['real_path'] = $this->real_path;
      $data['up_path'] = $this->lvlUpFolder($this->path);
      $data['files'] = scandir($this->real_path);
      $data['upload_file'] = $this->request->post('upload_file');
      $this->view = new View('admin/list-uploaded-files', $data);
      }
    else
      {
      $file_info = getimagesize($this->real_path);
      $html = '';

      if (strpos($file_info['mime'], 'image/') === 0)
        {
        $html = '<img src="' . FULLPATH_SITE . '/upload/' . $this->path . '" alt="" />';
        }
      else
        {
        $html = '<p><a href="' . FULLPATH_SITE . '/upload/' . $this->path . '">' . __('UPLOAD_DOWNLOAD_FILE', $this->path) . '</a></p>';
        }

      $this->response->addContent($html);
      }
    }

  public function addfolder()
    {
    if ($this->request->post('new_folder'))
      {
      if ($this->newFolder())
        {
        return $this->_default();
        }
      }

    $this->view = new View('admin/new-folder', array('path' => $this->path));
    }

  public function upload()
    {
    if ($this->request->file('file'))
      {
      if ($this->uploadFile())
        {
        return $this->_default();
        }
      }

    $this->view = new View('admin/upload-file', array('path' => $this->path));
    }

  public function delete()
    {
    if (!$this->path)
      return;

    if (is_dir($this->real_path))
      {
      if ($this->request->post('delete_folder_yes'))
        {
        $this->deleteFolder();
        $this->_default();
        }
      else if ($this->request->post('delete_folder_no'))
        {
        $this->response->redirect(urlModr($this->route, 'browse', $this->path));
        }
      else
        {
        $this->view = new View('admin/delete-folder', array('path' => $this->path));
        }
      }
    else
      {
      if ($this->request->post('delete_file_yes'))
        {
        $this->deleteFile();
        $this->_default();
        }
      else if ($this->request->post('delete_file_no'))
        {
        $this->response->redirect(urlModr($this->route, 'browse', $this->lvlUpFolder($this->path)));
        }
      else
        {
        $this->view = new View('admin/delete-file', array('path' => $this->path));
        }
      }
    }

  private function uploadFile()
    {
    $errors = array();
    $file = $this->request->file('file');
    $filename = $file['name'];

    if (empty($filename))
      $errors[] = __('UPLOAD_ERROR_NO_SELECTED');

    if (!count($errors))
      {
      if (isset($file['ajax']))
        {
        move_ajax_uploaded_file($file['stream'], $this->real_path . '/' . $filename);
        }
      else
        {
        move_uploaded_file($file['tmp_name'], $this->real_path . '/' . $filename);
        }

      $this->response->addInfo(__('UPLOAD_INFO_UPLOADED'));
      return true;
      }
    else
      {
      $this->response->addError($errors);
      return false;
      }
    }

  private function newFolder()
    {
    $errors = array();
    $folder_name = $this->request->post('folder_name');
    $folder_path = $this->real_path . '/' . $folder_name;

    if (empty($folder_name))
      $errors[] = __('UPLOAD_ERROR_FOLDER_NAME');
    if (file_exists($folder_path))
      $errors[] = __('UPLOAD_ERROR_FOLDER_EXISTS', htmlspecialchars($folder_name));

    if (!count($errors))
      {
      if (mkdir($folder_path))
        {
        $this->response->addInfo(__('UPLOAD_INFO_FOLDER_CREATED'));
        return true;
        }
      else
        {
        $this->response->addError(__('UPLOAD_ERROR_FOLDER_CREATE'));
        }
      }
    else
      {
      $this->response->addError($errors);
      }

    return false;
    }

  private function deleteFile()
    {
    $this->response->addInfo(__('UPLOAD_INFO_FILE_REMOVED', htmlspecialchars($this->path)));

    unlink($this->real_path);

    $this->path = $this->lvlUpFolder($this->path);
    $this->real_path = $this->lvlUpFolder($this->real_path);
    }

  private function deleteFolder()
    {
    if (@rmdir($this->real_path))
      {
      $this->response->addInfo(__('UPLOAD_INFO_FOLDER_REMOVED', htmlspecialchars($this->path)));
      }
    else
      {
      $this->response->addError(__('UPLOAD_ERROR_FOLDER_REMOVE'));
      }

    $this->path = $this->lvlUpFolder($this->path);
    $this->real_path = $this->lvlUpFolder($this->real_path);
    }

  private function lvlUpFolder($path)
    {
    return substr($path, 0, strrpos($path, '/'));
    }

  static function uninstall()
    {
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