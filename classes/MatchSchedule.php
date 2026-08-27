<?php

// / Autoloader
$autoloaderPath = __DIR__ . "/../AutoLoader.php";
// Include schema.php
include_once $autoloaderPath;

class MatchSchedule extends Database
{
    private $teams = [];

    public function loadTeamsForCompetition($competitionId)
    {
        $sql = "SELECT TEAMS.TEAM_ID, TEAMS.TEAM_NAME, TEAMS.HOME_STADIUM
                FROM COMPETITION_TEAMS
                JOIN TEAMS ON COMPETITION_TEAMS.TEAM_ID = TEAMS.TEAM_ID
                WHERE COMPETITION_TEAMS.COMPETITION_ID = ? AND COMPETITION_TEAMS.GROUP_NAME IS NULL";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $competitionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $this->teams = mysqli_fetch_all($result, MYSQLI_ASSOC);

        return $this->teams;
    }

    public function deleteExistingMatches($competitionId)
    {
        $sql = "DELETE FROM fixtures WHERE COMPETITION_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $competitionId);
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error deleting matches: " . mysqli_error($this->conn);
        }
    }

    private function getNextThursdayOrFriday($date)
    {
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);

        if ($dayOfWeek <= 4) {
            // Move to the next Thursday
            $timestamp = strtotime('next Thursday', $timestamp);
        } elseif ($dayOfWeek == 5) {
            // Move to the next Friday
            $timestamp = strtotime('next Friday', $timestamp);
        } elseif ($dayOfWeek == 6 || $dayOfWeek == 7) {
            // Move to the next Thursday if it's Saturday or Sunday
            $timestamp = strtotime('next Thursday', $timestamp);
        }

        return date('Y-m-d', $timestamp);
    }

    private function getNextThursday($date)
    {
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);

        if ($dayOfWeek <= 4) {
            // Move to the next Thursday
            $timestamp = strtotime('next Thursday', $timestamp);
        } else {
            // Move to the next Thursday if it's Friday, Saturday or Sunday
            $timestamp = strtotime('Thursday next week', $timestamp);
        }

        return date('Y-m-d', $timestamp);
    }

    private function getNextFriday($date)
    {
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);

        if ($dayOfWeek <= 5) {
            // Move to the next Friday
            $timestamp = strtotime('next Friday', $timestamp);
        } else {
            // Move to the next Friday if it's Saturday or Sunday
            $timestamp = strtotime('Friday next week', $timestamp);
        }

        return date('Y-m-d', $timestamp);
    }

    public function generateMatches($competitionId)
    {
        if (empty($this->teams)) {
            $this->loadTeamsForCompetition($competitionId);
        }

        if (count($this->teams) < 2) {
            echo "Error: competition needs at least 2 teams (assigned via 'Add Team to Competition') before generating a schedule.";
            return;
        }

        $startDate = Config::getStartDate();
        $thursday = $this->getNextThursday($startDate);
        $friday = $this->getNextFriday($startDate);

        $matches = [];
        $teamCount = count($this->teams);
        $teamsIndexed = array_values($this->teams);

        // If there's an odd number of teams, the circle method needs a placeholder "bye" slot
        if ($teamCount % 2 !== 0) {
            $teamsIndexed[] = null;
            $teamCount++;
        }

        for ($i = 0; $i < $teamCount - 1; $i++) {
            for ($j = 0; $j < $teamCount / 2; $j++) {
                $home = ($i + $j) % ($teamCount - 1);
                $away = ($teamCount - 1 - $j + $i) % ($teamCount - 1);

                if ($j == 0) {
                    $away = $teamCount - 1;
                }

                if ($teamsIndexed[$home] === null || $teamsIndexed[$away] === null) {
                    continue;
                }

                $matches[] = [$teamsIndexed[$home]['TEAM_ID'], $teamsIndexed[$away]['TEAM_ID']];
                $matches[] = [$teamsIndexed[$away]['TEAM_ID'], $teamsIndexed[$home]['TEAM_ID']];
            }
        }

        // Shuffle to ensure we don't always have home or away for each team
        shuffle($matches);

        $week = 1;
        while (!empty($matches)) {
            $teamsPlayedThisWeek = [];

            // Schedule matches for Thursday
            for ($i = 0; $i < 2; $i++) {
                if (empty($matches)) {
                    break;
                }

                $homeAway = $this->getNextAvailableMatch($matches, $teamsPlayedThisWeek);
                if ($homeAway === null) {
                    break;
                }

                $venue = $this->getStadium($homeAway[0]);
                $this->insertMatch($competitionId, $homeAway[0], $homeAway[1], $thursday, $venue);

                $teamsPlayedThisWeek[] = $homeAway[0];
                $teamsPlayedThisWeek[] = $homeAway[1];
            }

            // Schedule matches for Friday
            for ($i = 0; $i < 2; $i++) {
                if (empty($matches)) {
                    break;
                }

                $homeAway = $this->getNextAvailableMatch($matches, $teamsPlayedThisWeek);
                if ($homeAway === null) {
                    break;
                }

                $venue = $this->getStadium($homeAway[0]);
                $this->insertMatch($competitionId, $homeAway[0], $homeAway[1], $friday, $venue);

                $teamsPlayedThisWeek[] = $homeAway[0];
                $teamsPlayedThisWeek[] = $homeAway[1];
            }

            // Move to next week
            $thursday = $this->getNextThursday($thursday);
            $friday = $this->getNextFriday($friday);
            $week++;
        }
    }

    // One-off group-stage/knockout tie, entered manually by an admin.
    public function createSingleFixture($competitionId, $groupName, $roundName, $roundOrder, $homeTeamId, $awayTeamId, $date, $venue)
    {
        $groupName = $groupName !== '' ? $groupName : null;
        $sql = "INSERT INTO fixtures (COMPETITION_ID, GROUP_NAME, ROUND_NAME, ROUND_ORDER, HOME_TEAM_ID, AWAY_TEAM_ID, MATCH_DATE, VENUE)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'issiiiss', $competitionId, $groupName, $roundName, $roundOrder, $homeTeamId, $awayTeamId, $date, $venue);

        if ($stmt->execute()) {
            return true;
        }

        echo "Error inserting fixture: " . mysqli_error($this->conn);
        return false;
    }

    private function getNextAvailableMatch(&$matches, $teamsPlayedThisWeek)
    {
        foreach ($matches as $key => $match) {
            if (!in_array($match[0], $teamsPlayedThisWeek) && !in_array($match[1], $teamsPlayedThisWeek)) {
                unset($matches[$key]);
                return $match;
            }
        }
        return null;
    }

    private function getStadium($teamId)
    {
        foreach ($this->teams as $team) {
            if ($team['TEAM_ID'] == $teamId) {
                return $team['HOME_STADIUM'];
            }
        }
        return null;
    }

    private function insertMatch($competitionId, $homeTeam, $awayTeam, $date, $venue)
    {
        $sql = "INSERT INTO fixtures (COMPETITION_ID, HOME_TEAM_ID, AWAY_TEAM_ID, MATCH_DATE, VENUE) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("iisss", $competitionId, $homeTeam, $awayTeam, $date, $venue);

        if (!$stmt->execute()) {
            echo "Error inserting match: " . $stmt->error;
        }

        $stmt->close();
    }

    public function closeDatabase()
    {
        $this->closeConnection();
    }
}
