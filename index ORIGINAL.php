<?php
////////////////////////////||
// BACKUP FILE - NOT IN USE ||
////////////////////////////||

require_once("template/header.php");

// Import Data
$gamesurl = "http://45.32.242.231:8080/lolstats/games";
$teamsurl = "http://45.32.242.231:8080/lolstats/teams/";
$games = file_get_contents($gamesurl);
$gameslist = json_decode($games, true);

function getGame($gameslist) {
    $flag = true;
    $firstgame;
    $i = 0;
    while ($flag) {
        $i++;
        $rand = rand(0, count($gameslist["_embedded"]["rh:doc"]));
        $firstgame = $gameslist["_embedded"]["rh:doc"][$rand];
        if ($firstgame["_id"]["year"] == date("Y") || $i == 1000) {
            $flag = false;
        }
    }
    return $firstgame;
}

// Generate user data from imports
$game = getGame($gameslist);
$blueteam = json_decode(file_get_contents($teamsurl . $game["teams"][0]["_id"]["\$oid"]), true);
$redteam = json_decode(file_get_contents($teamsurl . $game["teams"][1]["_id"]["\$oid"]), true);
$bluelogo = "img/logo/" . strtolower($blueteam["abbrev"]) . ".png";
$redlogo = "img/logo/" . strtolower($redteam["abbrev"]) . ".png";

// Calculate data for overview
$role = rand(0,4);
$blueplayers = $game["teams"][0]["players"];
$blueplayer;
for ($i = 0; $i < count($blueplayers); $i++) {
    if (isRole($role, $blueplayers[$i])) {
        $blueplayer = $blueplayers[$i];
        break;
    }
}
if (!isset($blueplayer)) $blueplayer = $blueplayers[$role];
$redplayers = $game["teams"][1]["players"];
$redplayer;
for ($i = 0; $i < count($redplayers); $i++) {
    if (isRole($role, $redplayers[$i])) {
        $redplayer = $redplayers[$i];
        break;
    }
}
if (!isset($redplayer)) $redplayer = $redplayers[$role];

/** isRole
 * Test to see whether a player matches the required role
 * @param $role     The number of the role to compare to
 * @param $player   The player to check the role of
 * @return true or false depending on whether the user is the role
 */
function isRole($role, $player) {
    $r = $player["role"];
    $l = $player["lane"];
    switch ($role) {
        case 0:
            if ($l != "MIDDLE" && $r == "SOLO")
                return true;
        case 1:
            if ($l == "JUNGLE")
                return true;
        case 2:
            if ($l == "MIDDLE")
                return true;
        case 3:
            if ($r == "DUO_CARRY")
                return true;
        case 4:
            if ($r == "DUO_SUPPORT")
                return true;
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
function getKillParticipation($player, $team) {
    $teamkills = 0;
    $participation;
    foreach ($team as $player) {
        $teamkills += $player["kills"];
    }
    $participation = ($player["kills"] + $player["assists"]) / max(1, $teamkills);
    return number_format($participation*100, 0);
}

?>

    <div class="row">
        <div class="overview__box">
            <div class="overview overview__red">
                <dl class="overview__stats">
                    <dd>KDA: <?php echo getKDA($redplayer); ?></dd>
                    <dd>Kill Participation: <?php echo getKillParticipation($redplayer, $redplayers); ?>%</dd>
                    <dd>Wards Placed: <?php echo $redplayer["wardsPlaced"]; ?></dd>
                </dl>
                <h2 class="overview__title"><img src="<?php echo $redlogo; ?>" alt="" /><?php echo $redplayer["name"]; ?></h2>
            </div>
            <div class="overview__container">
                <div class="overview overview__blue">
                    <h2 class="overview__title"><img src="<?php echo $bluelogo; ?>" alt="" /><?php echo $blueplayer["name"]; ?></h2>
                    <dl class="overview__stats">
                        <dd>KDA: <?php echo getKDA($blueplayer); ?></dd>
                        <dd>Kill Participation: <?php echo getKillParticipation($blueplayer, $blueplayers); ?>%</dd>
                        <dd>Wards Placed: <?php echo $blueplayer["wardsPlaced"]; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row main-logos">
        <div class="medium-4 columns">
            <img src="img/logo/2016/opl/av.png" alt="" />
        </div>
        <div class="medium-4 columns">
            <img src="img/logo/2016/opl/chf.png" alt="" />
        </div>
        <div class="medium-4 columns">
            <img src="img/logo/2016/opl/dw.png" alt="" />
        </div>
        <div class="medium-4 columns">
            <img src="img/logo/2016/opl/hln.png" alt="" />
        </div>
        <div class="medium-4 columns">
            <img src="img/logo/2016/opl/inf.png" alt="" />
        </div>
        <div class="medium-4 columns">
            <img src="img/logo/2016/opl/lgc.png" alt="" />
        </div>
    </div>

<?php require_once("template/footer.php");
