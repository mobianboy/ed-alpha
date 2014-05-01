<?php

interface mailsystem {
  public function sendFrom($address);
  public function sendMail($address, $subject, $content, $options);
}


class SendMailSystem implements mailsystem {
  private $fromAddress;

  public function sendFrom($address) {
    $this->fromAddress = $address;
  }

  public function sendMail($address, $subject, $content, $options) {
    $options['From'] = $this->fromAddress;
    $options['Reply-To'] = $this->fromAddress;

    $headers = "";
    if(is_array($options)) {
      foreach($options as $key => $header) {
        $headers .= $key.": ".$header."\r\n";
      }
      $res = mail($address, $subject, $content, $headers);
    } else {
      $res = false;
    }
    return $res;
  }

}

class MailNotification {
  private $messages = array();
  private $templates = array();
  private $mailer;

  public function setMailer($mailer) {
    $this->mailer = $mailer;
  }

  public function addMessage($to, $subject, $message, $template, $options = null) {
    $this->messages[] = array("address" => $to, "subject" => $subject, "message" => $message, "template" => $template, "options" => $options);
  }

  private function getTemplate($template) {
    if(isset($this->templates[$template])) {
      return $this->templates[$template];
    } else {
      if(file_exists($_SERVER['DOCUMENT_ROOT']."/wp-content/themes/score/tpl/notification/emails/".$template.".tpl")) {
        $tempData = file_get_contents($_SERVER['DOCUMENT_ROOT']."/wp-content/themes/score/tpl/notification/emails/".$template.".tpl");
        if($tempData) {
          $this->templates[$template] = $tempData;
        }
        return $tempData;
      } else {
        return false;
      }
    }
  }

  public function processMessages() {
    $errors = array();
    if(count($this->messages)) {
      foreach($this->messages as $val) {
        $template = $this->getTemplate($val['template']);
        if($template === false) {
          $errors[] = "Cannot load template: ".$template;
          continue;
        }
        $content = $template;
        if(count($val['message'])) {
          foreach($val['message'] as $key2 => $val2) {
            $content = str_replace("[=".$key2."=]", $val2, $content);
          }
        }
        if(is_array($val['options'])) {
          $headers = $val['options'];
        } else {
          $headers = array();
        }
        if(!isset($headers['X-Mailer'])) {
          $headers['X-Mailer'] = "PHP/".phpversion();
        }
        $this->mailer->sendFrom('no-reply@eardish.com');
        if(!$this->mailer->sendMail($val['address'], $val['subject'], $content, $headers)) {
          $errors[] = "Cannot send email message: ".$val['subject']." -> ".$val['address'];
        }
      }
    }
    return $errors;
  }

}

