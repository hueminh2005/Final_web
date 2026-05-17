<?php
/**
 * Mail Service
 */

class MailService {
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/mail.php';
    }
    
    /**
     * Send email
     */
    public function send($to, $subject, $body, $isHtml = true) {
        $headers = [
            'From: ' . $this->config['from']['address'],
            'Reply-To: ' . $this->config['from']['address'],
            'X-Mailer: PHP/' . phpversion()
        ];
        
        if ($isHtml) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }
        
        try {
            return mail($to, $subject, $body, implode("\r\n", $headers));
        } catch (Exception $e) {
            error_log('Mail error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send verification email
     */
    public function sendVerificationEmail($email, $displayName, $token) {
        $verificationUrl = APP_URL . '/verify-email.php?token=' . $token;
        
        $subject = 'Verify Your Email Address';
        $body = $this->getVerificationEmailTemplate($displayName, $verificationUrl);
        
        return $this->send($email, $subject, $body);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $displayName, $token, $otp) {
        $resetUrl = APP_URL . '/reset-password.php?token=' . $token;
        
        $subject = 'Reset Your Password';
        $body = $this->getPasswordResetTemplate($displayName, $resetUrl, $otp);
        
        return $this->send($email, $subject, $body);
    }
    
    /**
     * Send share notification email
     */
    public function sendShareNotificationEmail($recipientEmail, $ownerName, $noteName) {
        $subject = $ownerName . ' shared a note with you';
        $body = $this->getShareNotificationTemplate($ownerName, $noteName);
        
        return $this->send($recipientEmail, $subject, $body);
    }
    
    /**
     * Get verification email template
     */
    private function getVerificationEmailTemplate($displayName, $verificationUrl) {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Welcome to Note Management!</h2>
                <p>Hi {$displayName},</p>
                <p>Thank you for registering. Please verify your email address by clicking the link below:</p>
                <p><a href='{$verificationUrl}' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a></p>
                <p>If you didn't create this account, please ignore this email.</p>
                <p>Best regards,<br>Note Management Team</p>
            </body>
            </html>
        ";
    }
    
    /**
     * Get password reset template
     */
    private function getPasswordResetTemplate($displayName, $resetUrl, $otp) {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Password Reset Request</h2>
                <p>Hi {$displayName},</p>
                <p>You requested to reset your password. Click the link below:</p>
                <p><a href='{$resetUrl}' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                <p>Or use this OTP: <strong>{$otp}</strong></p>
                <p>This link expires in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <p>Best regards,<br>Note Management Team</p>
            </body>
            </html>
        ";
    }
    
    /**
     * Get share notification template
     */
    private function getShareNotificationTemplate($ownerName, $noteName) {
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Note Shared With You</h2>
                <p>{$ownerName} shared a note with you.</p>
                <p><strong>Note:</strong> {$noteName}</p>
                <p><a href='" . APP_URL . "/index.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Shared Notes</a></p>
                <p>Best regards,<br>Note Management Team</p>
            </body>
            </html>
        ";
    }
}
?>
