<?php

require_once 'Database.php';

class StandingTable extends Database
{
    public function getStandingTable($competitionId, $groupName = null)
    {
        $sql = "SELECT
                    STANDINGS.STANDING_ID,
                    STANDINGS.GROUP_NAME,
                    TEAMS.TEAM_NAME,
                    STANDINGS.GOALS_FOR,
                    STANDINGS.GOALS_AGAINST,
                    STANDINGS.GOAL_DIFF,
                    STANDINGS.WON,
                    STANDINGS.DRAW,
                    STANDINGS.LOST,
                    STANDINGS.POINTS,
                    STANDINGS.STATUS
                FROM STANDINGS JOIN TEAMS ON STANDINGS.TEAM_ID = TEAMS.TEAM_ID
                WHERE STANDINGS.COMPETITION_ID = ?" . ($groupName !== null ? " AND STANDINGS.GROUP_NAME = ?" : " AND STANDINGS.GROUP_NAME IS NULL")
            . " ORDER BY STANDINGS.POINTS DESC";

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

    // $teamIdsWithGroups: array of [teamId, groupNameOrNull]
    public function resetStandings($competitionId, $teamIdsWithGroups)
    {
        mysqli_begin_transaction($this->conn);
        try {
            $sqlDelete = "DELETE FROM STANDINGS WHERE COMPETITION_ID = ?";
            $stmtDelete = mysqli_prepare($this->conn, $sqlDelete);
            mysqli_stmt_bind_param($stmtDelete, 'i', $competitionId);
            if (!mysqli_stmt_execute($stmtDelete)) {
                throw new Exception("Error deleting records: " . mysqli_error($this->conn));
            }

            $sqlInsert = "INSERT INTO STANDINGS (COMPETITION_ID, TEAM_ID, GROUP_NAME, GOALS_FOR, GOALS_AGAINST) VALUES (?, ?, ?, 0, 0)";
            $stmtInsert = mysqli_prepare($this->conn, $sqlInsert);
            foreach ($teamIdsWithGroups as $entry) {
                [$teamId, $groupName] = $entry;
                mysqli_stmt_bind_param($stmtInsert, 'iis', $competitionId, $teamId, $groupName);
                if (!mysqli_stmt_execute($stmtInsert)) {
                    throw new Exception("Error inserting records: " . mysqli_error($this->conn));
                }
            }

            mysqli_commit($this->conn);
        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            echo "Error: " . $e->getMessage();
        }
    }

    public function getTeams()
    {
        $sql = "SELECT TEAM_ID, TEAM_NAME, HOME_STADIUM FROM TEAMS ORDER BY TEAM_NAME ASC";
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            die("Query failed: " . mysqli_error($this->conn));
        }

        $teams = mysqli_fetch_all($result, MYSQLI_ASSOC);
        return $teams;
    }

    // Returns true on success, or an error message string on failure
    // (e.g. a duplicate team name).
    public function createTeam($name, $stadium)
    {
        $sql = "INSERT INTO TEAMS (TEAM_NAME, HOME_STADIUM) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $name, $stadium);

        try {
            mysqli_stmt_execute($stmt);
            return true;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                return "A team named \"$name\" already exists.";
            }
            return "Could not create team: " . $e->getMessage();
        }
    }

    public function updateTeam($teamId, $name, $stadium)
    {
        $sql = "UPDATE TEAMS SET TEAM_NAME = ?, HOME_STADIUM = ? WHERE TEAM_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $stadium, $teamId);

        try {
            mysqli_stmt_execute($stmt);
            return true;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                return "A team named \"$name\" already exists.";
            }
            return "Could not update team: " . $e->getMessage();
        }
    }

    // Blocks the delete (rather than cascading) if the team has recorded
    // matches, standings, or players - deleting real season history by
    // accident is worse than requiring an explicit cleanup first.
    public function deleteTeam($teamId)
    {
        $sql = "DELETE FROM TEAMS WHERE TEAM_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $teamId);

        try {
            mysqli_stmt_execute($stmt);
            return true;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1451) {
                return "Can't delete this team - it already has matches, standings, or players recorded against it.";
            }
            return "Could not delete team: " . $e->getMessage();
        }
    }
}
