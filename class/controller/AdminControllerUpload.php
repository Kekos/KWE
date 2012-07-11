<?php
/**
 * KWF Controller: AdminControllerUpload
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 1.0
 */

class AdminControllerUpload extends Controller
  {
  private $upload_dir;
  private $path = '';
  private $real_path = '';

  public function before($action = false)
    {
    if ((!Access::$is_logged_in || !Access::$is_administrator) || ($action != 'js_browse' && !Access::hasControllerPermission('upload')))
      {
      $this->response->redirect(urlModr());
      }

    $this->response->title = 'Filuppladdning';
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
      $this->response->addInfo('Hittade inte filen eller mappen med sökvägen ' . htmlspecialchars($this->path));
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
        $html = '<p><a href="' . FULLPATH_SITE . '/upload/' . $this->path . '">Ladda ner filen ' . $this->path . ' här</a></p>';
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
      $errors[] = 'Bläddra efter en fil och tryck på "Ladda upp" för att ladda upp!';

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

      $this->response->addInfo('Filen laddades upp.');
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
      $errors[] = 'Du angav inte ett namn för den nya mappen.';
    if (file_exists($folder_path))
      $errors[] = 'Det fanns redan en mapp med namnet ' . htmlspecialchars($folder_name);

    if (!count($errors))
      {
      if (mkdir($folder_path))
        {
        $this->response->addInfo('Mappen skapades.');
        return true;
        }
      else
        {
        $this->response->addError('Det gick inte att skapa mappen.');
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
    $this->response->addInfo('Filen ' . htmlspecialchars($this->path) . ' togs bort.');

    unlink($this->real_path);

    $this->path = $this->lvlUpFolder($this->path);
    $this->real_path = $this->lvlUpFolder($this->real_path);
    }

  private function deleteFolder()
    {
    if (@rmdir($this->real_path))
      {
      $this->response->addInfo('Mappen ' . htmlspecialchars($this->path) . ' togs bort.');
      }
    else
      {
      $this->response->addError('Det gick inte att ta bort mappen. Kontrollera att den är tom först!');
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