<!DOCTYPE HTML>
<html lang="en_US">

<body>

    <?php
require_once 'classes/Matches.php';
    error_reporting(E_ERROR | E_PARSE);

    // Create instances of classes
    $matches = new Matches();

    // Get fixtures
    $fixtures = $matches->getFixtures();
    // var_dump($fixtures);

    // Close connections
    $matches->closeConnection();

    $groupedByDate = [];
    foreach ($fixtures as $match) {
        $date = $match['MATCH_DATE'];
        $groupedByDate[$date][] = $match;

    }
    // var_dump($groupedByDate);

    $index = 1;
    $week = 1;
    $printWeek = true;


    ?>

    <div>

        <?php
    if (count($fixtures) > 0) {
        foreach ($groupedByDate as $date => $matches) {

            // format date
            $now = new DateTime($date);
            $longDateFormat = "D, F jS, Y";
            $formattedDate = $now->format($longDateFormat);


            if($printWeek) {
                $printWeek = false;
                echo "<p class='matches-dates'> WEEK $week/18 " . "</p>";
            }


            // echo "<p style=' margin:12px 0;margin-left:20px; font-size:15px;'>Matches on $formattedDate  $index: \n</p>";
            echo "<div class='fixeture-box'>";
            foreach ($matches as $row) {
                $onday = new DateTime($row['MATCH_DATE']);
                $longDateFormatDay = "D, F jS, Y";
                $formattedDateDay = $now->format($longDateFormatDay);

                if($index % 5 == 0 && $week < 18) {
                    $week++;
                    $printWeek = true;
                }

                $index++;

                ?>

        <div class="match">
            <div class="teams">
                <div>
                    <img src="images/teams/<?php echo explode(' ', $row['homeTeam'])[0]; ?>.svg"
                        alt="team logo">
                    <h3><?php echo $row['homeTeam']; ?>
                    </h3>
                </div>
                <div>
                    <img src="images/teams/<?php echo explode(' ', $row['awayTeam'])[0]; ?>.svg"
                        alt="team logo">
                    <h3><?php echo $row['awayTeam']; ?>
                    </h3>
                </div>
            </div>
            <div class="scores">
                <p><?php echo $row["HOME_SCORE"] ?>
                </p>
                <p><?php echo $row["AWAY_SCORE"] ?>
                </p>
            </div>
            <div class="match-date">
                <p><?php echo $formattedDateDay ?></p>
                <p class="hour">4:00 PM</p>
            </div>

            <div class="match-staduim">
                <img src="images/Stadium.svg" alt="stadium">
                <p><?php if ($_SESSION['admin_logged_in'] && $_SESSION['admin_logged_in'] === true) {
                    echo $row["FIXTURE_ID"] . " ";
                }
                echo $row['VENUE'];?></p>
            </div>
        </div>

        <?php
            }
            echo "</div>";
        }
    } else {
        echo "No matches found.";
    }
    ?>
    </div>
</body>

</html>