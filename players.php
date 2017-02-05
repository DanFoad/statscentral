<?php

require_once("template/header.php");

// Import Data
$playerdata = json_decode(file_get_contents("players.json"), true);
$eventdata = json_decode(file_get_contents("events.json"), true);
$regionslist = array_keys($playerdata);

$region = "GLOBAL";
$year = "ALL";
$split = "ALL";

if (isset($_GET["region"])) {
    $region = strtoupper($_GET["region"]);
}

if (isset($_GET["year"])) {
    $year = $_GET["year"];
}

if (isset($_GET["split"]) && isset($_GET["region"])) {
    $split = strtoupper($_GET["split"]);
    $split = str_replace("_", " ", $split);
}

$cs = array(0, 0, 0, 0, 0);
$gold = array(0, 0, 0, 0, 0);
$numberofgames = 0;
foreach ($eventdata as $regionname=>$regiondata) {
    if ($region != "GLOBAL" && strtoupper($regionname) != strtoupper($region)) {
        continue;
    }
    foreach($regiondata as $yearname=>$yeardata) {
        if ($year != "ALL" && $yearname != $year) {
            continue;
        }
        foreach ($yeardata as $splitname=>$splitdata) {
            if ($split != "ALL" && strtoupper($splitname) != $split) {
                continue;
            }
            $numberofgames++;
            $cs[0] += $splitdata["csAt10"][0];
            $cs[1] += $splitdata["csAt10"][1];
            $cs[2] += $splitdata["csAt10"][2];
            $cs[3] += $splitdata["csAt10"][3];
            $cs[4] += $splitdata["csAt10"][4];

            $gold[0] += $splitdata["goldAt10"][0];
            $gold[1] += $splitdata["goldAt10"][1];
            $gold[2] += $splitdata["goldAt10"][2];
            $gold[3] += $splitdata["goldAt10"][3];
            $gold[4] += $splitdata["goldAt10"][4];
        }
    }
}

$cs[0] = $cs[0] / $numberofgames;
$cs[1] = $cs[1] / $numberofgames;
$cs[2] = $cs[2] / $numberofgames;
$cs[3] = $cs[3] / $numberofgames;
$cs[4] = $cs[4] / $numberofgames;

$gold[0] = $gold[0] / $numberofgames;
$gold[1] = $gold[1] / $numberofgames;
$gold[2] = $gold[2] / $numberofgames;
$gold[3] = $gold[3] / $numberofgames;
$gold[4] = $gold[4] / $numberofgames;

$players = getPlayers($playerdata, $region, $year, $split, $cs, $gold);
uksort($players, "strnatcasecmp");


function getPlayers($playerdata, $region, $year, $split, $cs, $gold) {
    $players = array();

    foreach ($playerdata as $regionname=>$regiondata) {
        if ($region != "GLOBAL" && strtoupper($regionname )!= $region) {
            continue;
        }
        foreach($regiondata as $yearname=>$yeardata) {
            if ($year != "ALL" && $yearname != $year) {
                continue;
            }
            foreach ($yeardata as $splitname=>$splitdata) {
                if ($split != "ALL" && strtoupper($splitname) != $split) {
                    continue;
                }
                foreach ($splitdata as $playername=>$playerinfo) {
                    if (!array_key_exists($playername, $players)) {
                        $players[$playername] = $playerinfo;
                        $players[$playername]["split"] = $split;
                    } else {
                        $players[$playername]["team"][2] += $playerinfo["team"][2];
                        $players[$playername]["kills"] += $playerinfo["kills"];
                        $players[$playername]["assists"] += $playerinfo["assists"];
                        $players[$playername]["deaths"] += $playerinfo["deaths"];
                        $players[$playername]["played"] += $playerinfo["played"];
                        $players[$playername]["tddtc"] += $playerinfo["tddtc"];
                        $players[$playername]["fbp"] += $playerinfo["fbp"];
                        $players[$playername]["ttccd"] += $playerinfo["ttccd"];
                        $players[$playername]["wardsPlaced"] += $playerinfo["wardsPlaced"];
                        $players[$playername]["visionWardsPlaced"] += $playerinfo["visionWardsPlaced"];
                        $players[$playername]["csAt10"] += $playersinfo["csAt10"];
                        $players[$playername]["cs"] += $playerinfo["cs"];
                        $players[$playername]["gold"] += $playerinfo["gold"];
                        $players[$playername]["goldAt10"] += $playerinfo["goldAt10"];
                        $players[$playername]["minplayed"] += $playerinfo["minplayed"];
                    }
                }
            }
        }
    }

    foreach ($players as $player=>$playerinfo) {
        $players[$player]["fbp"] = $playerinfo["fbp"] / $playerinfo["played"];
        $players[$player]["tddtc"] = $playerinfo["tddtc"] / $playerinfo["minplayed"];
        $players[$player]["ttccd"] = $playerinfo["ttccd"] / $playerinfo["played"];
        $players[$player]["csAt10"] = getCSDifferential($cs, $region, $year, $split, $playerinfo);
        $players[$player]["cspm"] = $playerinfo["cs"] / $playerinfo["minplayed"];
        $players[$player]["goldAt10"] = getGoldDifferential($gold, $region, $year, $split, $playerinfo);
        $players[$player]["goldpm"] = $playerinfo["gold"] / $playerinfo["minplayed"];
        $players[$player]["wardsPlaced"] = $playerinfo["wardsPlaced"] / $playerinfo["played"];
        $players[$player]["visionWardsPlaced"] = $playerinfo["visionWardsPlaced"] / $playerinfo["played"];
    }

    return $players;
}

