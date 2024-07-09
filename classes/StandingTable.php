<?php

require_once 'Database.php';

class StandingTable extends Database
{
    public function getStandingTable()
    {
        $sql = "SELECT
                    LEAGUE.POSITION,
                    TEAMS.TEAM_NAME ,
                    LEAGUE.GOALS_FOR,
                    LEAGUE.GOALS_AGAINST,
                    LEAGUE.WON,
                    LEAGUE.DRAW,
                    LEAGUE.LOST,
                    LEAGUE.POINTS
         FROM LEAGUE JOIN TEAMS ON LEAGUE.TEAM_ID = TEAMS.TEAM_ID ORDER BY POSITION ASC";
        $teams = mysqli_query($this->conn, $sql);

        // Check for errors
        if (!$teams) {
            echo "Error: " . mysqli_error($this->conn);
        }

        // Fetch data as associative array
        $standingTable = mysqli_fetch_all($teams, MYSQLI_ASSOC);

        return $standingTable;
    }

    public function resetLeagueTable($teamIds)
    {
        // Start a transaction
        mysqli_begin_transaction($this->conn);
        try {
            // Step 1: Delete all existing records from the LEAGUE table
            $sqlDelete = "TRUNCATE TABLE LEAGUE";
            if (!mysqli_query($this->conn, $sqlDelete)) {
                throw new Exception("Error deleting records: " . mysqli_error($this->conn));
            }

            // Step 2: Prepare the INSERT statement
            $sqlInsert = "INSERT INTO LEAGUE (TEAM_ID) VALUES ";
            $values = [];
            $placeholders = [];
            foreach ($teamIds as $teamId) {
                $placeholders[] = "(?)";
                $values[] = $teamId;
            }
            $sqlInsert .= implode(", ", $placeholders);

            $stmt = mysqli_prepare($this->conn, $sqlInsert);

            // Bind parameters
            $types = str_repeat('i', count($values)); // All integers
            mysqli_stmt_bind_param($stmt, $types, ...$values);

            // Execute the INSERT statement
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error inserting records: " . mysqli_error($this->conn));
            }

            // Commit the transaction
            mysqli_commit($this->conn);

        } catch (Exception $e) {
            // Rollback the transaction on error
            mysqli_rollback($this->conn);
            echo "Error: " . $e->getMessage();
        }
    }

    public function getTeams()
    {
        $sql = "SELECT TEAM_ID, TEAM_NAME, HOME_STADIUM FROM TEAMS";
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            die("Query failed: " . mysqli_error($this->conn));
        }

        $teams = mysqli_fetch_all($result, MYSQLI_ASSOC);
        return $teams;
    }
}
