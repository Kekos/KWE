<?php
$content = array();
$content['num_events'] = $this->request->post('num_events_' . $this->controller['id']);
$content['html_before'] = $this->request->post('html_before_' . $this->controller['id']);
$content['html_after'] = $this->request->post('html_after_' . $this->controller['id']);
$content['html_format'] = $this->request->post('html_format_' . $this->controller['id']);

if (!is_numeric($content['num_events']))
  $errors[] = 'Du måste ange en siffra i fältet "Visa antal händelser".';
if (strlen($content['html_format']) < 0)
  $errors[] = 'Om du anger en tom HTML-formatsträng visas ingen händelsetext.';

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