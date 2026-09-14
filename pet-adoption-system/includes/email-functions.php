<?php
/**
 * Email Functions with Gmail SMTP
 * Pet Adoption Management System
 */

// Correct PHPMailer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ========== EMAIL CONFIGURATION ==========
define('EMAIL_USERNAME', 'zamanttha_sahidulla@dlsu.edu.ph'); //use your email
define('EMAIL_PASSWORD', 'wydp ojzp gtwq bmlc'); // 16 app password (thru gmail)
define('EMAIL_FROM_NAME', 'Pet Adoption Center'); // app name
define('EMAIL_FROM_ADDRESS', 'zamanttha_sahidulla@dlsu.edu.ph'); //use the same email
define('EMAIL_REPLY_TO', 'zamanttha_sahidulla@dlsu.edu.ph'); //use the same email
define('DEBUG_MODE', true);

/**
 * Get email template by type
 */
function getEmailTemplate($type) {
    $templates = [
        'interview_request' => [
            'subject' => 'Interview Request - {pet_name} Adoption',
            'body' => "Dear {adopter_name},

Thank you for your application to adopt {pet_name}!

We have reviewed your application (Reference #{application_id}) and would like to schedule an interview with you.

Please reply to this email with your availability for the following week, or call us at (123) 456-7890 to schedule.

Interview Details:
- Pet: {pet_name}
- Application ID: #{application_id}
- Your contact: {adopter_email}

We look forward to speaking with you!

Best regards,
Pet Adoption Center Team"
        ],
        
        'acceptance' => [
            'subject' => 'Congratulations! Your Application Has Been Approved - {pet_name}',
            'body' => "Dear {adopter_name},

Wonderful news! Your application to adopt {pet_name} has been APPROVED! 🎉

Application Reference: #{application_id}

Next Steps:
1. Please contact us within 3 business days to schedule your adoption appointment
2. Bring a valid ID and proof of address
3. Adoption fee: $150 (includes vaccinations, spay/neuter, microchip)
4. We'll provide all medical records and adoption paperwork

Contact Information:
📞 Phone: (123) 456-7890
📧 Email: info@petadoptioncenter.com
🕐 Hours: Monday-Friday, 9:00 AM - 5:00 PM

Congratulations on your new family member!

Best regards,
Pet Adoption Center Team"
        ],
        
        'rejection' => [
            'subject' => 'Update on Your Adoption Application - {pet_name}',
            'body' => "Dear {adopter_name},

Thank you for your interest in adopting {pet_name} and for taking the time to complete our adoption application (Reference #{application_id}).

After careful consideration, we have decided to move forward with another applicant whose situation we felt was the best match for {pet_name}'s specific needs.

We encourage you to continue browsing our available pets at our website, as we have many wonderful animals looking for loving homes. Each pet has unique needs, and we're confident you'll find the perfect match!

If you have any questions, please don't hesitate to contact us.

Thank you for considering adoption!

Best regards,
Pet Adoption Center Team"
        ],
        
        'confirmation' => [
            'subject' => 'Application Received - {pet_name}',
            'body' => "Dear {adopter_name},

Thank you for your interest in adopting {pet_name}!

We have received your application (Reference #{application_id}) and will review it shortly.

What's Next?
- Review: 3-5 business days
- Interview: If selected, we'll contact you at {adopter_email}
- Decision: Within 2-3 days after interview

You can contact us anytime:
📧 Email: info@petadoptioncenter.com
📞 Phone: (123) 456-7890

Thank you for choosing to adopt!

Best regards,
Pet Adoption Center Team"
        ],
        
        'custom' => [
            'subject' => '',
            'body' => ''
        ]
    ];
    
    return $templates[$type] ?? $templates['custom'];
}

/**
 * Replace merge fields in email content
 */
function replaceMergeFields($content, $data) {
    $replacements = [
        '{pet_name}' => $data['pet_name'] ?? '',
        '{adopter_name}' => $data['adopter_name'] ?? '',
        '{adopter_email}' => $data['adopter_email'] ?? '',
        '{application_id}' => $data['application_id'] ?? '',
        '{interview_date}' => $data['interview_date'] ?? '',
        '{animal_type}' => $data['animal_type'] ?? '',
        '{breed}' => $data['breed'] ?? '',
    ];
    
    return str_replace(array_keys($replacements), array_values($replacements), $content);
}

/**
 * Send email using Gmail SMTP
 */
function sendGmailSMTP($to, $subject, $body) {
    // Validate email address
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email address: $to");
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        if (DEBUG_MODE) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                error_log("SMTP Debug level $level: $str");
            };
        }
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USERNAME;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Timeout settings
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = true;
        
        // SSL options
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);
        
        // Content
        $mail->isHTML(false);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $result = $mail->send();
        
        if (DEBUG_MODE) {
            error_log("Email sent successfully to: $to");
        }
        
        return $result;
        
    } catch (Exception $e) {
        $error_msg = "Email Error: " . $mail->ErrorInfo;
        error_log($error_msg);
        error_log("Exception: " . $e->getMessage());
        
        if (DEBUG_MODE) {
            error_log("To: $to");
            error_log("Subject: $subject");
        }
        
        return false;
    }
}

