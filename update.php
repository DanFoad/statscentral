<?php

// Import Data
$gamesurl = "http://statscentral.gg:8080/lolstats/games/?count";
$teamsurl = "http://statscentral.gg:8080/lolstats/teams/";
$championsurl = "https://global.api.pvp.net/api/lol/static-data/euw/v1.2/champion?api_key=4429cbce-bf0f-468b-bd87-fe97ca969fd0";
$games;
for ($attempt = 0; $attempt < 5; $attempt++) {
    $games = file_get_contents($gamesurl);
    if ($games) break;
}
if (!$games)
    die("Failed to connect to database 5 times, exiting program...\r\n");

$teamslist = array();
$n = 1;
while (true) {
    echo "Getting Team names :: PAGE " . $n . "\r\n";
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $teams = json_decode(file_get_contents($teamsurl . "?page=" . $n), true);
        if ($teams) break;
    }
    if (!$teams)
        die("Failed to connect to database 5 times, exiting program...\r\n");

    if ($teams["_returned"] == 0)
        break;

    foreach ($teams["_embedded"]["rh:doc"] as $team) {
        array_push($teamslist, array($team["_id"]["\$oid"], $team["abbrev"]));
    }
    $n++;
}

$gameslist = json_decode($games, true);
$playerdata = array();
$championdata = array();
$eventdata = array();
$teamdata = $teamslist;
$championslistraw = file_get_contents($championsurl);
if (!$championslistraw) die("Failed to connect to Riot API, exiting program...\r\n");
$championslist = json_decode($championslistraw, true);
$champinfo = $championslist["data"];

// Set up champion info
foreach ($champinfo as &$champdata) {
	$champdata["data"] = array();
}

// Set up team info
/*
foreach ($teamdata as &$teamraw) {
    $teamraw["name"];
    $teamraw["players"] = array();
    $teamraw["played"] = 0;
    $teamraw["wins"] = 0;
}
*/
$pages = $gameslist["_total_pages"];
echo $pages . " pages found" . "\r\n";

$numberofgames = 0;

