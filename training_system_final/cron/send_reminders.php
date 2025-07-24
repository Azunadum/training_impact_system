<?php
file_put_contents(__DIR__ . '/cron_log.txt', date('Y-m-d H:i:s') . " - Script triggered\n", FILE_APPEND);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Load PHPMailer
require __DIR__ . '/../vendor/autoload.php'; // ✅ Correct and reliable


// ✅ Connect to your DB
$pdo = new PDO('mysql:host=localhost;dbname=training_system_db', 'root', '');

// ✅ Fetch all entries older than 1 minute and not yet sent
$sql = "SELECT * FROM training_entries 
        WHERE reminder_sent = 0 
        AND created_at <= NOW() - INTERVAL 1 MINUTE";
$stmt = $pdo->query($sql);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Loop and send emails
foreach ($entries as $entry) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Setup
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'system.testinggg@gmail.com';         // 🔁 your Gmail
        $mail->Password = 'mnrxgqtueoqxjlvk';           // 🔁 App Password (not Gmail password)
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email setup
        
        $mail->setFrom('system.testinggg@gmail.com', 'Dost System');
        $mail->addAddress($entry['staff_email'], $entry['staff_name']);
        $mail->Subject = "Training Reminder";
        $mail->Body    = "Dear " . $entry['staff_name'] . ",\n\nWe hope this message finds you well. \n\n This is a courteous reminder that you are required to complete the Training Impact Assessment related to your recent participation in the training titled: " . $entry['title'] . "\n\nYour unique code is: " . $entry['unique_code'] . "\n\nPlease use it to complete your training form.\n\nThank you!";

        // Send the email
        $mail->send();

        // ✅ Update DB so we don’t send again
        $update = $pdo->prepare("UPDATE training_entries SET reminder_sent = 1 WHERE id = ?");
        $update->execute([$entry['id']]);

        echo "Reminder sent to: " . $entry['staff_email'] . "\n";
    } catch (Exception $e) {
        echo "Error sending to " . $entry['staff_email'] . ": {$mail->ErrorInfo}\n";
    }
}
?>