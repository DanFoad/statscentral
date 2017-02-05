<?php

/** champions.php
 * All champions page
 * @author Dan Foad
 */

// Get header
require_once("template/header.php");

// Import data
$champraw = file_get_contents("/var/www/html/dev/champions.json");
$championdata = json_decode($champraw, true);
$eventraw = file_get_contents("/var/www/html/dev/events.json");
$eventdata = json_decode($eventraw, true);

// Global data for display
$region = getRegion();
$year = getYear();
$split = getSplit();

/** getRegion
 * Get which league user has requested data from
 * @return League name if specified, GLOBAL otherwise
 */
function getRegion() {
    global $championdata;
    if (isset($_GET["league"])) {
        if (in_array(strtoupper($_GET["league"]), array_keys($championdata))) {
            return $_GET["league"];
        }
    } else {
        return "GLOBAL";
    }
}

/** getYear
 * Get which year user has requested data from
 * @return Year if specified, ALL YEARS otherwise
 */
function getYear() {
    if (isset($_GET["year"])) {
        if ($_GET["year"] > date("Y")) {
            return "ALL YEARS";
        } else {
            return $_GET["year"];
        }
    } else {
        return "ALL YEARS";
    }
}

/** getSplit
 * Get which split user has requested data from
 * @return Split if specified, ALL SPLITS otherwise
 */
function getSplit() {
    global $region, $year;
    // Can only have specified split if region and year already specified
    if ($year != "ALL YEARS" && $region != "GLOBAL") {
        if (isset($_GET["split"])) {
            $split = str_replace("_", " ", $_GET["split"]);
            return $split;
        }
    }
    return "ALL SPLITS";
}

$data = array();
$numberofgames = 0;

// Get champion data from data resource
foreach ($championdata as $regionname=>$regiondata) { // Step through each region
    if ($region != "GLOBAL" && strtoupper($regionname) != strtoupper($region)) {
        continue;
    }
    foreach ($regiondata as $yearname=>$yeardata) { // Step through each year
        if ($year != "ALL YEARS" && $yearname != $year) {
            continue;
        }
        foreach ($yeardata as $splitname=>$splitdata) { // Step through each split
            if ($split != "ALL SPLITS" && strtoupper($splitname) != strtoupper($split)) {
                continue;
            }
            $numberofgames += $eventdata[$regionname][$yearname][$splitname]["numberofgames"];
            foreach ($splitdata as $champname=>$champdata) { // Step through all champion data in split
                if (!array_key_exists($champname, $data)) {
                    $data[$champname] = $champdata;
                } else {
                    $data[$champname][1] += $champdata[1];
                    $data[$champname][2] += $champdata[2];
                    $data[$champname][3] += $champdata[3];
                }
            }
        }
    }
}

// Set up strings for dropdown and title
$data = json_encode($data);
$split_print = "";
if ($split != "ALL SPLITS") {
    $split_print = " " . $split;
}

$url = "";
if ($region != "GLOBAL") {
    $url .= $region . "/";
}
if ($year != "ALL YEARS") {
    $url .= $year . "/";
}
if ($split != "ALL SPLITS") {
    $url .= str_replace(" ", "_", $split) . "/";
}

?>

<ul id="regionchooser" class="dropdown-content">
    <?php
    $year_temp = "";
    if ($year != "ALL YEARS") $year_temp = $year . "/";
    echo "<a href=\"/champions/" . $year_temp . "\"><li>Global</li></a>";
    foreach (array_keys($championdata) as $region_temp) {
        echo "<a href='/champions/" . $year_temp . $region_temp . "/'><li>" . $region_temp . "</li></a>";
    }
    ?>
</ul>
<ul id="yearchooser" class="dropdown-content">
    <?php
    $region_temp = "";
    if ($region != "GLOBAL") $region_temp = $region;
    echo "<a href=\"/champions/" . $region_temp . "/\"><li>All Years</li></a>";
    echo "<a href=\"/champions/2015/" . $region_temp . "/\"><li>2015</li></a>";
    echo "<a href=\"/champions/2016/" . $region_temp . "/\"><li>2016</li></a>";
    
    ?>
</ul>

<?php
    if ($region != "GLOBAL" && $year != "ALL YEARS") {
?>
<ul id="splitchooser" class="dropdown-content">
    <?php
    echo "<a href=\"/champions/" . $year . "/" . $region . "/\"><li>All Splits</li></a>";
    foreach (array_keys($championdata[$region][$year]) as $split_temp) {
        $split_temp_url = str_replace(" ", "_", $split_temp);
        echo "<a href=\"/champions/" . $year . "/" . $region . "/" . $split_temp_url . "/\"><li>" . $split_temp . "</li></a>";
    }
    ?>
</ul>
<?php
    }
