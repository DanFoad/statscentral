<?php

/** champion.php
 * Individual Champion pages
 * @author Dan Foad
 */

// If not champion specified, go back to all champs page
if (!isset($_GET["champion"])) {
    header("Location: /champions/");
}

// Import Data
$championdata = json_decode(file_get_contents("champions.json"), true);
$championinfo = json_decode(file_get_contents("championinfo.json"), true);
$regionslist = array_keys($championdata);

// If champion specified doesn't exist, go back to all champs page
if (!array_key_exists($_GET["champion"], $championinfo)) {
	header("Location: /champions/");
}

// Get header
require_once("template/header.php");

// Initialise specifiers
$region = "GLOBAL";
$year = "ALL YEARS";
$split = "ALL SPLITS";
$champion = $championinfo[$_GET["champion"]];

// Get specifiers from GET request
if (isset($_GET["region"])) {
    $region = strtoupper($_GET["region"]);
}
if (isset($_GET["year"])) {
	$year = $_GET["year"];
}
if (isset($_GET["split"])) {
    $split = str_replace("_", " ", $_GET["split"]);
}

// Initialise working arrays
$roles = ["TOP", "JUNGLE", "MID", "ADC", "SUPPORT"];
$champdata = array();
$allchamps = array();

// Initialise champion data
foreach ($roles as $role) { // Step through all roles
	$champ = array();
	$champ["played"] = 0;
	$champ["wins"] = 0;
	$champ["gold"] = 0;
	$champ["kills"] = 0;
	$champ["deaths"] = 0;
	$champ["assists"] = 0;
	$champ["damage"] = 0;
	$champ["cs"] = 0;
    $champ["matchtime"] = 0;
    $champ["winlength"] = array(
        array(0, 0), array(0, 0), array(0, 0), array(0, 0), array(0, 0)
    );
    $champ["patchrate"] = array();
	
	$champdata[$role] = $champ;
	$allchamps[$role] = $champ;
}
$champdata["bans"] = 0;
$allchamps["totalplayed"] = 0;
$allchamps["bans"] = 0;
$champdata["players"] = array();
$mostplayed = array("", 0);

// Set champion data from data resource
foreach ($champion["data"] as $regionname=>$regiondata) { // Step through specified regions
	if ($region != "GLOBAL" && strtoupper($regionname) != strtoupper($region)) {
		continue;
	}
	foreach ($regiondata as $yearname=>$yeardata) { // Step through specified years
		if ($year != "ALL YEARS" && $yearname != $year) {
			continue;
		}
        foreach ($yeardata as $splitname=>$splitdata) { // Step through specified splits
            if ($split != "ALL SPLITS" && strtoupper($splitname) != strtoupper($split)) {
                continue;
            }
            foreach ($roles as $role) { // Step through all roles
                if (!array_key_exists($role, $splitdata)) continue;
                $champdata[$role]["played"] += $splitdata[$role]["played"];
                if ($champdata[$role]["played"] > $mostplayed[1]) {
                    $mostplayed[0] = $role;
                    $mostplayed[1] = $champdata[$role]["played"];
                }			
                $champdata[$role]["wins"] += $splitdata[$role]["wins"];
                $champdata[$role]["gold"] += $splitdata[$role]["gold"];
                $champdata[$role]["kills"] += $splitdata[$role]["kills"];
                $champdata[$role]["deaths"] += $splitdata[$role]["deaths"];
                $champdata[$role]["assists"] += $splitdata[$role]["assists"];
                $champdata[$role]["damage"] += $splitdata[$role]["damage"];
                $champdata[$role]["cs"] += $splitdata[$role]["cs"];
                $champdata[$role]["matchtime"] += $splitdata[$role]["matchtime"];
                for ($i = 0; $i < 5; $i++) {
                    $champdata[$role]["winlength"][$i][0] += $splitdata[$role]["winlength"][$i][0];
                    $champdata[$role]["winlength"][$i][1] += $splitdata[$role]["winlength"][$i][1];
                }
                foreach ($splitdata[$role]["patchrate"] as $patch=>$patchplays) { // Step through each patch
                    if (!array_key_exists($patch, $champdata[$role]["patchrate"])) {
                        $champdata[$role]["patchrate"][$patch] = $patchplays;
                    } else {
                        $champdata[$role]["patchrate"][$patch] += $patchplays;
                    }
                }
            }
            $champdata["bans"] += $splitdata["bans"];
            for ($i = 0; $i < count($splitdata["players"]); $i++) { // Step through each player in split
                if (strlen($splitdata["players"][$i][0]) == 0) continue;
                $found = false;
                for ($j = 0; $j < count($champdata["players"]); $j++) {
                    if ($splitdata["players"][$i][0] == $champdata["players"][$j][0] && $splitdata["players"][$i][1] == $champdata["players"][$j][1]) {
                        $found = true;
                    }
                }
                if (!$found) array_push($champdata["players"], $splitdata["players"][$i]);
            }
        }
	}
}

