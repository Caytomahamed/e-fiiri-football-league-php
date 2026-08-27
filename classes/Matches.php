<?php
// Autoloader
$autoloaderPath = __DIR__ . "/../AutoLoader.php";
// Include schema.php
include_once $autoloaderPath;

class Matches extends Database
{
    // All fixtures for a competition, regardless of group (group-stage + knockout together).
    public function getAllFixtures($competitionId)
    {
        $sql = "SELECT fixtures.FIXTURE_ID,
                       fixtures.GROUP_NAME,
                       fixtures.ROUND_NAME,
                       fixtures.ROUND_ORDER,
                       lgHome.TEAM_NAME as homeTeam,
                       lgAway.TEAM_NAME as awayTeam,
                       fixtures.VENUE,
                       fixtures.MATCH_DATE,
                       fixtures.HOME_SCORE,
                       fixtures.AWAY_SCORE,
                       fixtures.HOME_PENALTIES,
                       fixtures.AWAY_PENALTIES,
                       winnerTeam.TEAM_NAME as winnerTeam,
                       fixtures.NOTE
                FROM fixtures
                JOIN TEAMS as lgHome ON fixtures.HOME_TEAM_ID = lgHome.TEAM_ID
                JOIN TEAMS as lgAway ON fixtures.AWAY_TEAM_ID = lgAway.TEAM_ID
                LEFT JOIN TEAMS as winnerTeam ON fixtures.WINNER_TEAM_ID = winnerTeam.TEAM_ID
                WHERE fixtures.COMPETITION_ID = ?
                ORDER BY fixtures.ROUND_ORDER ASC, fixtures.MATCH_DATE ASC";

        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $competitionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            echo "Error: " . mysqli_error($this->conn);
            return [];
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getFixtures($competitionId, $groupName = null)
    {
        $sql = "SELECT fixtures.FIXTURE_ID,
                       fixtures.GROUP_NAME,
                       fixtures.ROUND_NAME,
                       fixtures.ROUND_ORDER,
                       lgHome.TEAM_NAME as homeTeam,
                       lgAway.TEAM_NAME as awayTeam,
                       fixtures.VENUE,
                       fixtures.MATCH_DATE,
                       fixtures.HOME_SCORE,
                       fixtures.AWAY_SCORE,
                       fixtures.HOME_PENALTIES,
                       fixtures.AWAY_PENALTIES,
                       winnerTeam.TEAM_NAME as winnerTeam,
                       fixtures.NOTE
                FROM fixtures
                JOIN TEAMS as lgHome ON fixtures.HOME_TEAM_ID = lgHome.TEAM_ID
                JOIN TEAMS as lgAway ON fixtures.AWAY_TEAM_ID = lgAway.TEAM_ID
                LEFT JOIN TEAMS as winnerTeam ON fixtures.WINNER_TEAM_ID = winnerTeam.TEAM_ID
                WHERE fixtures.COMPETITION_ID = ?"
            . ($groupName !== null ? " AND fixtures.GROUP_NAME = ?" : "")
            . " ORDER BY fixtures.ROUND_ORDER ASC, fixtures.MATCH_DATE ASC";

        $stmt = mysqli_prepare($this->conn, $sql);
        if ($groupName !== null) {
            mysqli_stmt_bind_param($stmt, 'is', $competitionId, $groupName);
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $competitionId);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            echo "Error: " . mysqli_error($this->conn);
            return [];
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Flat list of every fixture across every competition, for admin dropdowns.
    public function getFixturesForAdminPicker()
    {
        $sql = "SELECT fixtures.FIXTURE_ID, lgHome.TEAM_NAME as homeTeam, lgAway.TEAM_NAME as awayTeam,
                       fixtures.MATCH_DATE, fixtures.HOME_SCORE, fixtures.AWAY_SCORE,
                       fixtures.ROUND_NAME, fixtures.GROUP_NAME, COMPETITIONS.NAME as competitionName
                FROM fixtures
                JOIN TEAMS as lgHome ON fixtures.HOME_TEAM_ID = lgHome.TEAM_ID
                JOIN TEAMS as lgAway ON fixtures.AWAY_TEAM_ID = lgAway.TEAM_ID
                JOIN COMPETITIONS ON fixtures.COMPETITION_ID = COMPETITIONS.COMPETITION_ID
                ORDER BY fixtures.MATCH_DATE DESC, fixtures.FIXTURE_ID DESC";
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            echo "Error: " . mysqli_error($this->conn);
            return [];
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function updateScore($fixtureId, $homeScore, $awayScore, $homePens = null, $awayPens = null)
    {
        $homePens = ($homePens !== null && $homePens !== '') ? intval($homePens) : null;
        $awayPens = ($awayPens !== null && $awayPens !== '') ? intval($awayPens) : null;

        $sql = "UPDATE fixtures
             SET HOME_SCORE = ?, AWAY_SCORE = ?, HOME_PENALTIES = ?, AWAY_PENALTIES = ?
           WHERE FIXTURE_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iiiii', $homeScore, $awayScore, $homePens, $awayPens, $fixtureId);

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

    public function updateTeamStats($teamId, $competitionId, $groupName, $goalsFor, $goalsAgainst)
    {
        // Fetch current stats, scoped to this competition (+ group)
        $sql = "SELECT WON, LOST, DRAW, GOALS_FOR, GOALS_AGAINST, POINTS
                FROM STANDINGS
                WHERE TEAM_ID = ? AND COMPETITION_ID = ?"
            . ($groupName !== null ? " AND GROUP_NAME = ?" : " AND GROUP_NAME IS NULL");
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($groupName !== null) {
            mysqli_stmt_bind_param($stmt, 'iis', $teamId, $competitionId, $groupName);
        } else {
            mysqli_stmt_bind_param($stmt, 'ii', $teamId, $competitionId);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $team = mysqli_fetch_assoc($result);

        if (!$team) {
            echo "Error: no STANDINGS row for this team in this competition/group. Use 'Initialize Standings' first.";
            return;
        }

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
        $goalsFor += intval($team['GOALS_FOR']);
        $goalsAgainst += intval($team['GOALS_AGAINST']);

        // Update the standings with the new stats
        $sql = "UPDATE STANDINGS
                SET WON = ?, LOST = ?, DRAW = ?, GOALS_FOR = ?, GOALS_AGAINST = ?, POINTS = ?
                WHERE TEAM_ID = ? AND COMPETITION_ID = ?"
            . ($groupName !== null ? " AND GROUP_NAME = ?" : " AND GROUP_NAME IS NULL");
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($groupName !== null) {
            mysqli_stmt_bind_param($stmt, 'iiiiiiiis', $won, $lost, $draw, $goalsFor, $goalsAgainst, $points, $teamId, $competitionId, $groupName);
        } else {
            mysqli_stmt_bind_param($stmt, 'iiiiiiii', $won, $lost, $draw, $goalsFor, $goalsAgainst, $points, $teamId, $competitionId);
        }

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