for ($i = 1; $i <= $pages; $i++) {
    echo "Getting page " . $i . "\r\n";
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $games = file_get_contents($gamesurl . "&page=" . $i);
        if ($games) break;
    }
    if (!$games)
        die("Failed to connect to database 5 times, exiting program...\r\n");

    $gameslist = json_decode($games, true);

    foreach ($gameslist["_embedded"]["rh:doc"] as $j=>$game) {
        $region = $game["_id"]["region"];
        $year = $game["_id"]["year"];
        $event = $game["_id"]["event"];

        /**** EVENT DATA ****/

        if (!array_key_exists($region, $eventdata)) {
            $eventdata[$region] = array();
        }
        if (!array_key_exists($year, $eventdata[$region])) {
            $eventdata[$region][$year] = array();
        }
        if (!array_key_exists($event, $eventdata[$region][$year])) {
            $eventdata[$region][$year][$event] = array();
            $eventdata[$region][$year][$event]["numberofgames"] = 0;
            $eventdata[$region][$year][$event]["playercount"] = array(0, 0, 0, 0, 0);
            $eventdata[$region][$year][$event]["csAt10"] = array(0, 0, 0, 0, 0);
            $eventdata[$region][$year][$event]["goldAt10"] = array(0, 0, 0, 0, 0);
        }
        $eventdata[$region][$year][$event]["numberofgames"]++;

        echo "Getting event data for EVENT::" . $event . " GAME::" . $eventdata[$region][$year][$event]["numberofgames"] . " . . . Successful\r\n";

        /**** END EVENT DATA ****/

        /**** PLAYER DATA ****/
        $numberofgames++;

        if (!array_key_exists($region, $playerdata)) {
            $playerdata[$region] = array();
        }
        if (!array_key_exists($year, $playerdata[$region])) {
            $playerdata[$region][$year] = array();
        }
        if (!array_key_exists($event, $playerdata[$region][$year])) {
            $playerdata[$region][$year][$event] = array();
        }


        for ($k = 0; $k <= 1; $k++) {
            for ($l = 0; $l < 5; $l++) {
                $player = array();
                echo "Getting player data for: GAME::" . $numberofgames . " SIDE::" . $k . " PLAYER::" . $l . " . . . ";
                $name = trim($game["teams"][$k]["players"][$l]["name"], " ");
                if (strlen($name) == 0) {
                    continue;
                }

                if (array_key_exists($name, $playerdata[$region][$year][$event])) {
                    echo "UPDATING . . . ";
                    $playerdata[$region][$year][$event][$name]["matchTime"] = $game["matchTime"];
                    updatePlayer($name, $playerdata[$region][$year][$event][$name], $game["teams"][$k]);
                    if ($playerdata[$region][$year][$event][$name]["role"] == "JUNGLE") {
                        foreach ($game["firsts"] as $first) {
                            if ($first["type"] == "blood" && $first["team"]["\$oid"] == $playerdata[$region][$year][$event][$name]["team"][0]) {
                                $playerdata[$region][$year][$event][$name]["fbpdata"][1]++;
                            }
                        }
                    }
                    echo "Successful\r\n";
                    continue;
                }

                $teamid = $game["teams"][$k]["_id"]["\$oid"];
                $logo;
                foreach ($teamslist as $team) {
                    if ($team[0] == $teamid) {
                        $logo = strtoupper($team[1]);
                        break;
                    }
                }

                $player["team"] = array($teamid, $logo, 0);
                $player["kills"] = 0;
                $player["assists"] = 0;
                $player["deaths"] = 0;
                $player["role"] = $game["teams"][$k]["players"][$l]["lane"];
                $player["played"] = 0;
                $player["champs"] = array();
                $player["tddtc"] = 0; // Total Damage Dealt To Champions
                $player["matchTime"] = $game["matchTime"];
                $player["fbpdata"] = array(0, 0);
                $player["fbp"] = 0; // First Blood Participation
                $player["ttccd"] = 0; // Total Time Crowd Control Dealt
                $player["wardsPlaced"] = 0;
                $player["visionWardsPlaced"] = 0;
                $player["csAt10"] = 0;
                $player["cs"] = 0; // CS total
                $player["gold"] = 0; // Gold total
                $player["goldAt10"] = 0;
                $player["minplayed"] = 0;

                foreach ($game["firsts"] as $first) {
                    if ($first["type"] == "blood" && $first["team"]["\$oid"] == $player["team"][0]) {
                        $player["fbpdata"][1]++;
                    }
                }

                updatePlayer($name, $player, $game["teams"][$k]);

                $playerdata[$region][$year][$event][$name] = $player;
                echo "Successful\r\n";
            }
        }
        /**** END PLAYER DATA ****/

        /**** CHAMPION DATA ****/

		$roles = ["TOP", "JUNGLE", "MID", "ADC", "SUPPORT"];

        if (!array_key_exists($region, $championdata)) {
            $championdata[$region] = array();
        }
        if (!array_key_exists($year, $championdata[$region])) {
            $championdata[$region][$year] = array();
        }
        if (!array_key_exists($event, $championdata[$region][$year])) {
            $championdata[$region][$year][$event] = array();
        }

        echo "Getting champion data for GAME::" . $numberofgames . " . . . ";

        $team1win = $game["teams"][0]["win"] ? 1 : 0;
        $team2win = $game["teams"][1]["win"] ? 1 : 0;

        echo "PICKS . . . ";

        for ($k = 0; $k < 5; $k++) {
            $team1pick = strval($game["teams"][0]["picks"][$k]);
            $team2pick = strval($game["teams"][1]["picks"][$k]);
            $team1name = getChampName($team1pick);
            $team2name = getChampName($team2pick);
			$teamnames = array($team1name, $team2name);

            if (!array_key_exists($team1name, $championdata[$region][$year][$event])) {
                $championdata[$region][$year][$event][$team1name] = [$team1pick, 0, 0, 0];
            }
            $championdata[$region][$year][$event][$team1name][1]++;
            $championdata[$region][$year][$event][$team1name][3] += $team1win;

            if (!array_key_exists($team2name, $championdata[$region][$year][$event])) {
                $championdata[$region][$year][$event][$team2name] = [$team2pick, 0, 0, 0];
            }
            $championdata[$region][$year][$event][$team2name][1]++;
            $championdata[$region][$year][$event][$team2name][3] += $team2win;

			/**** CHAMPION INFO DATA ****/

			for ($l = 0; $l <= 1; $l++) {
				if (!array_key_exists($region, $champinfo[$teamnames[$l]]["data"])) {
					$champinfo[$teamnames[$l]]["data"][$region] = array();
				}
                if (!array_key_exists($year, $champinfo[$teamnames[$l]]["data"][$region])) {
                    $champinfo[$teamnames[$l]]["data"][$region][$year] = array();
                }
				if (!array_key_exists($event, $champinfo[$teamnames[$l]]["data"][$region][$year])) {
					$champinfo[$teamnames[$l]]["data"][$region][$year][$event] = array();
					$champinfo[$teamnames[$l]]["data"][$region][$year][$event]["bans"] = 0;
					$champinfo[$teamnames[$l]]["data"][$region][$year][$event]["players"] = array();
				}
                $role = $game["teams"][$l]["players"][$k]["lane"];
                if (!array_key_exists($role, $champinfo[$teamnames[$l]]["data"][$region][$year][$event])) {
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
                    // array(played, wins) in blocks of 10: 10-20, 20-30 etc
                    $champ["winlength"] = array(
                        array(0, 0), array(0, 0), array(0, 0), array(0, 0), array(0, 0)
                    );
                    $champ["patchrate"] = array();

                    $champinfo[$teamnames[$l]]["data"][$region][$year][$event][$role] = $champ;
                }
                $patch = explode(".", $game["gameVersion"])[0] . "." . explode(".", $game["gameVersion"])[1];
                if (!array_key_exists($patch, $champinfo[$teamnames[$l]]["data"][$region][$year][$event][$role]["patchrate"])) {
                    $champinfo[$teamnames[$l]]["data"][$region][$year][$event][$role]["patchrate"][$patch] = 0;
                }
				updateChampionInfo($champinfo[$teamnames[$l]]["data"][$region][$year][$event], $game, $l, $k);
			}

			/**** END CHAMPION INFO DATA ****/
        }

        echo "BANS . . . ";
        for ($k = 0; $k < count($game["teams"][0]["bans"]); $k++) {
            $team1ban = strval($game["teams"][0]["bans"][$k]);
            $team1name = getChampName($team1ban);

            if (!array_key_exists($team1name, $championdata[$region][$year][$event])) {
                $championdata[$region][$year][$event][$team1name] = [$team1ban, 0, 0, 0];
            }
            $championdata[$region][$year][$event][$team1name][2]++;

			if (!array_key_exists($region, $champinfo[$team1name]["data"])) {
				$champinfo[$team1name]["data"][$region] = array();
			}
			if (!array_key_exists($year, $champinfo[$team1name]["data"][$region])) {
                $champinfo[$team1name]["data"][$region][$year] = array();
            }
            if (!array_key_exists($event, $champinfo[$team1name]["data"][$region][$year])) {
				$champinfo[$team1name]["data"][$region][$year][$event] = array();
				$champinfo[$team1name]["data"][$region][$year][$event]["bans"] = 0;
                $champinfo[$team1name]["data"][$region][$year][$event]["players"] = array();
			}
			$champinfo[$team1name]["data"][$region][$year][$event]["bans"]++;

        }
        for ($k = 0; $k < count($game["teams"][1]["bans"]); $k++) {
            $team2ban = strval($game["teams"][1]["bans"][$k]);
            $team2name = getChampName($team2ban);

            if (!array_key_exists($team2name, $championdata[$region][$year][$event])) {
                $championdata[$region][$year][$event][$team2name] = [$team2ban, 0, 0, 0];
            }
            $championdata[$region][$year][$event][$team2name][2]++;

			if (!array_key_exists($region, $champinfo[$team2name]["data"])) {
				$champinfo[$team2name]["data"][$region] = array();
			}
			if (!array_key_exists($year, $champinfo[$team2name]["data"][$region])) {
                $champinfo[$team2name]["data"][$region][$year] = array();
            }
            if (!array_key_exists($event, $champinfo[$team2name]["data"][$region][$year])) {
				$champinfo[$team2name]["data"][$region][$year][$event] = array();
				$champinfo[$team2name]["data"][$region][$year][$event]["bans"] = 0;
                $champinfo[$team2name]["data"][$region][$year][$event]["players"] = array();
			}
			$champinfo[$team2name]["data"][$region][$year][$event]["bans"]++;
        }

        echo "Sucessful\r\n";

		/**** END CHAMPION DATA ****/
    }
    echo "All player data collected for page " . $i . "\r\n";
    echo "All champion data collected for page " . $i . "\r\n";
    echo "All event data collected for page " . $i . "\r\n";
}
echo "All pages complete\r\n";

