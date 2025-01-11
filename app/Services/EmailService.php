<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
    protected $mail;

    public function __construct () {

        $this->mail = new PHPMailer(true);
        $this->mail->setLanguage('br');
        $this->mail->CharSet = 'UTF-8';  
        $this->configureMail();

    }

    
    private function configureMail (): void {

        $this->mail->isSMTP();                                   
        $this->mail->Host = env('MAIL_HOST');  
        $this->mail->SMTPAuth = true;                               
        $this->mail->Username = env('MAIL_USERNAME');                
        $this->mail->Password = env('MAIL_PASSWORD');                
        $this->mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');     
        $this->mail->Port = env('MAIL_PORT', 587);      
            
    }


    public function sendEmail ($to, $subject, $body, $from): void {     
        
        $this->mail->setFrom($from ?? env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $this->mail->addAddress($to);
        $this->mail->isHTML(true); 
        $this->mail->Subject = $subject;
        $this->mail->Body = $body;
        $this->mail->AltBody = strip_tags($body); 

        $this->mail->send();
   
    }

}