/**
 * Send application email
 */
function sendApplicationEmail($pdo, $application_id, $email_type, $custom_subject = null, $custom_body = null, $interview_date = null) {
    try {
        // Get application details
        $stmt = $pdo->prepare("
            SELECT aa.*, 
                   CONCAT(a.first_name, ' ', a.last_name) as adopter_name,
                   a.email as adopter_email,
                   a.first_name,
                   a.last_name,
                   p.pet_name,
                   p.animal_type,
                   p.breed
            FROM adoption_applications aa
            JOIN adopters a ON aa.adopter_id = a.adopter_id
            JOIN pets p ON aa.animal_id = p.animal_id
            WHERE aa.application_id = ?
        ");
        $stmt->execute([$application_id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$app) {
            error_log("Application not found: $application_id");
            return ['success' => false, 'message' => 'Application not found'];
        }
        
        // Validate adopter email
        if (empty($app['adopter_email']) || !filter_var($app['adopter_email'], FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid adopter email for application: $application_id");
            return ['success' => false, 'message' => 'Invalid adopter email address'];
        }
        
        // Get template or use custom content
        if ($email_type === 'custom') {
            $subject = $custom_subject;
            $body = $custom_body;
        } else {
            $template = getEmailTemplate($email_type);
            $subject = $template['subject'];
            $body = $template['body'];
        }
        
        // Prepare merge data
        $merge_data = [
            'pet_name' => $app['pet_name'],
            'adopter_name' => trim($app['first_name'] . ' ' . $app['last_name']),
            'adopter_email' => $app['adopter_email'],
            'application_id' => $application_id,
            'interview_date' => $interview_date ?? '',
            'animal_type' => $app['animal_type'],
            'breed' => $app['breed']
        ];
        
        // Replace merge fields
        $subject = replaceMergeFields($subject, $merge_data);
        $body = replaceMergeFields($body, $merge_data);
        
        $to = $app['adopter_email'];
        
        error_log("Attempting to send email to: $to");
        error_log("Email type: $email_type");
        error_log("Subject: $subject");
        
        // Send email via Gmail SMTP
        $email_sent = sendGmailSMTP($to, $subject, $body);
        
        // Log email to database
        $log_stmt = $pdo->prepare("
            INSERT INTO email_logs (application_id, email_type, sent_to, subject, message, status, sent_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $status = $email_sent ? 'Sent' : 'Failed';
        $log_stmt->execute([
            $application_id,
            ucfirst($email_type),
            $to,
            $subject,
            $body,
            $status
        ]);
        
        if ($email_sent) {
            return [
                'success' => true,
                'message' => "Email sent successfully to $to!"
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send email. Please check error logs.'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Email error in sendApplicationEmail: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [
            'success' => false,
            'message' => 'An error occurred while sending the email: ' . $e->getMessage()
        ];
    }
}

/**
 * Get email log for application
 */
function getEmailLog($pdo, $application_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM email_logs 
        WHERE application_id = ? 
        ORDER BY sent_at DESC
    ");
    $stmt->execute([$application_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Format email type for display
 */
function formatEmailType($type) {
    return match($type) {
        'interview_request', 'Interview Request' => 'Interview Request',
        'acceptance', 'Acceptance' => 'Acceptance Letter',
        'rejection', 'Rejection' => 'Rejection Notice',
        'confirmation', 'Confirmation' => 'Confirmation Email',
        'custom', 'Custom' => 'Custom Email',
        default => ucfirst($type)
    };
}