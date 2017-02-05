<?php

/** index.php
 * Normal-use index page, non-splash
 * @author Dan Foad
 */

require_once("template/header.php");

// Import Data
$playerdata = json_decode(file_get_contents("players.json"), true);
$championinfo = json_decode(file_get_contents("championinfo.json"), true);
$regionslist = array_keys($playerdata);
sort($regionslist);

$champnames = array();
foreach ($championinfo as $tempchamp) {
    $champnames[strval($tempchamp["id"])] = $tempchamp["key"];
}

$leagueids = array("1" => ["ALLSTAR", "ALL STAR"], "2" => ["NALCS", "NA LCS"], "3" => ["EULCS", "EU LCS"], "4" => ["NACS", "NA CS"], "5" => ["EUCS", "EU CS"], "6" => ["LCK", "LCK"], "7" => ["LPL", "LPL"], "8" => ["LMS", "LMS"], "9" => ["WORLDS", "WORLDS"], "10" => ["MSI", "MSI"], "12" => ["IWC", "INTERNATION WILDCARD"], "13" => ["OPL", "OPL"], "14" => ["CBLOL", "CBLOL BRAZIL"], "15" => ["FIRE", "TEAM FIRE"], "16" => ["ICE", "TEAM ICE"], "17" => ["CLS", "COPA LATINOAMERICA"], "18" => ["CLN", "COPA LATINOAMERICA NORTH"]);

// Get two random players with the same role in the same region
$role = rand(0,4);
$region = $regionslist[array_rand($regionslist)];
$blueplayer = getPlayer($playerdata, $role, $region, date("Y"));
$redplayer;
do {
    $redplayer = getPlayer($playerdata, $role, $region, date("Y"));
} while ($blueplayer["name"] == $redplayer["name"]);

$blueplayer["bg"] = "/img/champSplash/" . $champnames[strval($blueplayer["champ"])] . "_Splash_Centered_0.jpg";
$redplayer["bg"] = "/img/champSplash/" . $champnames[strval($redplayer["champ"])] . "_Splash_Centered_0.jpg";

/** getPlayer
 * Get data for a random player in a given role+region+year, compiled from data resource
 * @param $playerdata   Compiled data to parse through
 * @param $role         The role of the player to get data for
 * @param $region       The region the player plays in
 * @param $year         The year to get data from
 $ @return The data of the player compiled from source
 */
function getPlayer($playerdata, $role, $region, $year) {
    $events = $playerdata[$region][$year];
    $playersInRole = array();
    $player = array();
    $datanames = ["kills", "assists", "deaths", "played", "tddtc", "fbp", "ttccd", "wardsPlaced", "visionWardsPlaced", "csAt10", "cs", "gold", "goldAt10", "minplayed"];
    
    // Compile all data found for given player in given region and year
    foreach ($events as $split=>$splitdata) {
        foreach ($splitdata as $playername=>$playerinfo) {
            if (isRole($role, $playerinfo)) {
                if (!array_key_exists($playername, $playersInRole)) {
                    $playersInRole[$playername] = $playerinfo;
                    $playersInRole[$playername]["split"] = $split;
                } else {
                    $playersInRole[$playername]["team"][2] += $playerinfo["team"][2];
                    foreach ($datanames as $attribute) {
                        $playersInRole[$playername][$attribute] += $playerinfo[$attribute];
                    }
                }
            }
        }
    }
    
    // Calculate data from final totals
    foreach ($playersInRole as $player=>$playerinfo) {
        $playersInRole[$player]["fbp"] = $playerinfo["fbp"] / $playerinfo["played"];
        $playersInRole[$player]["tddtc"] = $playerinfo["tddtc"] / $playerinfo["played"];
        $playersInRole[$player]["ttccd"] = $playerinfo["ttccd"] / $playerinfo["played"];
        $playersInRole[$player]["csAt10"] = $playerinfo["csAt10"] / $playerinfo["played"];
        $playersInRole[$player]["cspm"] = $playerinfo["cs"] / $playerinfo["minplayed"];
        $playersInRole[$player]["goldAt10"] = $playerinfo["goldAt10"] / $playerinfo["played"];
        $playersInRole[$player]["goldpm"] = $playerinfo["gold"] / $playerinfo["minplayed"];
        $playersInRole[$player]["wardsPlaced"] = $playerinfo["wardsPlaced"] / $playerinfo["played"];
        $playersInRole[$player]["visionWardsPlaced"] = $playerinfo["visionWardsPlaced"] / $playerinfo["played"];
    }

    // Get random player
    $rand = array_rand($playersInRole);
    $player = $playersInRole[$rand];

    $player["name"] = $rand;
    
    // Get player's team logo
    $player["logo"] = "img/logo/" . $year . "/" . strtolower($region) . "/" . strtolower($player["split"]) . "/" . strtolower($player["team"][1]) . ".png";

    return $player;
}