// Cleanup
foreach ($playerdata as $region=>$regiondata) {
    foreach ($regiondata as $year=>$yeardata) {
        foreach($yeardata as $event=>$splitdata) {
            foreach ($splitdata as $playername=>$player) {
                $playerdata[$region][$year][$event][$playername]["champ"] = getMostPlayed($player["champs"]);
                $playerdata[$region][$year][$event][$playername]["fbp"] = number_format(100 * $player["fbpdata"][0] / max($player["fbpdata"][1], 1));

                switch($playerdata[$region][$year][$event][$playername]["role"]) {
                    case "TOP":
                        $eventdata[$region][$year][$event]["playercount"][0]++;
                        $eventdata[$region][$year][$event]["csAt10"][0] += $player["csAt10"] / $player["played"];
                        $eventdata[$region][$year][$event]["goldAt10"][0] += $player["goldAt10"] / $player["played"];
                        break;
                    case "JUNGLE":
                        $eventdata[$region][$year][$event]["playercount"][1]++;
                        $eventdata[$region][$year][$event]["csAt10"][1] += $player["csAt10"] / $player["played"];
                        $eventdata[$region][$year][$event]["goldAt10"][1] += $player["goldAt10"] / $player["played"];
                        break;
                    case "MID":
                        $eventdata[$region][$year][$event]["playercount"][2]++;
                        $eventdata[$region][$year][$event]["csAt10"][2] += $player["csAt10"] / $player["played"];
                        $eventdata[$region][$year][$event]["goldAt10"][2] += $player["goldAt10"] / $player["played"];
                        break;
                    case "ADC":
                        $eventdata[$region][$year][$event]["playercount"][3]++;
                        $eventdata[$region][$year][$event]["csAt10"][3] += $player["csAt10"] / $player["played"];
                        $eventdata[$region][$year][$event]["goldAt10"][3] += $player["goldAt10"] / $player["played"];
                        break;
                    case "SUPPORT":
                        $eventdata[$region][$year][$event]["playercount"][4]++;
                        $eventdata[$region][$year][$event]["csAt10"][4] += $player["csAt10"] / $player["played"];
                        $eventdata[$region][$year][$event]["goldAt10"][4] += $player["goldAt10"] / $player["played"];
                        break;
                }

                unset($playerdata[$region][$year][$event][$playername]["champs"]);
                unset($playerdata[$region][$year][$event][$playername]["fbpdata"]);
                unset($playerdata[$region][$year][$event][$playername]["matchTime"]);
            }
        }
    }
}
foreach ($eventdata as $region=>$regiondata) {
    foreach ($regiondata as $year=>$yeardata) {
        foreach($yeardata as $event=>$splitdata) {
            for ($i = 0; $i < 5; $i++) {
                $eventdata[$region][$year][$event]["csAt10"][$i] = $eventdata[$region][$year][$event]["csAt10"][$i] / $eventdata[$region][$year][$event]["playercount"][$i];
                $eventdata[$region][$year][$event]["goldAt10"][$i] = $eventdata[$region][$year][$event]["goldAt10"][$i] / $eventdata[$region][$year][$event]["playercount"][$i];
            }
        }
    }
}
unset($gameslist);