// Gether information for all champions
foreach ($championinfo as $champraw) { // Step through each champion
	foreach ($champraw["data"] as $regionname=>$regiondata) { // Step through each region
		if ($region != "GLOBAL" && strtoupper($regionname) != strtoupper($region)) {
			continue;
		}
		foreach ($regiondata as $yearname=>$yeardata) { // Step through each year
			if ($year != "ALL YEARS" && $yearname != $year) {
				continue;
			}
            foreach ($yeardata as $splitname=>$splitdata) {// Step through each split
                if ($split != "ALL SPLITS" && strtoupper($splitname) != strtoupper($split)) {
                    continue;
                }
                foreach ($roles as $role) { // Step through all roles
                    if (!array_key_exists($role, $splitdata)) continue;
                    $allchamps["totalplayed"] += $splitdata[$role]["played"];
                    $allchamps[$role]["played"] += $splitdata[$role]["played"];
                    $allchamps[$role]["wins"] += $splitdata[$role]["wins"];
                    $allchamps[$role]["gold"] += $splitdata[$role]["gold"];
                    $allchamps[$role]["kills"] += $splitdata[$role]["kills"];
                    $allchamps[$role]["deaths"] += $splitdata[$role]["deaths"];
                    $allchamps[$role]["assists"] += $splitdata[$role]["assists"];
                    $allchamps[$role]["damage"] += $splitdata[$role]["damage"];
                    $allchamps[$role]["cs"] += $splitdata[$role]["cs"];
                    $allchamps[$role]["matchtime"] += $splitdata[$role]["matchtime"];
                    for ($i = 0; $i < 5; $i++) {
                        $allchamps[$role]["winlength"][$i][0] += $splitdata[$role]["winlength"][$i][0];
                        $allchamps[$role]["winlength"][$i][1] += $splitdata[$role]["winlength"][$i][1];
                    }
                    foreach ($splitdata[$role]["patchrate"] as $patch=>$patchplays) { // Step through each patch
                        if (!array_key_exists($patch, $allchamps[$role]["patchrate"])) {
                            $allchamps[$role]["patchrate"][$patch] = $patchplays;
                        } else {
                            $allchamps[$role]["patchrate"][$patch] += $patchplays;
                        }
                    }
                }
                $allchamps["bans"] += $splitdata["bans"];
            }
		}
	}
}

// Get patches champ has benn played in
$patches = array_keys($champdata[$mostplayed[0]]["patchrate"]);
sort($patches);

/** f
 * Format division operations into human-readable format
 * @param $n    Numerator for fraction
 * @param $p    Denominator for fraction
 * @param $d    Number of decimal places to go to
 * @return Formatted number of division
 */
function f($n, $p, $d) {
	$str = number_format($n / $p, $d, ".", "");
	return $str;
}

?>

<ul id="regionchooser" class="dropdown-content">
    <?php
    $year_temp = "";
    if ($year != "ALL YEARS") $year_temp = $year . "/";
    echo "<a href=\"/champion/" . $champion["key"] . "/" . $year_temp . "\"><li>Global</li></a>";
    foreach (array_keys($championdata) as $region_temp) {
        echo "<a href='/champion/" . $champion["key"] . "/" . $region_temp . "/" . $year_temp . "'><li>" . $region_temp . "</li></a>";
    }
    ?>
</ul>
<ul id="yearchooser" class="dropdown-content">
    <?php
    $region_temp = "";
    if ($region != "GLOBAL") $region_temp = $region . "/";
    echo "<a href=\"/champion/" . $champion["key"] . "/" . $region_temp . "\"><li>All Years</li></a>";
    echo "<a href=\"/champion/" . $champion["key"] . "/" . $region_temp . "2015/\"><li>2015</li></a>";
    echo "<a href=\"/champion/" . $champion["key"] . "/" . $region_temp . "2016/\"><li>2016</li></a>";
    
    ?>