/** isRole
 * Test to see whether a player matches the required role
 * @param $role     The number of the role to compare to
 * @param $player   The player to check the role of
 * @return true or false depending on whether the user is the role
 */
function isRole($role, $player) {
    $r = $player["role"];
    switch ($role) {
        case 0:
            if ($r == "TOP")
                return true;
                break;
        case 1:
            if ($r == "JUNGLE")
                return true;
                break;
        case 2:
            if ($r == "MID")
                return true;
                break;
        case 3:
            if ($r == "ADC")
                return true;
                break;
        case 4:
            if ($r == "SUPPORT")
                return true;
                break;
    }
}

/** getKDA
 * Calculates the Kill/Death/Assist ratio of a given player
 * @param $player   The player to calculate the KDA for
 * @return The KDA of the player to 1 decimal place
 */
function getKDA($player) {
    $kda = ($player["kills"] + $player["assists"]) / max(1, $player["deaths"]);
    return number_format($kda, 1);
}

/** getKillParticipation
 * Calculates the Kill Participation of a player
 * @param $player   The player to calculate the Kill Participation of
 * @param $team     The team that the player is on
 * @return The Kill Participation of the player
 */
function getKillParticipation($player, $teamkills) {
    $participation = ($player["kills"] + $player["assists"]) / max(1, $teamkills);
    return floor($participation*100);
}

/** getRoleStats
 * Gather output strings from compiled data on player
 * @param $player   The data for the player to work with
 * @return Output strings in human-readable formats
 */
function getRoleStats($player) {
    $ret = "";
    switch($player["role"]) {
        case "SUPPORT":
            $player["ttccd"] = floor($player["ttccd"] / $player["played"]);
            $ret = "<dd>Avg. Wards Placed: " . number_format($player["wardsPlaced"] / $player["played"], 1) . "</dd>\r\n";
            $ret .= "<dd>Crowd Control Time: " . number_format($player["ttccd"] / $player["played"], 1) . "s</dd>";
            break;
        case "ADC":
        case "MID":
        case "TOP":
            $player["tddtc"] = floor($player["tddtc"] / $player["played"]);
            $ret = "<dd>Dmg To Champs: " . number_format($player["tddtc"] / $player["played"], 1) . "hp/m</dd>";
            break;
        case "JUNGLE":
            $ret = "<dd>1st Blood Partic.: " . number_format($player["fbp"], 1) . "%</dd>";
            break;
    }
    return $ret;
}