echo "Writing player data to file... ";

$fp = fopen("/var/www/html/dev/players.json", "w");
fwrite($fp, json_encode($playerdata));
fclose($fp);

echo "Sucessful\r\n";
echo "Player data stored, update complete.\r\n";

echo "Writing champion data to file... ";

$fp = fopen("/var/www/html/dev/champions.json", "w");
fwrite($fp, json_encode($championdata));
fclose($fp);

if (count($champinfo) > 0) {
    $fp = fopen("/var/www/html/dev/championinfo.json", "w");
    fwrite($fp, json_encode($champinfo));
    fclose($fp);
}

echo "Sucessful\r\n";
echo "Champion data stored, update complete.\r\n";

echo "Writing event data to file... ";

$fp = fopen("/var/www/html/dev/events.json", "w");
fwrite($fp, json_encode($eventdata));
fclose($fp);

echo "Sucessful\r\n";
echo "Event data stored, update complete.\r\n";

function getMostPlayed($champs) {
    $c = array_count_values($champs);
    if (empty($c)) {
        return array_rand($champs);
    }
    return array_search(max($c), $c);
}

function getChampName($id) {
	global $champinfo;
	foreach ($champinfo as $name=>$data) {
		if (strval($data["id"]) == $id) {
			return $name;
		}
	}
}

