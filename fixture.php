<!DOCTYPE HTML>
<html lang="en_US">

<body>

    <?php
require_once 'classes/Matches.php';
    error_reporting(E_ERROR | E_PARSE);

    if (!isset($selectedCompetitionId)) {
        require_once './competitionSelector.php';
    }

    $isKnockoutStyle = $selectedCompetition !== null && in_array($selectedCompetition['TYPE'], ['GROUP_KNOCKOUT', 'CUP']);

    // Create instances of classes
    $matches = new Matches();

    // Get fixtures (group-stage + knockout together for group/cup competitions)
    $fixtures = $matches->getAllFixtures($selectedCompetitionId);

    // Close connections
    $matches->closeConnection();

    function renderMatchCard($row)
    {
        $onday = new DateTime($row['MATCH_DATE']);
        $formattedDateDay = $onday->format('D, F jS, Y');

        if ($row['HOME_SCORE'] !== null) {
            $homeScoreDisplay = $row['HOME_SCORE'];
            $awayScoreDisplay = $row['AWAY_SCORE'];
        } else {
            $homeScoreDisplay = '-';
            $awayScoreDisplay = '-';
        }
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
                <p><?php echo $homeScoreDisplay ?>
                </p>
                <p><?php echo $awayScoreDisplay ?>
                </p>
            </div>
            <?php if ($row['HOME_PENALTIES'] !== null) { ?>
            <p class="pens"><?php echo $row['HOME_PENALTIES'] . ' - ' . $row['AWAY_PENALTIES']; ?> (pens)</p>
            <?php } elseif ($row['HOME_SCORE'] === null && $row['winnerTeam'] !== null) { ?>
            <p class="pens"><?php echo htmlspecialchars($row['winnerTeam']); ?> won<?php echo $row['NOTE'] ? ' (' . htmlspecialchars($row['NOTE']) . ')' : ''; ?></p>
            <?php } ?>
            <div class="match-date">
                <p><?php echo $formattedDateDay ?></p>
            </div>

            <div class="match-staduim">
                <img src="images/Stadium.svg" alt="stadium">
                <p><?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                    echo $row["FIXTURE_ID"] . " ";
                }
                echo $row['VENUE'];?></p>
            </div>
        </div>
        <?php
    }
    ?>

    <div>

        <?php
    if (count($fixtures) > 0) {
        if ($isKnockoutStyle) {
            $groupedByRound = [];
            foreach ($fixtures as $match) {
                $label = $match['GROUP_NAME'] !== null ? 'Group ' . $match['GROUP_NAME'] : $match['ROUND_NAME'];
                $groupedByRound[$label][] = $match;
            }

            foreach ($groupedByRound as $label => $rows) {
                echo "<p class='matches-dates'>" . htmlspecialchars($label) . "</p>";
                echo "<div class='fixeture-box'>";
                foreach ($rows as $row) {
                    renderMatchCard($row);
                }
                echo "</div>";
            }
        } else {
            $groupedByDate = [];
            foreach ($fixtures as $match) {
                $date = $match['MATCH_DATE'];
                $groupedByDate[$date][] = $match;
            }

            $index = 1;
            $week = 1;
            $printWeek = true;

            foreach ($groupedByDate as $date => $matchesOnDate) {
                if ($printWeek) {
                    $printWeek = false;
                    echo "<p class='matches-dates'> WEEK $week </p>";
                }

                echo "<div class='fixeture-box'>";
                foreach ($matchesOnDate as $row) {
                    if ($index % 5 == 0) {
                        $week++;
                        $printWeek = true;
                    }
                    $index++;

                    renderMatchCard($row);
                }
                echo "</div>";
            }
        }
    } else {
        echo "No matches found.";
    }
    ?>
    </div>
</body>

</html>
