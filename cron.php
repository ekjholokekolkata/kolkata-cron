<?php
// =========================================================================
// SECURITY GATE
// =========================================================================
$SECRET_TOKEN = "CHANGE_THIS_TO_STRONG_RANDOM_STRING";

// allow browser test + cron
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['token']) || $_GET['token'] !== $SECRET_TOKEN) {
        http_response_code(403);
        die("Unauthorized access");
    }
}

// =========================================================================
// STABILITY SETTINGS
// =========================================================================
header("Content-Type: text/plain");
ignore_user_abort(true);
set_time_limit(0);

ini_set('display_errors', 0);
error_reporting(0);

// =========================================================================
// ONESIGNAL CONFIG
// =========================================================================
define('ONESIGNAL_APP_ID', '2c1874f6-8215-43a0-9e0d-c234c8756c09');
define('ONESIGNAL_REST_KEY', 'YOUR_ONESIGNAL_REST_KEY_HERE');

// =========================================================================
// DB CONNECTION
// =========================================================================
if (!file_exists('db_config.php')) {
    die("DB config missing");
}

include('db_config.php');

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("DB connection failed");
}

// =========================================================================
// FUNCTION: SEND NOTIFICATION
// =========================================================================
function sendNotification($payload) {

    $ch = curl_init("https://onesignal.com/api/v1/notifications");

    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json; charset=utf-8",
            "Authorization: Basic " . ONESIGNAL_REST_KEY
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return ($httpCode == 200 || $httpCode == 201);
}

// =========================================================================
// SAFE QUERY RUNNER (prevents crashes)
// =========================================================================
function runQuery($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) {
        error_log("SQL ERROR: " . $conn->error);
        return false;
    }
    return $result;
}

// =========================================================================
// MODULE 1: kolkata_explore
// =========================================================================
$q1 = runQuery($conn, "SELECT id, title, watermark_text, image_url FROM kolkata_explore WHERE notification_sent = 0 LIMIT 5");

if ($q1) {
    while ($row = $q1->fetch_assoc()) {

        $payload = [
            "app_id" => ONESIGNAL_APP_ID,
            "included_segments" => ["Total Subscriptions"],
            "headings" => ["en" => "New Picture Uploaded!"],
            "contents" => ["en" => $row['title'] . " posted a new picture"]
        ];

        if (!empty($row['image_url'])) {
            $payload["big_picture"] = $row['image_url'];
        }

        if (sendNotification($payload)) {
            $id = (int)$row['id'];
            $conn->query("UPDATE kolkata_explore SET notification_sent = 1 WHERE id = $id");
        }
    }
}

// =========================================================================
// MODULE 2: story_reviews
// =========================================================================
$q2 = runQuery($conn, "SELECT id, user_name FROM story_reviews WHERE notification_sent = 0 LIMIT 5");

if ($q2) {
    while ($row = $q2->fetch_assoc()) {

        $payload = [
            "app_id" => ONESIGNAL_APP_ID,
            "included_segments" => ["All Subscriptions"],
            "headings" => ["en" => "New Comment!"],
            "contents" => ["en" => $row['user_name'] . " commented on a picture"]
        ];

        if (sendNotification($payload)) {
            $id = (int)$row['id'];
            $conn->query("UPDATE story_reviews SET notification_sent = 1 WHERE id = $id");
        }
    }
}

// =========================================================================
// MODULE 3: reviews
// =========================================================================
$q3 = runQuery($conn, "SELECT id, name FROM reviews WHERE notification_sent = 0 LIMIT 5");

if ($q3) {
    while ($row = $q3->fetch_assoc()) {

        $payload = [
            "app_id" => ONESIGNAL_APP_ID,
            "included_segments" => ["Total Subscriptions"],
            "headings" => ["en" => "New Review"],
            "contents" => ["en" => $row['name'] . " left a review"]
        ];

        if (sendNotification($payload)) {
            $id = (int)$row['id'];
            $conn->query("UPDATE reviews SET notification_sent = 1 WHERE id = $id");
        }
    }
}

// =========================================================================
// MODULE 4: instagram_links
// =========================================================================
$q4 = runQuery($conn, "SELECT id FROM instagram_links WHERE notification_sent = 0 LIMIT 5");

if ($q4) {
    while ($row = $q4->fetch_assoc()) {

        $payload = [
            "app_id" => ONESIGNAL_APP_ID,
            "included_segments" => ["Total Subscriptions"],
            "headings" => ["en" => "Gallery Update"],
            "contents" => ["en" => "New update in gallery"]
        ];

        if (sendNotification($payload)) {
            $id = (int)$row['id'];
            $conn->query("UPDATE instagram_links SET notification_sent = 1 WHERE id = $id");
        }
    }
}

// =========================================================================
// CLEAN EXIT
// =========================================================================
$conn->close();

echo "OK - Cron executed successfully";
?>
