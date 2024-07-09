<!DOCTYPE HTML>
<html lang="en_US">

<body>
  <?php
require_once 'classes/StandingTable.php';
  // Create instances of classes
  $standingTable = new StandingTable();

  // Get standing table
  $standing = $standingTable->getStandingTable();

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

  // Sort the league table
  sortByPoints($standing);
  // var_dump($standing);

  $standingTable->closeConnection();
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
      </tr>
    </thead>
    <?php
if (count($standing) > 0) {
    $Id = 0;
    foreach ($standing as $team) {
        ++$Id;

        if ($Id === 1) {
            $style = 'style="border-left: 5px solid blue;"';
        } elseif ($Id === 9 || $Id === 10) {
            $style = 'style="border-left: 5px solid orange;"';
        } else {
            $style = '';
        }

        ?>
    <tbody>
      <!-- Populate with actual standings data dynamically -->
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
            <td>' . $team["GOALS_FOR"] . '</td>
            <td>' . $team["GOALS_AGAINST"] . '</td>
            <td>' . intval($team["GOALS_FOR"]) - intval($team["GOALS_AGAINST"]) . '</td>
            <td>' . $team["POINTS"] . '</td>

          </tr>';
        ?>

      <!-- More teams as needed -->
    </tbody>
    <?php
    }
} else {
    echo "No Standing table found.";
}
  ?>
  </table>
</body>

</html>