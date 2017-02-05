<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '/var/www/vendor/autoload.php';
use Mailgun\Mailgun;

$client = new \Http\Adapter\Guzzle6\Client();

$mailgun = new \Mailgun\Mailgun("key-f213a7f2073ed6e2bc4d7d3c4a102a5d", $client);
$domain = "mg.statscentral.gg";

$f_name = cleanupentries($_POST["name"]);
$f_email = cleanupentries($_POST["email"]);
$f_message = cleanupentries($_POST["message"]);
$from_ip = $_SERVER["REMOTE_ADDR"];
$from_browser = $_SERVER["HTTP_USER_AGENT"];

// Captcha
$response = $_POST["g-recaptcha-response"];
$url = "https://www.google.com/recaptcha/api/siteverify";
$vars = "secret=6Ld47R8TAAAAAAcsrXS07yYYQivK12AmiJE6EVZ-&response=" . $response;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$verify = json_decode(curl_exec($ch), true);

function cleanupentries($entry) {
    $entry = trim($entry);
    $entry = stripslashes($entry);
    $entry = htmlspecialchars($entry);

    return $entry;
}

$subject = "Contact Form filled out on " . date("d-m-Y H:i:s");

$message = "This email was submitted on " . date("d-m-Y") .
           "\n\nName: " . $f_name .
           "\n\nEmail: " . $f_email .
           "\n\nMessage: " . $f_message .
           "\n\nTechnical Details: " . $from_ip . "\n" . $from_browser;

if (!$f_email || !$f_name || !$f_message) {
    echo "Error: Invalid details in contact form";
    exit;
} else if (!$verify["success"]) {
    echo "Error: Invalid captcha provided";
    exit;
} else if (filter_var($f_email, FILTER_VALIDATE_EMAIL)) {
        $sent = $mailgun->sendMessage($domain, array(
            "from"      => "Contact Form <mailgun@statscentral.gg>",
            "to"        => "Team <team@statscentral.gg>",
            "subject"   => $subject,
            "text"      => $message
        ));
        echo $sent->http_response_body->message;
        exit;
} else {
    echo "Error: Invalid email address specified";
}
?>
