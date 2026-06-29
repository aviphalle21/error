<?php
// services/EmailService.php

class EmailService {
    
    private static function logEmail($pdo, $email, $subject, $status, $response) {
        if (!is_dir(__DIR__ . '/../storage/logs')) {
            @mkdir(__DIR__ . '/../storage/logs', 0755, true);
        }

        try {
            if ($pdo) {
                $stmt = $pdo->prepare("INSERT INTO email_logs (recipient, subject, status, response) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $subject, $status, $response]);
            }
        } catch (Exception $e) {
            // Silent fallback
        }
        
        $fileLogMessage = "[" . date('Y-m-d H:i:s') . "] {$status} - {$email} - {$subject} - Response: {$response}\n";
        file_put_contents(__DIR__ . '/../storage/logs/email.log', $fileLogMessage, FILE_APPEND);
    }

    private static function sendEmailAPI($toEmail, $toName, $subject, $htmlContent) {
        // Fetch from system settings
        $provider = getSetting('email_provider', 'Brevo');
        $senderEmail = getSetting('support_email', 'noreply@example.com');
        $senderName = getSetting('library_name', 'Library System');

        if ($provider === 'Brevo') {
            $apiKey = getSetting('brevo_api_key');
            if (empty($apiKey)) return "NO_API_KEY_CONFIGURED";

            $url = 'https://api.brevo.com/v3/smtp/email';
            $data = [
                'sender' => ['name' => $senderName, 'email' => $senderEmail],
                'to' => [['email' => $toEmail, 'name' => $toName]],
                'subject' => $subject,
                'htmlContent' => $htmlContent
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'api-key: ' . $apiKey,
                'content-type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return ['success' => true, 'response' => $result];
            } else {
                return ['success' => false, 'response' => $result ?: $curlError];
            }
        } else {
            // SMTP Logic fallback
            // In a real prod environment, we would use PHPMailer here.
            // For now, we simulate success or return an error if missing deps.
            // (Assuming we might install PHPMailer via composer if needed, 
            // but for this task we will just mock standard mail if SMTP chosen 
            // or we can implement a raw socket SMTP sender. Let's return error 
            // unless they install PHPMailer)
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return ['success' => false, 'response' => 'PHPMailer not installed. Please use Brevo or install PHPMailer via Composer.'];
            }
        }
    }

    private static function renderTemplate($templateName, $variables = []) {
        $path = __DIR__ . "/../templates/email/{$templateName}.html";
        if (!file_exists($path)) {
            $html = "<h2>" . ucfirst(str_replace('_', ' ', $templateName)) . "</h2>";
            foreach ($variables as $k => $v) { $html .= "<p><strong>{$k}:</strong> {$v}</p>"; }
            return $html;
        }
        $html = file_get_contents($path);
        foreach ($variables as $key => $val) {
            $html = str_replace("{{{$key}}}", $val, $html);
        }
        return $html;
    }

    public static function sendOTP($pdo, $toEmail, $userName, $otp) {
        $subject = 'Password Reset OTP';
        $htmlContent = self::renderTemplate('otp', [
            'userName' => htmlspecialchars($userName),
            'otp' => htmlspecialchars($otp)
        ]);
        
        $result = self::sendEmailAPI($toEmail, $userName, $subject, $htmlContent);
        if (is_string($result) && $result === 'NO_API_KEY_CONFIGURED') return 'DEVELOPMENT_MODE';
        
        if ($result['success']) {
            self::logEmail($pdo, $toEmail, $subject, 'success', $result['response']);
            return true;
        } else {
            self::logEmail($pdo, $toEmail, $subject, 'failed', $result['response']);
            return false;
        }
    }

    public static function sendVerificationEmail($pdo, $toEmail, $userName, $token) {
        $subject = 'Verify Your Account Registration';
        $htmlContent = self::renderTemplate('registration', [
            'userName' => htmlspecialchars($userName),
            'verifyUrl' => htmlspecialchars($token)
        ]);
        
        $result = self::sendEmailAPI($toEmail, $userName, $subject, $htmlContent);
        if (is_string($result) && $result === 'NO_API_KEY_CONFIGURED') return 'DEVELOPMENT_MODE';
        
        if ($result['success']) {
            self::logEmail($pdo, $toEmail, $subject, 'success', $result['response']);
            return true;
        } else {
            self::logEmail($pdo, $toEmail, $subject, 'failed', $result['response']);
            return false;
        }
    }

    public static function sendBookingConfirmation($pdo, $toEmail, $userName, $bookingData) {
        $subject = 'Table Booking Confirmation';
        $htmlContent = self::renderTemplate('booking', [
            'userName' => htmlspecialchars($userName),
            'bookingId' => htmlspecialchars($bookingData['id']),
            'tableNumber' => htmlspecialchars($bookingData['table_number']),
            'date' => htmlspecialchars($bookingData['date']),
            'time' => htmlspecialchars($bookingData['start_time'] . ' - ' . $bookingData['end_time'])
        ]);
        
        $result = self::sendEmailAPI($toEmail, $userName, $subject, $htmlContent);
        if (is_string($result) && $result === 'NO_API_KEY_CONFIGURED') return 'DEVELOPMENT_MODE';
        
        if ($result['success']) {
            self::logEmail($pdo, $toEmail, $subject, 'success', $result['response']);
            return true;
        } else {
            self::logEmail($pdo, $toEmail, $subject, 'failed', $result['response']);
            return false;
        }
    }

    public static function sendPaymentReceipt($pdo, $toEmail, $userName, $paymentData) {
        $subject = 'Payment Receipt';
        $htmlContent = self::renderTemplate('payment_receipt', [
            'userName' => htmlspecialchars($userName),
            'transactionId' => htmlspecialchars($paymentData['transaction_id']),
            'amount' => htmlspecialchars($paymentData['amount']),
            'plan' => htmlspecialchars($paymentData['plan_name']),
            'date' => htmlspecialchars($paymentData['payment_date'])
        ]);
        
        $result = self::sendEmailAPI($toEmail, $userName, $subject, $htmlContent);
        if (is_string($result) && $result === 'NO_API_KEY_CONFIGURED') return 'DEVELOPMENT_MODE';
        
        if ($result['success']) {
            self::logEmail($pdo, $toEmail, $subject, 'success', $result['response']);
            return true;
        } else {
            self::logEmail($pdo, $toEmail, $subject, 'failed', $result['response']);
            return false;
        }
    }

    public static function sendAdminNotification($pdo, $subject, $message) {
        $adminEmail = getSetting('support_email');
        $htmlContent = self::renderTemplate('admin_notification', [
            'subject' => htmlspecialchars($subject),
            'message' => nl2br(htmlspecialchars($message))
        ]);
        
        $result = self::sendEmailAPI($adminEmail, 'Administrator', $subject, $htmlContent);
        if (is_string($result) && $result === 'NO_API_KEY_CONFIGURED') return 'DEVELOPMENT_MODE';
        
        if ($result['success']) {
            self::logEmail($pdo, $adminEmail, $subject, 'success', $result['response']);
            return true;
        } else {
            self::logEmail($pdo, $adminEmail, $subject, 'failed', $result['response']);
            return false;
        }
    }
}
