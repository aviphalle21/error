<?php
// config/mail.php
// SMTP Configuration for PHPMailer

return [
    'host'       => 'smtp.gmail.com', // e.g., smtp.gmail.com, smtp.sendgrid.net, smtp-relay.brevo.com
    'port'       => 587,              // 587 for TLS, 465 for SSL
    'username'   => 'your-email@gmail.com',
    'password'   => 'your-app-password',
    'encryption' => 'tls',            // 'tls' or 'ssl'
    'from_email' => 'noreply@saraswatiabhyasika.com',
    'from_name'  => 'Saraswati Abhyasika'
];