function getCSDifferential($avgCS, $region, $year, $split, $player) {
    $csdif = 0;
    switch($player["role"]) {
        case "TOP":
            $csdif = ($player["csAt10"] / $player["played"]) - $avgCS[0];
            break;
        case "JUNGLE":
            $csdif = ($player["csAt10"] / $player["played"]) - $avgCS[1];
            break;
        case "MID":
            $csdif = ($player["csAt10"] / $player["played"]) - $avgCS[2];
            break;
        case "ADC":
            $csdif = ($player["csAt10"] / $player["played"]) - $avgCS[3];
            break;
        case "SUPPORT":
            $csdif = ($player["csAt10"] / $player["played"]) - $avgCS[4];
            break;
    }
    return $csdif;
}

function getGoldDifferential($avgGold, $region, $year, $split, $player) {
    $golddif = 0;
    switch($player["role"]) {
        case "TOP":
            $golddif = ($player["goldAt10"] / $player["played"]) - $avgGold[0];
            break;
        case "JUNGLE":
            $golddif = ($player["goldAt10"] / $player["played"]) - $avgGold[1];
            break;
        case "MID":
            $golddif = ($player["goldAt10"] / $player["played"]) - $avgGold[2];
            break;
        case "ADC":
            $golddif = ($player["goldAt10"] / $player["played"]) - $avgGold[3];
            break;
        case "SUPPORT":
            $golddif = ($player["goldAt10"] / $player["played"]) - $avgGold[4];
            break;
    }
    return $golddif;
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

?>

<ul id="regionchooser" class="dropdown-content">
    <li><a href="/players/<?php if ($year != "ALL") echo $year . "/"; ?>">Global</a></li>

    <?php

    $yeartext = "";
    if ($year != "ALL") $yeartext = $year . "/";
    foreach ($eventdata as $regionname=>$regiondata) {
        echo "<li><a href=\"/players/" . $regionname . "/" . $yeartext . "\">" . $regionname . "</a></li>";
    }

    ?>
</ul>

<ul id="yearchooser" class="dropdown-content">
    <li><a href="/players/<?php echo $region . "/"; ?>">All Years</a></li>
    <li><a href="/players/<?php echo $region . "/" . "2015/"; ?>">2015</a></li>
    <li><a href="/players/<?php echo $region . "/" . "2016/"; ?>">2016</a></li>
</ul>

<?php
if ($region != "GLOBAL" && $year != "ALL") {
?>
<ul id="splitchooser" class="dropdown-content">
    <li><a href="/players/<?php echo $region . "/" . $year . "/"; ?>">All Splits</a></li>
    <?php
        foreach ($eventdata[$region][$year] as $splitname=>$splitdata) {
            echo "<li><a href=\"/players/" . $region . "/" . $year . "/" . str_replace(" ", "_", $splitname) . "/\">" . $splitname . "</a></li>";
        }
    ?>
</ul>
<?php } ?>

<h1>Player Stats</h1>

<ul class="league-chooser row">
    <li><a class="dropdown-button" href="#" data-activates="regionchooser"><?php echo $region; ?><i class="material-icons right">arrow_drop_down</i></a></li>
    <li><a class="dropdown-button" href="#" data-activates="yearchooser"><?php
        if ($year == "ALL") {
            echo "All Years";
        } else {
            echo $year;
        }?><i class="material-icons right">arrow_drop_down</i></a></li>
    <?php
    if ($region != "GLOBAL" && $year != "ALL") {
    ?>
    <li><a class="dropdown-button" href="#" data-activates="splitchooser"><?php
        if ($split == "ALL") {
            echo "All Splits";
        } else {
            echo $split;
        } ?><i class="material-icons right">arrow_drop_down</i></a></li>
    <?php
    }
    ?>
</ul>

<div class="row">
    <div id="search" class="z-depth-1"></div>
    <table class="tablesaw tablesaw-sortable tablesaw-columntoggle highlight" data-tablesaw-mode="columntoggle" data-tablesaw-sortable data-tablesaw-sortable-switch>
        <thead>
            <th data-search-n="1" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="persist"><center>Player</center></th>
            <th data-search-n="2" data-tablesaw-sortable-col data-tablesaw-priority="persist"><center>Team</center></th>
            <th data-search-n="3" data-tablesaw-sortable-col data-tablesaw-priority="persist"><center>Position</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="1" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Total KDA"><center>KDA</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="2" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Total Kills"><center>K</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="2" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Total Deaths"><center>D</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="2" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Total Assists"><center>A</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="1" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Kill Participation"><center>Kill Part.</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="2" class="tooltipped" data-position="top" data-delay="50" data-tooltip="CS Per Minute"><center>CS Per min</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="2" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Gold Per Minute"><center>GPM</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="2" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Damage Dealt to Champions Per Minute"><center>DMG Per Min</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="3" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Average Stealth Wards Per Match"><center>Wards Per Match</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="3" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Average Vision Wards Per Match"><center>Vision Wards Per Match</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="4" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Average CS Differential at 10mins"><center>CSD at 10</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="4" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Average Gold Differential at 10mins"><center>GD at 10</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="5" class="tooltipped" data-position="top" data-delay="50" data-tooltip="First Blood Kill Participation"><center>FB Kill Part.</center></th>
            <th data-tablesaw-sortable-col data-sortable-numeric data-tablesaw-priority="5" class="tooltipped" data-position="top" data-delay="50" data-tooltip="Total Games Played"><center>Games Played</center></th>
        </thead>
        <tbody class="playerStats">
            <?php
            foreach ($players as $playername=>$player) {
                echo "<tr>";
                echo "<td><center>" . $playername . "</center></td>";
                echo "<td><center>" . $player["team"][1] . "</center></td>";
                echo "<td><center>" . $player["role"] . "</center></td>";
                echo "<td><center>" . getKDA($player) . "</center></td>";
                echo "<td><center>" . $player["kills"] . "</center></td>";
                echo "<td><center>" . $player["deaths"] . "</center></td>";
                echo "<td><center>" . $player["assists"] . "</center></td>";
                echo "<td><center>" . getKillParticipation($player, $player["team"][2]) . "%</center></td>";
                echo "<td><center>" . number_format($player["cspm"], 1) . "</center></td>";
                echo "<td><center>" . number_format($player["goldpm"], 1) . "</center></td>";
                echo "<td><center>" . number_format($player["tddtc"], 1) . "</center></td>";
                echo "<td><center>" . number_format($player["wardsPlaced"], 1) . "</center></td>";
                echo "<td><center>" . number_format($player["visionWardsPlaced"], 1) . "</center></td>";
                echo "<td><center>" . number_format($player["csAt10"], 1) . "</center></td>";
                echo "<td><center>" . number_format($player["goldAt10"], 1) . "</center></td>";
                echo "<td><center>" . number_format($player["fbp"], 1) . "%</center></td>";
                echo "<td><center>" . $player["played"] . "</center></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script src="/js/tablesaw.js"></script>
<script>

    var data = {};
    var chosen = [];
    var all = [];

    /*! Tablesaw - v2.0.2 - 2015-10-28
    * https://github.com/filamentgroup/tablesaw
    * Copyright (c) 2015 Filament Group; Licensed  */
    ;(function( $ ) {

        // DOM-ready auto-init of plugins.
        // Many plugins bind to an "enhance" event to init themselves on dom ready, or when new markup is inserted into the DOM
        $( function(){
            $( document ).trigger( "enhance.tablesaw" );

            $(".tablesaw thead th").each(function(i, entry) {
                if ($(entry).data("search-n")) {
                    $(entry).css("padding-left", "24px");
                    $(entry).append("<i class='statSearch fa fa-filter'></i>");
                    var n = $(entry).data("search-n");
                    var name = $(entry).text();
                    var temp = $(".playerStats td:nth-child(" + n + ")").map(function(i, el) {
                        return $(el).text();
                    }).get();
                    data[name] = [];
                    chosen[n-1] = [];
                    all[n-1] = true;
                    for (var i = 0; i < temp.length; i++) {
                        if ($.inArray(temp[i], data[name]) === -1) {
                            data[name].push(temp[i]);
                        }
                    }
                    data[name].sort();
                }
            });

            $(".statSearch").on("click", function(e) {
                e.stopPropagation();
                var name = $(this).parent().text();
                var n = parseInt($(this).parent().data("search-n"));
                var html = "\
                    <input class='filled-in' id='searchall' type='checkbox' ";
                    if (all[n-1]) html += "checked";
                html += " /><label for='searchall'>All " + name + "s</label>\
                    <input class='search__input' type='text' placeholder='Search " + name + "s' />\
                    <div class='search__list'>";
                for (var i = 0; i < data[name].length; i++) {
                    html += "<input class='filled-in' id='searchitem" + i + "' type='checkbox' ";
                    if ($.inArray(data[name][i], chosen[n-1]) !== -1) html += "checked";
                    html +=" /><label for='searchitem" + i + "'>" + data[name][i] + "</label>\r\n";
                }
                html += "</div>\r\n<button>Update</button>";
                $("#search").html(html);
                $("#search").css({
                    "top" : $(this).parent().offset().top + $(this).parent().outerHeight() + 1,
                    "left" : $(this).parent().offset().left
                });
                $("#search").show();

                $(".search__input").on("keyup", function() {
                    var search = $(this).val().toLowerCase();
                    $(".search__list label").each(function(i, entry) {
                        if ($(entry).text().toLowerCase().search(search) > -1) {
                            $(entry).show();
                        } else {
                            $(entry).hide();
                        }
                    });
                });

                $(".search__list input").change(function() {
                    if (this.checked) {
                        if (all[n-1]) {
                            chosen[n-1] = [];
                            all[n-1] = false;
                            $("#searchall").prop("checked", false);
                            chosen[n-1].push($("label[for=" + $(this).attr("id") + "]").text());
                        } else {
                            chosen[n-1].push($("label[for=" + $(this).attr("id") + "]").text());
                        }
                    } else {
                        var x = chosen[n-1].indexOf($("label[for=" + $(this).attr("id") + "]").text());
                        console.log(x);
                        if (x > -1) {
                            chosen[n-1].splice(x, 1);
                        }
                        console.log(chosen[n-1]);
                        if (chosen[n-1].length == 0) {
                            all[n-1] = true;
                            $("#searchall").prop("checked", true);
                        }
                    }
                });

                $("#searchall").change(function() {
                    if (this.checked) {
                        all[n-1] = true;
                        chosen[n-1] = [];
                        $(".search__list input").each(function(i, entry) {
                            $(entry).prop("checked", false);
                        });
                    } else {
                        all[n-1] = false;
                    }
                });

                $("#search button").click(function() {
                    $(".playerStats tr").each(function(i, entry) {
                        flag = true;
                        for (var i = 0; i < chosen.length; i++) {
                            if (all[i]) {
                                continue;
                            } else {
                                var val = $($("td", entry)[i]).text();
                                if (chosen[i].indexOf(val) === -1) {
                                    flag = false;
                                    break;
                                }
                            }
                        }
                        if (flag) {
                            $(entry).show();
                        } else {
                            $(entry).hide();
                        }
                    });
                    $("#search").hide();
                });
            });

            $(document).click(function() {
                $("#search").hide();
            });
            $("#search").on("click", function(e) {
                e.stopPropagation();
            });

        });

    })( jQuery );
</script>

<?php require_once("template/footer.php");