</ul>

<?php
    if ($region != "GLOBAL" && $year != "ALL YEARS") {
?>
<ul id="splitchooser" class="dropdown-content">
    <?php
    echo "<a href=\"/champion/" . $champion["key"] . "/" . $region . "/" . $year . "/\"><li>All Splits</li></a>";
    foreach (array_keys($championdata[$region][$year]) as $split_temp) {
        $split_temp_url = str_replace(" ", "_", $split_temp);
        echo "<a href=\"/champion/" . $champion["key"] . "/" . $region . "/" . $year . "/" . $split_temp_url . "/\"><li>" . $split_temp . "</li></a>";
    }
    ?>
</ul>
<?php
    }
?>

<div class="row champ__splash z-depth-1">
    <img class="champ__splash--image" src="/img/champSplash/<?php echo $champion["key"]; ?>_Splash_Centered_0.jpg" alt="">
    <div class="champ__title">
        <h2><?php echo $champion["name"]; ?></h2>
        <h3><?php echo ucfirst($champion["title"]); ?></h3>
    </div>
</div>

<ul class="league-chooser row">
    <li><a class="dropdown-button" href="#" data-activates="regionchooser"><?php echo $region; ?><i class="material-icons right">arrow_drop_down</i></a></li>
    <li><a class="dropdown-button" href="#" data-activates="yearchooser"><?php echo $year; ?><i class="material-icons right">arrow_drop_down</i></a></li>
    <?php
    if ($region != "GLOBAL" && $year != "ALL YEARS") {
    ?>
    <li><a class="dropdown-button" href="#" data-activates="splitchooser"><?php echo $split; ?><i class="material-icons right">arrow_drop_down</i></a></li>
    <?php
    }
    ?>
</ul>

<div class="row champ__stats z-depth-1">
	<div class="champ__players">
		<img src="/img/logo/leagues/<?php echo strtolower($region); ?>.png" alt="" />
		<div class="champ__players--container">
            <div class="champ__players--list">
                <?php
                    foreach ($champdata["players"] as $player) {
                        echo "<div class='champ__player'><img src='/img/logo/teams/" . strtolower($player[1]) . ".png' alt='' /><p>" . $player[0] . "</p></div>";
                    }

                ?>
            </div>
        </div>
	</div>
	<div class="champ__table--container">
		<div class="champ__table">
			<table class="tablesaw highlight">
				<thead>
					<th><?php echo $champion["name"] ?></th>
					<?php
						foreach ($roles as $role) {
							if ($champdata[$role]["played"] > 0) {
								echo "<th>" . $role . "</th>";
							}
						}
					?>
				</thead>
				<tbody>
					<tr>
						<td>Win Rate</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . number_format(($champdata[$role]["wins"] / $champdata[$role]["played"]) * 100, 1) . "%</td>";
								}
							}
						?>
					</tr>
					<tr>
						<td>Played</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . $champdata[$role]["played"] . "</td>";
								}
							}
						?>
					</tr>
					<tr>
						<td>Ban Rate</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . number_format(($champdata["bans"] / $allchamps["totalplayed"]) * 10, 1) . "%</td>";
								}
							}
						?>
					</tr>
					<tr>
						<td>Average Gold Earned</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . number_format($champdata[$role]["gold"] / $champdata[$role]["played"], 1) . "</td>";
								}
							}
						?>
					</tr>
					<tr>
						<td>Average Kills</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . number_format($champdata[$role]["kills"] / $champdata[$role]["played"], 1) . "</td>";
								}
							}
						?>
					</tr>
					<tr>
						<td>Average Deaths</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . number_format($champdata[$role]["deaths"] / $champdata[$role]["played"], 1) . "</td>";
								}
							}
						?>
					</tr>
					<tr>
						<td>Average Assists</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . number_format($champdata[$role]["assists"] / $champdata[$role]["played"], 1) . "</td>";
								}
							}
						?>
					</tr>
					<tr>
						<td>Average DMG to Champs</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . number_format($champdata[$role]["damage"] / $champdata[$role]["played"], 1) . "</td>";
								}
							}
						?>
					</tr>
					<tr>
						<td>Average CS</td>
						<?php
							foreach ($roles as $role) {
								if ($champdata[$role]["played"] > 0) {
									echo "<td>" . number_format($champdata[$role]["cs"] / $champdata[$role]["played"], 1) . "</td>";
								}
							}
						?>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="champ__radarchart">
			<canvas id="champ__radar"></canvas>
		</div>
	</div>
    <div class="champ__linecharts">
        <div>
            <h2>Win Rate % - Game Length</h2>
            <canvas id="champ__winlength"></canvas>
        </div>
        <div>
            <h2>Play Rate % - Patch</h2>
            <canvas id="champ__patchrate"></canvas>
        </div>
    </div>
