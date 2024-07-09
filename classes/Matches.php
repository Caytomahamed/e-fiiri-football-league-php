<?php
// Autoloader
$autoloaderPath = __DIR__ . "/../AutoLoader.php";
// Include schema.php
include_once $autoloaderPath;

class Matches extends Database
{
    public function getFixtures()
    {
        $sql = "SELECT fixtures.FIXTURE_ID,
                       lgHome.TEAM_NAME as homeTeam,
                       lgAway.TEAM_NAME as awayTeam,
                       fixtures.VENUE,
                       fixtures.MATCH_DATE,
                       fixtures.HOME_SCORE,
                       fixtures.AWAY_SCORE
                FROM fixtures
                JOIN TEAMS as lgHome ON fixtures.HOME_TEAM_ID = lgHome.TEAM_ID
                JOIN TEAMS as lgAway ON fixtures.AWAY_TEAM_ID = lgAway.TEAM_ID
                ORDER BY fixtures.MATCH_DATE ASC";
        $run = mysqli_query($this->conn, $sql);

        // Check for errors
        if (!$run) {
            echo "Error: " . mysqli_error($this->conn);
        }

        // Fetch data as associative array
        $fixtures = mysqli_fetch_all($run, MYSQLI_ASSOC);

        return $fixtures;
    }

    public function updateScore($fixtureId, $homeScore, $awayScore)
    {
        $sql = "UPDATE fixtures
             SET HOME_SCORE = ?, AWAY_SCORE = ?
           WHERE FIXTURE_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iii', $homeScore, $awayScore, $fixtureId);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Score updated successfully!";
            echo "<script>
                alert('Success updating score🥅⚽!');
            </script>";

        } else {
            $message = "Error updating score: " . mysqli_error($this->conn);
        }

        mysqli_stmt_close($stmt);

        return $message;
    }

    public function updateTeamStats($teamId, $goalsFor, $goalsAgainst)
    {
        // Fetch current stats
        $sql = "SELECT WON, LOST, DRAW, GOALS_FOR, GOALS_AGAINST, POINTS
                FROM league
                WHERE TEAM_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $teamId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $team = mysqli_fetch_assoc($result);

        // Update stats based on the match result
        $won = $team['WON'];
        $lost = $team['LOST'];
        $draw = $team['DRAW'];
        $points = $team['POINTS'];

        if ($goalsFor > $goalsAgainst) {
            $won++;
            $points += 3;
        } elseif ($goalsFor < $goalsAgainst) {
            $lost++;
        } else {
            $draw++;
            $points++;
        }

        // update goals
        $goalsFor += $team['GOALS_FOR'];
        $goalsAgainst += $team['GOALS_AGAINST'];


        // Update the league table with the new stats
        $sql = "UPDATE league
                SET WON = ?, LOST = ?, DRAW = ?, GOALS_FOR = ?, GOALS_AGAINST = ?, POINTS = ?
                WHERE TEAM_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iiiiiii', $won, $lost, $draw, $goalsFor, $goalsAgainst, $points, $teamId);

        echo "<script>
                alert('Success updating the league stading🏆!');
            </script>";

        if (!mysqli_stmt_execute($stmt)) {
            echo "Error: " . mysqli_error($this->conn);
            return;
        }
        return;
    }
}
