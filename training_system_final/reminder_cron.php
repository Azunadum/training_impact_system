<?php
// Load Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// === 1️⃣ DB CONFIG ===
$host = 'localhost';
$db   = 'tia_system';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass);

// === 2️⃣ SMTP CONFIG ===
$smtpHost = 'smtp.gmail.com';
$smtpUser = 'azunadum20@gmail.com';       //Gmail
$smtpPass = 'yvgyjtlkntmacfjp';         // Gmail app password

// === 3️⃣ HOW TO TEST ===
// Option A: Test mode with minutes
$testMode = true;      // ✅ set to false to use months in production
$minutesAfter = 1;     // send if end_date + 1 minute <= now

// Option B: Real mode with months
$monthsAfter = 6;

// === 4️⃣ Build query based on mode ===
if ($testMode) {
    echo "Running in TEST mode: end_date + {$minutesAfter} minute(s) <= NOW\n";
    $query = "
        SELECT *
        FROM training_entries
        WHERE DATE_ADD(end_date, INTERVAL :minutes MINUTE) <= NOW()
          AND DATE_ADD(end_date, INTERVAL :minutes MINUTE) > NOW() - INTERVAL 1 MINUTE
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['minutes' => $minutesAfter]);
} else {
    echo "Running in REAL mode: end_date + {$monthsAfter} month(s) = CURDATE()\n";
    $query = "
        SELECT *
        FROM training_entries
        WHERE DATE_ADD(end_date, INTERVAL :months MONTH) = CURDATE()
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['months' => $monthsAfter]);
}

$dueEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$dueEntries) {
    echo "✅ No due reminders.\n";
    exit;
}

// === 5️⃣ Get HR users ===
$hrStmt = $pdo->query("SELECT * FROM hr_users");
$hrUsers = $hrStmt->fetchAll(PDO::FETCH_ASSOC);

// === 6️⃣ Send mail ===
foreach ($dueEntries as $entry) {
    $traineeEmail = $entry['staff_email'];
    $traineeName  = $entry['staff_name'];
    $trainingTitle = $entry['title'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($smtpUser, 'Training Impact System');
        $mail->addAddress($traineeEmail, $traineeName);

        foreach ($hrUsers as $hr) {
            $mail->addCC($hr['email'], $hr['name']);
        }

        $mail->isHTML(true);
        $mail->Subject = "Reminder: Complete Impact Assessment for \"$trainingTitle\"";
        $mail->Body = "
            Hi {$traineeName},<br><br>
            This is a reminder to complete your Training Impact Assessment for:<br>
            <strong>{$trainingTitle}</strong><br><br>
            Thank you!<br>
            Training Impact System
        ";

        $mail->send();
        echo "✅ Reminder sent to {$traineeEmail} and CC to HR.\n";
    } catch (Exception $e) {
        echo "❌ Could not send to {$traineeEmail}. Error: {$mail->ErrorInfo}\n";
    }
}

echo "✅ Finished sending reminders.\n";