</div>

<script src="/js/tablesaw.js"></script>
<script src="/js/chart.min.js"></script>
<script src="/js/vibrant.min.js"></script>
<script>

    /*! Tablesaw - v2.0.2 - 2015-10-28
    * https://github.com/filamentgroup/tablesaw
    * Copyright (c) 2015 Filament Group; Licensed  */
    ;(function( $ ) {
        

        // DOM-ready auto-init of plugins.
        // Many plugins bind to an "enhance" event to init themselves on dom ready, or when new markup is inserted into the DOM
        $( function(){
            $( document ).trigger( "enhance.tablesaw" );
			
        
            /* COLOUR THEMING */
            var img = document.createElement('img');
            img.setAttribute('src', "/img/champ/<?php echo $champion["id"]; ?>.jpg");
            
            img.addEventListener("load", function() {
                var vibrant = new Vibrant(img);
                var swatches = vibrant.swatches();
                var c = swatches["Vibrant"].getHex();
                var d = swatches["DarkMuted"].getHex();
                var r = swatches["Vibrant"].getRgb();
                var chart = swatches["Vibrant"].getRgb();

                $("nav, .league-chooser li, .champ__table th, .page-footer, .dropdown-content").css("background-color", c);
                $(".champ__title, .cd-top").css("background-color", "rgba(" + r[0] + "," + r["1"] + "," + r["2"] + ",0.6)");
                $(".champ__table thead, .champ__table th, .champ__players--list").css("border-color", d);
                $(".champ__stats h2").css("color", c);
                
                $("nav ul a").mouseover(function() {
                    $(this).css("background-color", d);
                }).mouseleave(function() {
                    $(this).css("background-color", c);
                });
                
                /* CHARTS */
                var radarData = {
                    labels: ["Win Rate / 10", "AVG Kills", "AVG Deaths", "AVG Assists", "AVG CS"],
                    datasets: [
                        {
                            label: "All Champions",
                            borderColor: "rgba(68, 68, 68, 0.5)",
                            data: [<?php
                                echo f($allchamps[$mostplayed[0]]["wins"] * 10, $allchamps[$mostplayed[0]]["played"], 1) . ",";
                                echo f($allchamps[$mostplayed[0]]["kills"], $allchamps[$mostplayed[0]]["played"], 1) . ",";
                                echo f($allchamps[$mostplayed[0]]["deaths"], $allchamps[$mostplayed[0]]["played"], 1) . ",";
                                echo f($allchamps[$mostplayed[0]]["assists"], $allchamps[$mostplayed[0]]["played"], 1) . ",";
                                echo f($allchamps[$mostplayed[0]]["cs"], $allchamps[$mostplayed[0]]["matchtime"], 1);
                            ?>]
                        },
                        {
                            label: "<?php echo $champion["name"] . " - " . $mostplayed[0]; ?>",
                            backgroundColor: "rgba(" + chart[0] + "," + chart[1] + "," + chart[2] + ",0.2)",
                            borderColor: "rgba(" + chart[0] + "," + chart[1] + "," + chart[2] + ",1)",
                            data: [<?php
                                echo f($champdata[$mostplayed[0]]["wins"] * 10, $champdata[$mostplayed[0]]["played"], 1) . ",";
                                echo f($champdata[$mostplayed[0]]["kills"], $champdata[$mostplayed[0]]["played"], 1) . ",";
                                echo f($champdata[$mostplayed[0]]["deaths"], $champdata[$mostplayed[0]]["played"], 1) . ",";
                                echo f($champdata[$mostplayed[0]]["assists"], $champdata[$mostplayed[0]]["played"], 1) . ",";
                                echo f($champdata[$mostplayed[0]]["cs"], $champdata[$mostplayed[0]]["matchtime"], 1);
                            ?>]
                        }
                    ]
                };

                var winlengthData = {
                    labels: ["10-20", "20-30", "30-40", "40-50", "50+"],
                    datasets: [
                        {
                            label: "All Champions",
                            borderColor: "rgba(68, 68, 68, 0.5)",
                            data: [<?php
                                echo f($allchamps[$mostplayed[0]]["winlength"][0][1], $allchamps[$mostplayed[0]]["winlength"][0][0], 2) . ",";
                                echo f($allchamps[$mostplayed[0]]["winlength"][1][1], $allchamps[$mostplayed[0]]["winlength"][1][0], 2) . ",";
                                echo f($allchamps[$mostplayed[0]]["winlength"][2][1], $allchamps[$mostplayed[0]]["winlength"][2][0], 2) . ",";
                                echo f($allchamps[$mostplayed[0]]["winlength"][3][1], $allchamps[$mostplayed[0]]["winlength"][3][0], 2) . ",";
                                echo f($allchamps[$mostplayed[0]]["winlength"][4][1], $allchamps[$mostplayed[0]]["winlength"][4][0], 2);
                            ?>]
                        },
                        {
                            label: "<?php echo $champion["name"] . " - " . $mostplayed[0]; ?>",
                            fill: true,
                            backgroundColor: "rgba(" + chart[0] + "," + chart[1] + "," + chart[2] + ",0.2)",
                            borderColor: "rgba(" + chart[0] + "," + chart[1] + "," + chart[2] + ",1)",
                            data: [<?php
                                echo f($champdata[$mostplayed[0]]["winlength"][0][1], $champdata[$mostplayed[0]]["winlength"][0][0], 2) . ",";
                                echo f($champdata[$mostplayed[0]]["winlength"][1][1], $champdata[$mostplayed[0]]["winlength"][1][0], 2) . ",";
                                echo f($champdata[$mostplayed[0]]["winlength"][2][1], $champdata[$mostplayed[0]]["winlength"][2][0], 2) . ",";
                                echo f($champdata[$mostplayed[0]]["winlength"][3][1], $champdata[$mostplayed[0]]["winlength"][3][0], 2) . ",";
                                echo f($champdata[$mostplayed[0]]["winlength"][4][1], $champdata[$mostplayed[0]]["winlength"][4][0], 2);
                            ?>]
                        }
                    ]
                };
                var patchrateData = {
                    labels: <?php echo json_encode($patches); ?>,
                    datasets: [
                        {
                            label: "All Champions",
                            borderColor: "rgba(68, 68, 68, 0.5)",
                            data: [<?php
                                $i = 0;
                                foreach ($patches as $patchname) {
                                    $i++;
                                    echo f($allchamps[$mostplayed[0]]["patchrate"][$patchname] * 100, $allchamps[$mostplayed[0]]["played"], 0);
                                    if ($i != count($patches)) echo ",";
                                }
                            ?>]
                        },
                        {
                            label: "<?php echo $champion["name"] . " - " . $mostplayed[0]; ?>",
                            fill: true,
                            backgroundColor: "rgba(" + chart[0] + "," + chart[1] + "," + chart[2] + ",0.2)",
                            borderColor: "rgba(" + chart[0] + "," + chart[1] + "," + chart[2] + ",1)",
                            data: [<?php
                                $i = 0;
                                foreach ($patches as $patchname) {
                                    $i++;
                                    echo f($champdata[$mostplayed[0]]["patchrate"][$patchname] * 100, $champdata[$mostplayed[0]]["played"], 0);
                                    if ($i != count($patches)) echo ",";
                                }
                            ?>]
                        }
                    ]
                };

                var radarctx = $("#champ__radar").get(0).getContext("2d");
                var champradar = new Chart(radarctx, {
                    type: "radar",
                    data: radarData,
                    options: {}
                });

                var winlengthctx = $("#champ__winlength").get(0).getContext("2d");
                var winlength = new Chart(winlengthctx, {
                    type: "line",
                    data: winlengthData,
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    min: 0,
                                    max: 1
                                }
                            }]
                        }
                    }
                });

                var patchratectx = $("#champ__patchrate").get(0).getContext("2d");
                var patchrate = new Chart(patchratectx, {
                    type: "line",
                    data: patchrateData,
                    options: {}
                });
                
            });
        
		});

    })( jQuery );
</script>

<?php require_once("template/footer.php");
