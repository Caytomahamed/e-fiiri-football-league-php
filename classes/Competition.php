<?php

require_once 'Database.php';

class Competition extends Database
{
    public function getAllCompetitions()
    {
        $sql = "SELECT COMPETITION_ID, NAME, TYPE, SEASON, REGION, DIVISION_LEVEL FROM COMPETITIONS ORDER BY COMPETITION_ID ASC";
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            echo "Error: " . mysqli_error($this->conn);
            return [];
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Each competition plus how many fixtures have been scheduled for it -
    // used to show which competitions still need a schedule generated.
    public function getCompetitionFixtureStats()
    {
        $sql = "SELECT COMPETITIONS.COMPETITION_ID, COMPETITIONS.NAME, COMPETITIONS.TYPE,
                       COUNT(fixtures.FIXTURE_ID) as fixtureCount
                FROM COMPETITIONS
                LEFT JOIN fixtures ON fixtures.COMPETITION_ID = COMPETITIONS.COMPETITION_ID
                GROUP BY COMPETITIONS.COMPETITION_ID, COMPETITIONS.NAME, COMPETITIONS.TYPE
                ORDER BY COMPETITIONS.COMPETITION_ID ASC";
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            echo "Error: " . mysqli_error($this->conn);
            return [];
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getCompetitionById($competitionId)
    {
        $sql = "SELECT COMPETITION_ID, NAME, TYPE, SEASON, REGION, DIVISION_LEVEL FROM COMPETITIONS WHERE COMPETITION_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $competitionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);
    }

    public function createCompetition($name, $type, $season, $region, $divisionLevel)
    {
        $sql = "INSERT INTO COMPETITIONS (NAME, TYPE, SEASON, REGION, DIVISION_LEVEL) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        $region = $region !== '' ? $region : null;
        $divisionLevel = $divisionLevel !== '' ? intval($divisionLevel) : null;
        mysqli_stmt_bind_param($stmt, 'ssssi', $name, $type, $season, $region, $divisionLevel);

        try {
            mysqli_stmt_execute($stmt);
            return true;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                return "A competition named \"$name\" already exists.";
            }
            return "Could not create competition: " . $e->getMessage();
        }
    }

    public function updateCompetition($competitionId, $name, $type, $season, $region, $divisionLevel)
    {
        $sql = "UPDATE COMPETITIONS SET NAME = ?, TYPE = ?, SEASON = ?, REGION = ?, DIVISION_LEVEL = ? WHERE COMPETITION_ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        $region = $region !== '' ? $region : null;
        $divisionLevel = $divisionLevel !== '' ? intval($divisionLevel) : null;
        mysqli_stmt_bind_param($stmt, 'ssssii', $name, $type, $season, $region, $divisionLevel, $competitionId);

        try {
            mysqli_stmt_execute($stmt);
            return true;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                return "A competition named \"$name\" already exists.";
            }
            return "Could not update competition: " . $e->getMessage();
        }
    }

    // Deleting a whole competition intentionally means removing its
    // schedule/standings too, unlike deleting a single team - so this
    // cascades through fixtures/standings/competition_teams first.
    public function deleteCompetition($competitionId)
    {
        mysqli_begin_transaction($this->conn);
        try {
            foreach (['fixtures', 'STANDINGS', 'COMPETITION_TEAMS'] as $table) {
                $sql = "DELETE FROM $table WHERE COMPETITION_ID = ?";
                $stmt = mysqli_prepare($this->conn, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $competitionId);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_error($this->conn));
                }
            }

            $stmt = mysqli_prepare($this->conn, "DELETE FROM COMPETITIONS WHERE COMPETITION_ID = ?");
            mysqli_stmt_bind_param($stmt, 'i', $competitionId);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_error($this->conn));
            }

            mysqli_commit($this->conn);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return "Could not delete competition: " . $e->getMessage();
        }
    }

    public function getTeamsInCompetition($competitionId)
    {
        $sql = "SELECT TEAMS.TEAM_ID, TEAMS.TEAM_NAME, TEAMS.HOME_STADIUM, COMPETITION_TEAMS.GROUP_NAME
                FROM COMPETITION_TEAMS
                JOIN TEAMS ON COMPETITION_TEAMS.TEAM_ID = TEAMS.TEAM_ID
                WHERE COMPETITION_TEAMS.COMPETITION_ID = ?
                ORDER BY COMPETITION_TEAMS.GROUP_NAME ASC, TEAMS.TEAM_NAME ASC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $competitionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function addTeamToCompetition($competitionId, $teamId, $groupName)
    {
        $groupName = $groupName !== '' ? $groupName : null;
        $sql = "INSERT INTO COMPETITION_TEAMS (COMPETITION_ID, TEAM_ID, GROUP_NAME) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iis', $competitionId, $teamId, $groupName);

        try {
            mysqli_stmt_execute($stmt);
            return true;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                return "That team is already in this competition.";
            }
            return "Could not add team to competition: " . $e->getMessage();
        }
    }

    public function getDistinctRegions()
    {
        $sql = "SELECT DISTINCT REGION FROM COMPETITIONS WHERE REGION IS NOT NULL ORDER BY REGION ASC";
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            echo "Error: " . mysqli_error($this->conn);
            return [];
        }

        return array_column(mysqli_fetch_all($result, MYSQLI_ASSOC), 'REGION');
    }

    public function getGroupNames($competitionId)
    {
        $sql = "SELECT DISTINCT GROUP_NAME FROM COMPETITION_TEAMS WHERE COMPETITION_ID = ? AND GROUP_NAME IS NOT NULL ORDER BY GROUP_NAME ASC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $competitionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return array_column(mysqli_fetch_all($result, MYSQLI_ASSOC), 'GROUP_NAME');
    }
}
