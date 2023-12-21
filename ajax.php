<?php 
ini_set("display_errors", 0);
date_default_timezone_set('Europe/Madrid');
header('Content-Type: application/json; charset=utf-8');
$json = [];
if(isset($_POST['action']) && ($_POST['action'] == 'generate' || $_POST['action'] == 'send')) {
  $html = file_get_contents("templates/main.html");
  $innerhtml = "";
  $currentcolor = "ffffff";
  $currenttitle = 0;
  if(isset($_POST['form'])) {
    foreach ($_POST['form'] as $item) {
      if($item['type'] == 'separator') {
        $innerhtml .= file_get_contents("templates/separator.html");
        $innerhtml = str_replace('[color]', $currentcolor, $innerhtml);
      } else if($item['type'] == 'button') {
        $innerhtml .= file_get_contents("templates/button-".$item['value'][0].".html");
        $innerhtml = str_replace('[url]', $item['value'][1], $innerhtml);
      } else if($item['type'] == 'spaciator') {
        $innerhtml .= file_get_contents("templates/spaciator.html");
        $innerhtml = str_replace('[color]', $item['value'][0], $innerhtml);
      } else if($item['type'] == 'title') {
        $innerhtml .= file_get_contents("templates/title-".$item['value'][0].".html");
        $currentcolor = "ffffff";
        $currenttitle = $item['value'][0];
      } else if($item['type'] == 'item') {
        if($currenttitle == 8)  $temp = file_get_contents("templates/event.html");
        else $temp = file_get_contents("templates/item.html");
        $temp = str_replace('[title]', $item['value'][0], $temp);
        $temp = str_replace('[url]', $item['value'][2], $temp);
        $temp = str_replace('[description]', $item['value'][3], $temp);
        if($item['value'][1] != '') {
          $temp = str_replace('[has_subtitle]', "", $temp);
          $temp = str_replace('[/has_subtitle]', "", $temp);
          $temp = str_replace('[subtitle]', $item['value'][1], $temp);
        } else {
          $temp = str_replace('[has_subtitle]', "<!-- ", $temp);
          $temp = str_replace('[/has_subtitle]', " --><br/><br/>", $temp);
        }
        $temp = str_replace('[color]', $item['value'][4], $temp);
        $currentcolor = $item['value'][4];
        $innerhtml .= $temp;
      }
    }
  }
  $html = str_replace("[MAIN]", $innerhtml, $html);
  file_put_contents("temp.html", $html);
  if($_POST['action'] == 'send') {
    $file = date("Y-m-d_H_i_s").".html";
    file_put_contents("./html/".$file, $html);
    foreach(explode(",", $_POST['email']) as $email) {
      $email = chop($email);
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if(!mail($email, "https://pruebas.enuttisworking.com/NUEVO-BOLETIN-BEEN/html/".$file, $html, "Content-Type: text/html; charset=UTF-8\r\n")) $json = ['status' => 'danger', 'text' => 'NO se ha podido enviar la newsletter. Inténtelo más tarde.'];
      } else if(!isset($json['status'])) $json = ['status' => 'danger', 'text' => 'Email incorrecto "'.$email.'".'];
    }
    if(!isset($json['status'])) $json = ['status' => 'success', 'text' => 'Newsletter enviada correctamente.'];
  }
} else if(isset($_POST['action']) && $_POST['action'] == 'save' && isset($_POST['form'])) {
  file_put_contents("./saves/save-".date("Y-m-d_His").".json", json_encode($_POST['form']));
  if(!isset($json['status'])) $json = ['status' => 'success', 'text' => 'Newsletter guardada correctamente.'];
}
echo json_encode($json);