function updatePlayer($name, &$player, $team) {
    for ($j = 0; $j < count($team["players"]); $j++) {
        $player["team"][2] += $team["players"][$j]["kills"];
        if (trim($team["players"][$j]["name"], " ") == $name) {
            $player["kills"] += $team["players"][$j]["kills"];
            $player["deaths"] += $team["players"][$j]["deaths"];
            $player["assists"] += $team["players"][$j]["assists"];
            $player["wardsPlaced"] += $team["players"][$j]["wardsPlaced"];
            $player["visionWardsPlaced"] += $team["players"][$j]["visionWardsBoughtInGame"];
            $player["ttccd"] += $team["players"][$j]["totalTimeCrowdControlDealt"];
            $player["tddtc"] += $team["players"][$j]["totalDamageDealtToChampions"];
            $player["csAt10"] += $team["players"][$j]["csAt10"];
            $player["cs"] += $team["players"][$j]["totalMinionsKilled"];
            $player["minplayed"] += ($player["matchTime"] / 60);
            $player["goldAt10"] += $team["players"][$j]["timeline"]["goldPerMinDeltas"]["0-10"] * 10;
            $player["gold"] += $team["players"][$j]["goldEarned"];
            if ($team["players"][$j]["firstBloodKill"] || $team["players"][$j]["firstBloodAssist"]) {
                $player["fbpdata"][0]++;
            }

            $player["played"]++;
            array_push($player["champs"], $team["picks"][$j]);
        }
    }
}

function updateChampionInfo(&$champ, $game, $team, $k) {
	global $teamslist;
	$win = $game["teams"][$team]["win"] ? 1 : 0;
	$player = $game["teams"][$team]["players"][$k];

	$teamid = $game["teams"][$team]["_id"]["\$oid"];
	$teamname;
	foreach ($teamslist as $team) {
		if ($team[0] == $teamid) {
			$teamname = strtoupper($team[1]);
			break;
		}
	}

	$champ[$player["lane"]]["played"]++;
	if (!in_array(array($player["name"], $teamname), $champ["players"])) {
		array_push($champ["players"], array($player["name"], $teamname));
	}
	$champ[$player["lane"]]["wins"] += $win;
	$champ[$player["lane"]]["gold"] += $player["goldEarned"];
	$champ[$player["lane"]]["kills"] += $player["kills"];
	$champ[$player["lane"]]["deaths"] += $player["deaths"];
	$champ[$player["lane"]]["assists"] += $player["assists"];
	$champ[$player["lane"]]["damage"] += $player["totalDamageDealtToChampions"];
	$champ[$player["lane"]]["cs"] += $player["totalMinionsKilled"];
    $time = $game["matchTime"] / 60;
    $champ[$player["lane"]]["matchtime"] += $time;
    if ($time < 20) {
        $champ[$player["lane"]]["winlength"][0][0]++;
        $champ[$player["lane"]]["winlength"][0][1] += $win;
    } else if ($time < 30) {
        $champ[$player["lane"]]["winlength"][1][0]++;
        $champ[$player["lane"]]["winlength"][1][1] += $win;
    } else if ($time < 40) {
        $champ[$player["lane"]]["winlength"][2][0]++;
        $champ[$player["lane"]]["winlength"][2][1] += $win;
    } else if ($time < 50) {
        $champ[$player["lane"]]["winlength"][3][0]++;
        $champ[$player["lane"]]["winlength"][3][1] += $win;
    } else {
        $champ[$player["lane"]]["winlength"][4][0]++;
        $champ[$player["lane"]]["winlength"][4][1] += $win;
    }
    
    $patch = explode(".", $game["gameVersion"])[0] . "." . explode(".", $game["gameVersion"])[1];
    $champ[$player["lane"]]["patchrate"][$patch]++;
}
