<!DOCTYPE HTML>
<html lang="en_US">

<body>
  <?php
require_once 'classes/StandingTable.php';
require_once 'classes/Competition.php';
require_once 'classes/Matches.php';

if (!isset($selectedCompetitionId)) {
    require_once './competitionSelector.php';
}

  function calculatePoints($wins, $draws, $losses)
  {
      $pointsForWin = 3;
      $pointsForDraw = 1;
      $pointsForLoss = 0;

      $totalPoints = ($wins * $pointsForWin) + ($draws * $pointsForDraw) + ($losses * $pointsForLoss);
      return $totalPoints;
  }

  // Function to sort the league table by points (descending order)
  function sortByPoints(&$leagueTable)
  {
      usort($leagueTable, function ($teamA, $teamB) {
          $pointsA = calculatePoints(intval($teamA["WON"]), intval($teamA["DRAW"]), intval($teamA["LOST"]));
          $pointsB = calculatePoints(intval($teamB["WON"]), intval($teamB["DRAW"]), intval($teamB["LOST"]));

          // Sort by points descending, then by team name ascending for tiebreakers
          if ($pointsA === $pointsB) {
              return strcmp($teamA['TEAM_NAME'], $teamB['TEAM_NAME']);
          } else {
              return $pointsB - $pointsA; // Descending order
          }
      });
  }

  function renderStandingsTable($standing)
  {
      ?>
  <table>
    <thead>
      <tr>
        <th>Position</th>
        <th>Team</th>
        <th>Played</th>
        <th>Won</th>
        <th>Drawn</th>
        <th>Lost</th>
        <th>Goal For</th>
        <th>Goals Agains</th>
        <th>Goal Differ</th>
        <th>Points</th>
        <th>Status</th>
      </tr>
    </thead>
    <?php
    if (count($standing) > 0) {
        $Id = 0;
        $total = count($standing);
        foreach ($standing as $team) {
            ++$Id;

            if ($Id === 1) {
                $style = 'style="border-left: 5px solid blue;"';
            } elseif ($Id > $total - 2) {
                $style = 'style="border-left: 5px solid orange;"';
            } else {
                $style = '';
            }

            $hasSplitGoals = $team['GOALS_FOR'] !== null && $team['GOALS_AGAINST'] !== null;
            $goalsFor = $hasSplitGoals ? intval($team['GOALS_FOR']) : null;
            $goalsAgainst = $hasSplitGoals ? intval($team['GOALS_AGAINST']) : null;
            $goalDiff = $hasSplitGoals ? ($goalsFor - $goalsAgainst) : ($team['GOAL_DIFF'] !== null ? intval($team['GOAL_DIFF']) : null);

            ?>
    <tbody>
      <?php
            echo '<tr ' . $style . '>
            <td style="width:50px; text-align:center;">' . $Id . '</td>
           <td >
            <div>
              <img src="images/teams/' . explode(' ', $team["TEAM_NAME"])[0] . '.svg" alt="team logo">
              <p>' . $team["TEAM_NAME"] . '</p>
            </div>
            </td>
            <td >' . intval($team["WON"]) + intval($team["DRAW"]) + intval($team["LOST"]) . '</td>
            <td>' . $team["WON"] . '</td>
            <td>' . $team["DRAW"] . '</td>
            <td>' . $team["LOST"] . '</td>
            <td>' . ($goalsFor === null ? '-' : $goalsFor) . '</td>
            <td>' . ($goalsAgainst === null ? '-' : $goalsAgainst) . '</td>
            <td>' . ($goalDiff === null ? '-' : $goalDiff) . '</td>
            <td>' . $team["POINTS"] . '</td>
            <td>' . ($team["STATUS"] !== null ? htmlspecialchars($team["STATUS"]) : '') . '</td>

          </tr>';
        ?>

    </tbody>
    <?php
    }
} else {
    echo "No Standing table found.";
}
?>
  </table>
      <?php
  }

  function renderKnockoutList($fixturesList)
  {
      if (count($fixturesList) === 0) {
          echo '<p>No knockout fixtures recorded yet.</p>';
          return;
      }

      $groupedByRound = [];
      foreach ($fixturesList as $f) {
          $groupedByRound[$f['ROUND_NAME']][] = $f;
      }

      foreach ($groupedByRound as $roundName => $rows) {
          echo '<h3 class="round-heading">' . htmlspecialchars($roundName) . '</h3>';
          echo '<table><thead><tr><th>Home</th><th>Score</th><th>Away</th><th>Date</th></tr></thead><tbody>';
          foreach ($rows as $row) {
              if ($row['HOME_SCORE'] !== null) {
                  $score = $row['HOME_SCORE'] . ' - ' . $row['AWAY_SCORE'];
                  if ($row['HOME_PENALTIES'] !== null) {
                      $score .= ' <span class="pens">(' . $row['HOME_PENALTIES'] . '-' . $row['AWAY_PENALTIES'] . ' pen)</span>';
                  }
              } elseif ($row['winnerTeam'] !== null) {
                  $score = '<span class="pens">' . htmlspecialchars($row['winnerTeam']) . ' won' . ($row['NOTE'] ? ' (' . htmlspecialchars($row['NOTE']) . ')' : '') . '</span>';
              } else {
                  $score = 'vs';
              }
              $formattedDate = (new DateTime($row['MATCH_DATE']))->format('M jS, Y');
              echo '<tr>
                <td>' . htmlspecialchars($row['homeTeam']) . '</td>
                <td style="text-align:center;">' . $score . '</td>
                <td>' . htmlspecialchars($row['awayTeam']) . '</td>
                <td>' . $formattedDate . '</td>
              </tr>';
          }
          echo '</tbody></table>';
      }
  }

  $standingTable = new StandingTable();
  $competitionClass = new Competition();
  $matchesClass = new Matches();

  $type = $selectedCompetition !== null ? $selectedCompetition['TYPE'] : 'LEAGUE';

  if ($type === 'GROUP_KNOCKOUT') {
      $groups = $competitionClass->getGroupNames($selectedCompetitionId);
      foreach ($groups as $g) {
          $standing = $standingTable->getStandingTable($selectedCompetitionId, $g);
          sortByPoints($standing);
          echo '<h3 class="round-heading">Group ' . htmlspecialchars($g) . '</h3>';
          renderStandingsTable($standing);
      }

      echo '<h2 style="margin-top:30px;">Knockout Stage</h2>';
      $koFixtures = $matchesClass->getFixtures($selectedCompetitionId, null);
      renderKnockoutList($koFixtures);
  } elseif ($type === 'CUP') {
      $koFixtures = $matchesClass->getFixtures($selectedCompetitionId, null);
      renderKnockoutList($koFixtures);
  } else {
      $standing = $standingTable->getStandingTable($selectedCompetitionId, null);
      sortByPoints($standing);
      renderStandingsTable($standing);
  }

  $standingTable->closeConnection();
  $competitionClass->closeConnection();
  $matchesClass->closeConnection();
  ?>
</body>

</html>
