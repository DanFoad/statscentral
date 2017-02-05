<?php

/** events.php
 * All events page
 * @author Dan Foad
 */

require_once("template/header.php");

// Import data
$eventraw = file_get_contents("/var/www/html/dev/events.json");
$eventdata = json_decode($eventraw, true);
$year = "ALL YEARS";

if (isset($_GET["year"])) {
    if ($_GET["year"] <= date("Y")) {
        $year = $_GET["year"];
    }
}

function getRegionName($raw) {
    $region = "";
    switch ($raw) {
        case "OPL":
            $region = "Oceanic Pro League";
            break;
        case "NALCS":
            $region = "North American LCS";
            break;
        case "EULCS":
            $region = "European LCS";
            break;
        case "LCK":
            $region = "League Champions Korea";
            break;
        case "LMS":
            $region = "League Master Series";
            break;
        case "LJL":
            $region = "LoL Japan League";
            break;
        case "IEM":
            $region = "Intel Extreme Masters";
            break;
        case "WORLDS":
            $region = "World Championship";
            break;
        case "IWCCHILE":
            $region = "International Wildcard";
            break;
        case "IWCTURKEY":
            $region = "International Wildcard";
            break;
        case "IWCI2015":
            $region = "International Wildcard";
            break;
        case "MSI2015":
            $region = "Mid Season Invitational";
            break;
        case "NACS":
            $region = "NA Challenger Series";
            break;
        case "EUCS":
            $region = "EU Challenger Series";
            break;
        default:
            $region = $raw;
            break;
    }
    return $region;
}

$years = array();
foreach($eventdata as $i=>$region) {
    foreach ($region as $j=>$year_temp) {
        if (!in_array($j, $years)) {
            $years[] = $j;
        }
    }
}

?>

        <h1>Events</h1>

        <ul class="league-chooser row">
            <a href="/events/"><li>All</li></a>
            <?php
            foreach ($years as $year_temp) {
                echo "<a href='/events/" . $year_temp . "/'><li>" . $year_temp . "</li></a>";
            }
            ?>
        </ul>

        <div class="row champ__container">
            <?php
                foreach($eventdata as $i=>$region) {
                    foreach ($region as $j=>$year_temp) {
                        if ($year != "ALL YEARS" && $j != $year) continue;

                        foreach ($year_temp as $k=>$split) {
                            echo "<div class=\"champ\">";
                            echo "  <img class=\"event__image\" src=\"/img/logo/leagues/" . strtolower($i) . ".png\" />";
                            echo "  <p class=\"event__name\">" . getRegionName($i) . "</p>";
                            echo "  <p>" . $k . " - " . $j . "</p>";
                            echo "</div>";
                        }
                    }
                }
            ?>
        </div>

<?php require_once("template/footer.php");