<?php
$content = array();
$content['num_news'] = $this->request->post('num_news_' . $this->controller['id']);
$content['order'] = $this->request->post('order_' . $this->controller['id']);
$content['html_before'] = $this->request->post('html_before_' . $this->controller['id']);
$content['html_after'] = $this->request->post('html_after_' . $this->controller['id']);
$content['html_format'] = $this->request->post('html_format_' . $this->controller['id']);

if (!is_numeric($content['num_news']))
  $errors[] = 'Du måste ange hur många nyheter som ska visas, ange ett tal.';
if ($content['order'] != 'ASC' && $content['order'] != 'DESC')
  $errors[] = 'Du måste ange hur nyheterna ska sorteras.';
if (strlen($content['html_format']) < 0)
  $errors[] = 'Om du anger en tom HTML-formatsträng visas ingen nyhetstext.';

if (!count($errors))
  {
  $content = json_encode($content);
  return false;
  }
else
  {
  return true;
  }
?>