?>
        
        <h1>Champion Stats</h1>
        <h1 style="font-size: 28px; margin-top: -20px"><?php echo $region . " " . $year . $split_print; ?></h1>

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

        <div class="row champ__sorting">
            Sort By:
            <dl class="champ__sorting--options">
                <dd class="champ__sorting--selected" data-method="games">Games Played</dd>
                <dd data-method="champ">Champion Name</dd>
                <dd data-method="pickban">Pick/Ban</dd>
                <dd data-method="wins">Win Rate</dd>
            </dl>
        </div>

        <?php
            if ($data == "null" || $data == "[]") {
                echo "<h1 class=\"row\">No champion data found for " . $region . " " . $year . "</h1>";
            }
        ?>

        <div class="row champ__container"></div>

        <script type="text/javascript">
            // Data from PHP conversion
            var data = <?php echo $data; ?>;
            var url = "<?php echo $url; ?>";
            var numberofgames = <?php echo $numberofgames; ?>

            /** getPickBan
             * Calculate the Pick/Ban percentage of a champion
             * @param champdata The data for a specific champion
             * @return The Pick/Ban percentage of the champion
             */
            function getPickBan(champdata) {
                var pickban = (champdata[1] + champdata[2]) / Math.max(numberofgames, 1) * 100;
                return pickban;
            }

            /** getWinRate
             * Calculate the Win Rate percentage of a champion
             * @param champdata The data for a specific champion
             * @return The Win Rate percentage of the champion
             */
            function getWinRate(champdata) {
                var winrate = (champdata[3] / Math.max(champdata[1], 1)) * 100;
                return winrate;
            }

            /** sortChamps
             * Use custom parameters for sorting array of champions
             * @param method    The method to sort champions by (e.g. alphabetically by name)
             * @param ascending Whether to sort in ascending or descending order
             */
            function sortChamps(method, ascending) {
                var $champs = $(".champ__container").children(".champ");
                $champs.sort(function(a, b) {
                    if (!ascending) {
                        var temp = a;
                        a = b;
                        b = temp;
                    }
                    switch (method) { // No break statements for sub-sorting functionality
                        // Ordered in terms of preferred sorting order (least-significant first)
                        case "pickban":
                            var ab = parseInt(a.getAttribute("data-pickban")),
                                bb = parseInt(b.getAttribute("data-pickban"));

                            if (ab < bb)
                                return 1;
                            if (ab > bb)
                                return -1;
                            // Cascade to next sorting method if equal
                        case "games":
                            var ag = parseInt(a.getAttribute("data-played")),
                                bg = parseInt(b.getAttribute("data-played"));

                            if (ag < bg)
                                return 1;
                            if (ag > bg)
                                return -1;
                            // Cascade to next sorting method if equal
                        case "wins":
                            var aw = parseInt(a.getAttribute("data-wins")),
                                bw = parseInt(b.getAttribute("data-wins"));

                            if (aw < bw)
                                return 1;
                            if (aw > bw)
                                return -1;

                            // Sort by No. Games again if equal
                            var ag = parseInt(a.getAttribute("data-played")),
                                bg = parseInt(b.getAttribute("data-played"));

                            if (ag < bg)
                                return 1;
                            if (ag > bg)
                                return -1;
                            // Cascade to next sorting method if equal
                        case "champ":
                            var an = a.getAttribute("data-name"),
                                bn = b.getAttribute("data-name");

                            if (an > bn)
                                return 1;
                            if (an < bn)
                                return -1;
                            return 0;
                    }
                });
                $champs.detach().appendTo($(".champ__container"));
            }

            // Inject data into template for champion
            for (var champdata in data) {
                if (data.hasOwnProperty(champdata)) {
                    var champ = "<div class=\"champ\" data-id=\"" + data[champdata][0] + "\" data-name=\"" + champdata + "\" data-played=\"" + data[champdata][1] + "\" data-pickban=\"" + getPickBan(data[champdata]) + "\" data-wins=\"" + getWinRate(data[champdata]) + "\">";
                    champ += "<a href='/champion/" + champdata + "/" + url + "'>";
                    champ += "  <div class=\"champ__image-container\">";
                    champ += "<img class=\"champ__image\" src=\"/img/champ/" + data[champdata][0] + ".jpg\" />";
                    champ += "      <span class=\"champ__name\">" + champdata + "</span>";
                    champ += "  </div>";
                    champ += "</a>"
                    champ += "  <p>Games Played - " + data[champdata][1] + "</p>";
                    champ += "  <p>Pick / Ban - " + getPickBan(data[champdata]).toFixed(0) + "%</p>";
                    champ += "  <p>Win Rate - " + getWinRate(data[champdata]).toFixed(0) + "% (" + data[champdata][3] + "w)</p>";
                    champ += "</div>";
                    $(".champ__container").append(champ);
                }
            }

            // Initial sort method is by no. games played
            sortChamps("games", true);

            // Functionality for champion sorting options
            $(".champ__sorting--options dd").on("click", function() {
                var method = $(this).data("method");
                if ($(this).hasClass("champ__sorting--selected")) {
                    sortChamps(method, false);
                    $(this).removeClass("champ__sorting--selected");
                    $(this).addClass("champ__sorting--inverted");
                } else if ($(this).hasClass("champ__sorting--inverted")) {
                    sortChamps(method, true);
                    $(this).removeClass("champ__sorting--inverted");
                    $(this).addClass("champ__sorting--selected");
                } else {
                    $(".champ__sorting--inverted").removeClass("champ__sorting--inverted");
                    $(".champ__sorting--selected").removeClass("champ__sorting--selected");
                    sortChamps(method, true);
                    $(this).addClass("champ__sorting--selected");
                }
            });
        </script>

<?php require_once("template/footer.php");