?>

    <ul id="schedulechooser" class="dropdown-content">
        <?php
            foreach ($regionslist as $region) {
                foreach ($leagueids as $id=>$leagueinfo) {
                    if ($region == $leagueinfo[0]) {
                        echo "<li class=\"leagueSelector--dropdown\" data-leagueid=\"" . $id . "\" data-leaguename=\"" . $leagueinfo[1] . "\">" . $leagueinfo[1] . "</li>";
                        break;
                    }
                }
            }
       ?>
    </ul>
   
    <div class="row schedule__container">
        <h2 class="schedule__title">Schedule</h2>
        <ul class="schedule__chooser">
            <?php
            $firstregion = "";
            $flag = 0;
            foreach ($regionslist as $region) {
                foreach ($leagueids as $id=>$leagueinfo) {
                    if ($region == $leagueinfo[0]) {
                        if ($flag == 0) {
                            echo "<li class=\"leagueSelector leagueSelector--current\" data-leagueid=\"" . $id . "\" data-leaguename=\"" . $leagueinfo[1] . "\">" . $leagueinfo[1] . "</li>";
                            $flag = 1;
                            $firstregion = $leagueinfo[1];
                        } else {
                            echo "<li class=\"leagueSelector\" data-leagueid=\"" . $id . "\" data-leaguename=\"" . $leagueinfo[1] . "\">" . $leagueinfo[1] . "</li>";
                        }
                        break;
                    }
                }
            }

            ?>
        </ul>
        <ul class="league-chooser">
            <li><a class="dropdown-button mobileschedule" href="#" data-activates="schedulechooser"><?php echo $firstregion; ?><i class="material-icons right">arrow_drop_down</i></a></li>
        </ul>
        <div class="schedule z-depth-1">
        </div>
    </div>


    <div class="row">
        <div class="overview__box">
            <div class="overview overview__red" style="background:linear-gradient(rgba(228, 34, 35, 0.3), rgba(228, 34, 35, 0.5)),url('<?php echo $redplayer["bg"]; ?>') no-repeat;">
                <dl class="overview__stats">
                    <dd>KDA: <?php echo getKDA($redplayer); ?></dd>
                    <dd>Kill Participation: <?php echo getKillParticipation($redplayer, $redplayer["team"][2]); ?>%</dd>
                    <?php echo getRoleStats($redplayer); ?>
                </dl>
                <h2 class="overview__title"><img src="<?php echo $redplayer["logo"]; ?>" alt="" /><?php echo $redplayer["name"]; ?><span class="overview__title--role"><?php echo $redplayer["role"]; ?></span></h2>
            </div>
            <div class="overview__container">
                <div class="overview overview__blue" style="background:linear-gradient(rgba(141, 206, 226, 0.3), rgba(141, 206, 226, 0.5)),url('<?php echo $blueplayer["bg"]; ?>') no-repeat;">
                    <h2 class="overview__title"><img src="<?php echo $blueplayer["logo"]; ?>" alt="" /><?php echo $blueplayer["name"]; ?><span class="overview__title--role"><?php echo $blueplayer["role"]; ?></span></h2>
                    <dl class="overview__stats">
                        <dd>KDA: <?php echo getKDA($blueplayer); ?></dd>
                        <dd>Kill Participation: <?php echo getKillParticipation($blueplayer, $blueplayer["team"][2]); ?>%</dd>
                        <?php echo getRoleStats($blueplayer); ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row main-logos">
        <?php
        foreach ($regionslist as $regionname) {
            echo "<div class=\"medium-4 columns\">";
            echo "    <img src=\"img/logo/leagues/" . strtolower($regionname) . ".png\" alt=\"\" />";
            echo "</div>";
        }
         ?>
    </div>

<script type="text/javascript">
    var id = "2";
    var currentId = "0";
    var offset = -1 * new Date().getTimezoneOffset();

    updateResults($(".leagueSelector--current").data("leagueid"));

    $(".leagueSelector").on("click", function() {
        if (currentId == $(this).data("leagueid")) {
            return;
        }
        $(".leagueSelector--current").removeClass("leagueSelector--current");
        $(this).addClass("leagueSelector--current");
        updateResults($(this).data("leagueid"));
    });
    
    function updateDropdown() {
        $(".leagueSelector--dropwdown").each(function(i, entry) {
            var region = $("mobileschedule").html();
            region = region.substring(0, region.indexOf("<i class=\"material-icons right\">arrow_drop_down</i>"));
            if ($(entry).text() == region) {
                $(entry).hide();
            }
        });
    }
    
    $(".leagueSelector--dropdown").on("click", function() {
        if (currentId == $(this).data("leagueid")) {
            return;
        }
        $(".mobileschedule").html($(this).text() + "<i class=\"material-icons right\">arrow_drop_down</i>");
        updateDropdown();
        updateResults($(this).data("leagueid"));
    });

    function updateResults(id) {

        if (id === undefined) {
            return;
        }

        if (currentId == id) {
            return;
        }
        currentId = id;

        $(".schedule").html("<div class=\"schedule__loader\">Loading</div>");

        $.ajax({
            url: "/schedule.php",
            type: "get",
            data: {leagueId: id, timezone: offset},
            dataType: "json",
            success: function(res) {
                var matchesShown = 5;
                if (res[0] == "ERROR") {
                    $(".schedule").html("<div class=\"schedule__message\">Failed to load schedule, please try again</div>");
                    return;
                }
                if (res.length === 0) {
                    $(".schedule").html("<div class=\"schedule__message\">No upcoming matches</div>");
                    return;
                }
                $(".schedule").html("");
                if (res.length < matchesShown) matchesShown = res.length;
                for (var i = 0; i < matchesShown; i++) {
                    $(".schedule").append("<div class=\"schedule__item\">\
                            <h3 class=\"schedule__time\">" + res[i]["time"] + "</h3>\
                            <div class=\"schedule__teams\">\
                                <p class=\"schedule__team\"><img src=\"" + res[i]["logo1"] + "\" alt=\"\" />" + res[i]["team1"] + "</p>\
                                <p class=\"schedule__team\"><img src=\"" + res[i]["logo2"] + "\" alt=\"\" />" + res[i]["team2"] + "</p>\
                            </div>\
                        </div>");
                }
            },
            error: function(ob, err) {
                console.log(err);
            }
        });
    }

</script>

<?php require_once("template/footer.php");
