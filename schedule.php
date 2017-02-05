<?php

if (!isset($_GET["leagueId"])) {
    echo "[\"ERROR\"]";
    return;
}
$timezone = "0";
if (isset($_GET["timezone"])) {
    $timezone = $_GET["timezone"];
}

$scheduleurl = "http://api.lolesports.com/api/v1/scheduleItems?leagueId=" . $_GET["leagueId"];
$scheduleraw = file_get_contents($scheduleurl);
$scheduledata = json_decode($scheduleraw, true);

$scheduled = array();


foreach($scheduledata["scheduleItems"] as $i=>$match) {
    $time = convertTime($match["scheduledTime"]);
    if ($time < new DateTime() || !array_key_exists("match", $match)) {
        continue;
    }
    $scheduled[$match["match"]] = array();
    $scheduled[$match["match"]]["time"] = $time;
}

foreach($scheduledata["highlanderTournaments"] as $i=>$tournament) {
    $league = getLeague($tournament["title"]);
    foreach($tournament["brackets"] as $j=>$bracket) {
        foreach($bracket["matches"] as $k=>$match) {
            $id = $match["id"];
            if (!array_key_exists($id, $scheduled)) continue;

            if (strpos($match["name"], "-vs-") !== false) {
                $scheduled[$id]["team1"] = explode("-vs-",$match["name"])[0];
                $scheduled[$id]["team2"] = explode("-vs-",$match["name"])[1];
            } else {
                $scheduled[$id]["team1"] = "TBD";
                $scheduled[$id]["team2"] = "TBD";
            }
            $scheduled[$id]["logo1"] = "/img/logo/" . $scheduled[$id]["time"]->format("Y") . "/schedule/" . $league . "/" . strtolower($scheduled[$id]["team1"]) . ".png";
            $scheduled[$id]["logo2"] = "/img/logo/" . $scheduled[$id]["time"]->format("Y") . "/schedule/" . $league . "/" . strtolower($scheduled[$id]["team2"]) . ".png";
        }
    }
}

usort($scheduled, "sortByTime");

function getLeague($title) {
    $title = explode("_", $title)[0];
    return $title;
}

function convertTime($raw) {
    $time = DateTime::createFromFormat("Y-m-d\TH:i:s.000\+0000", $raw, new DateTimeZone('America/Los_Angeles'));
    return $time;
}

function sortByTime($a, $b) {

    $a = $a["time"];
    $b = $b["time"];

    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}

date_default_timezone_set( "UTC" );

foreach ($scheduled as $i=>$match) {
    if ($timezone < 0) {
        $scheduled[$i]["time"] = $scheduled[$i]["time"]->sub(new DateInterval("PT" . strval(-1 * $timezone) . "M0S"));
    } else {
        $scheduled[$i]["time"] = $scheduled[$i]["time"]->add(new DateInterval("PT" . strval($timezone) . "M0S"));
    }
    $scheduled[$i]["time"] = $scheduled[$i]["time"]->format('M d - ga');
}

echo json_encode($scheduled);
