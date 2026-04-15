<?php

namespace App\Utilities;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * @param string $sender
     * @param string $subject
     * @param string $body
     * @param Array $recipients 
     * [ 
     *      @param string $email
     *      @param string $name 
     * ]
     * @return boolean
   */
    public function sendEmail($params) 
    {
        $rsp = array('success'=>false, 'data'=>null, 'message'=>'Lo sentimos, el servicio no está disponible. Intentelo más tartde.');
        try {
            $subject    = trim($params["subject"]);
            $body       = $params["body"];
            $recipients = $params["recipients"];
            $mailer_name = $_ENV['mailer_name'];
            $mailer_password = $_ENV['mailer_password'];
            $mailer_username = $_ENV['mailer_username'];
            $mailer_host = $_ENV['mailer_host'];

            if (!(count($recipients) > 0)) {
                throw new \Exception('No se econtraron destinatarios.');
            }
            
            $errors = array(); $valids = array();
            foreach ($recipients as $k => $o) {
                if (!filter_var(@$o['email'], FILTER_VALIDATE_EMAIL)) {
                    array_push($errors, $o);
                } else {
                    array_push($valids, $o);
                }
            }

            if (count($errors) == count($recipients)) {
                throw new \Exception('Se econtraron destinatarios invalidos.');
            }

            $sender     = isset($params["sender"]) ? $params["sender"] : $mailer_name; // getenv('MAILER_NAME')
            $recipients = $valids;
            $mail       = new PHPMailer(true);

            //Server settings
            $mail->SMTPDebug  = 0;                                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $mailer_host;                  // getenv('MAILER_HOST');                  //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $mailer_username;              // getenv('MAILER_USERNAME');              //SMTP username
            $mail->Password   = $mailer_password;              // getenv('MAILER_PASSWORD');              //SMTP password
            $mail->SMTPSecure = "tls";                                  //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom($mailer_username, $sender); // getenv('MAILER_USERNAME')
            
            //Add a recipient
            foreach ($recipients as $k => $o) {
                $mail->addAddress($o['email'], (isset($o['name']) ? $o['name'] : $o['email']));
            }

            //Content
            $mail->isHTML(true);  //Set email format to HTML  
            $mail->CharSet = 'utf-8';                           
            $mail->Subject = $subject;
            $mail->Body    = $body;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            
            $mail->send();

            $rsp['success'] = true;
            $rsp['data'] = compact('mail');
            $rsp['message'] = 'Se envio correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}