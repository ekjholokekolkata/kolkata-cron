<?php
// =========================================================================
// GLOBAL EXECUTION STABILITY (BROWSER + CRON + GITHUB UNIFIED)
// =========================================================================
header("Connection: close");
ignore_user_abort(true);
set_time_limit(0);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =========================================================================
// 1. GLOBAL ONESIGNAL CONFIGURATIONS
// =========================================================================
define('ONESIGNAL_APP_ID', '2c1874f6-8215-43a0-9e0d-c234c8756c09');
define('ONESIGNAL_REST_KEY', 'os_v2_app_fqmhj5uccvb2bhqnyi2mq5lmbgmlus2ivpye4wf3tr3oqvyvjpneqskhi4eqcsd72xwuemt6isi76yv2jdsgdfglulvkn7u2vgragli');

// =========================================================================
// FIX 1: SAFETY CHECK
// =========================================================================
if (!file_exists('db_config.php')) {
    die("ERROR: db_config.php missing");
}

// =========================================================================
// 2. MODULE ONE: EXPLORE PHOTOS
// =========================================================================
include('db_config.php');

if (!isset($host, $user, $pass, $dbname)) {
    die("ERROR: DB credentials missing in db_config.php");
}

$host_explore = $host;
$user_explore = $user;
$pass_explore = $pass;
$dbname_explore = $dbname;

$connPhotos = new mysqli($host_explore, $user_explore, $pass_explore, $dbname_explore);

if ($connPhotos->connect_error) {
    die("DB CONNECTION FAILED (Photos): " . $connPhotos->connect_error);
}

$photoQuery = "SELECT id, title, watermark_text, image_url FROM kolkata_explore WHERE notification_sent = 0 LIMIT 5";
$photoResult = $connPhotos->query($photoQuery);

if ($photoResult) {
    while ($photoRow = $photoResult->fetch_assoc()) {
        $rowId = $photoRow['id'];
        $titleText = $photoRow['title'];
        $watermarkText = $photoRow['watermark_text'];
        $imgbbUrl = $photoRow['image_url'];

        $payload = [
            'app_id' => ONESIGNAL_APP_ID,
            'included_segments' => ['Total Subscriptions'],
            'headings' => ["en" => "New Picture Uploaded!"],
            'contents' => ["en" => "$titleText with $watermarkText posted a new picture"]
        ];

        if (!empty($imgbbUrl)) {
            $payload['big_picture'] = $imgbbUrl;
            $payload['ios_attachments'] = ["id1" => $imgbbUrl];
        }

        if (sendOneSignalNotification($payload)) {
            $connPhotos->query("UPDATE kolkata_explore SET notification_sent = 1 WHERE id = $rowId");
        }
    }
}

$connPhotos->close();

// =========================================================================
// 3. MODULE TWO: STORY REVIEWS
// =========================================================================
$connStoryReviews = new mysqli($host_explore, $user_explore, $pass_explore, $dbname_explore);

if (!$connStoryReviews->connect_error) {
    $storyQuery = "SELECT id, user_name FROM story_reviews WHERE notification_sent = 0 LIMIT 5";
    $storyResult = $connStoryReviews->query($storyQuery);

    if ($storyResult) {
        while ($storyRow = $storyResult->fetch_assoc()) {
            $rowId = $storyRow['id'];
            $authorName = $storyRow['user_name'];

            $payload = [
                'app_id' => ONESIGNAL_APP_ID,
                'included_segments' => ['All Subscriptions'],
                'headings' => ["en" => "New Comment!"],
                'contents' => ["en" => "$authorName , commented on a picture check out"]
            ];

            if (sendOneSignalNotification($payload)) {
                $connStoryReviews->query("UPDATE story_reviews SET notification_sent = 1 WHERE id = $rowId");
            }
        }
    }

    $connStoryReviews->close();
}

// =========================================================================
// 4. MODULE THREE: TESTIMONIAL REVIEWS
// =========================================================================
if (file_exists('dbconfig.php')) {
    include('dbconfig.php');

    $connReviews = new mysqli($host, $user, $pass, $dbname);

    if (!$connReviews->connect_error) {
        $reviewQuery = "SELECT id, name FROM reviews WHERE notification_sent = 0 LIMIT 5";
        $reviewResult = $connReviews->query($reviewQuery);

        if ($reviewResult) {
            while ($reviewRow = $reviewResult->fetch_assoc()) {
                $rowId = $reviewRow['id'];
                $authorName = $reviewRow['name'];

                $payload = [
                    'app_id' => ONESIGNAL_APP_ID,
                    'included_segments' => ['Total Subscriptions'],
                    'headings' => ["en" => "New Testimonial Review"],
                    'contents' => ["en" => "$authorName , check out what review gave"]
                ];

                if (sendOneSignalNotification($payload)) {
                    $connReviews->query("UPDATE reviews SET notification_sent = 1 WHERE id = $rowId");
                }
            }
        }

        $connReviews->close();
    }
}

// =========================================================================
// 5. MODULE FOUR: INSTAGRAM LINKS
// =========================================================================
if (file_exists('configgallery.php')) {
    include('configgallery.php');

    $dbname_gallery = "if0_41248478_gallery";

    $connGallery = new mysqli($host, $user, $pass, $dbname_gallery);

    if (!$connGallery->connect_error) {
        $instaQuery = "SELECT id FROM instagram_links WHERE notification_sent = 0 LIMIT 5";
        $instaResult = $connGallery->query($instaQuery);

        if ($instaResult) {
            while ($instaRow = $instaResult->fetch_assoc()) {
                $rowId = $instaRow['id'];

                $payload = [
                    'app_id' => ONESIGNAL_APP_ID,
                    'included_segments' => ['Total Subscriptions'],
                    'headings' => ["en" => "Gallery Update!"],
                    'contents' => ["en" => "Something new in gallery check out"]
                ];

                if (sendOneSignalNotification($payload)) {
                    $connGallery->query("UPDATE instagram_links SET notification_sent = 1 WHERE id = $rowId");
                }
            }
        }

        $connGallery->close();
    }
}

// =========================================================================
// 6. ONESIGNAL CURL ROUTER
// =========================================================================
function sendOneSignalNotification($payload) {
    $ch = curl_init("https://onesignal.com/api/v1/notifications");

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . ONESIGNAL_REST_KEY
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // NEW: visibility for debugging consistency
    error_log("OneSignal HTTP CODE: " . $httpCode);

    if ($response === false) {
        error_log("CURL ERROR: " . curl_error($ch));
    }

    curl_close($ch);

    return ($httpCode === 200 || $httpCode === 201);
}

// =========================================================================
// FINAL OUTPUT (ALWAYS CONSISTENT RESPONSE)
// =========================================================================
flush();
echo "Status: Active - Check Complete.";
?>