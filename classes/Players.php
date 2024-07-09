<?php

// Autoloader
$autoloaderPath = __DIR__ . "/../AutoLoader.php";
// Include schema.php
include_once $autoloaderPath;

class Players extends Database
{
    public function getPlayers()
    {
        $sql = "SELECT
                    P.FNAME AS FirstName,
                    P.MNAME AS MiddleName,
                    P.LNAME AS LastName,
                    P.POSITION AS PlayerPosition,
                    T.TEAM_NAME AS ClubName,
                    P.NATIONALITY AS Nationality
                FROM
                    PLAYER P
                JOIN
                    TEAMS t ON P.TEAM_ID = T.TEAM_ID;";

        $players = mysqli_query($this->conn, $sql);

        // Check for errors
        if (!$players) {
            echo "Error: " . mysqli_error($this->conn);
        }

        // Fetch data as associative array
        $playerList = mysqli_fetch_all($players, MYSQLI_ASSOC);

        return $playerList;
    }

    public function addPlayer($fname, $mname, $lname, $dob, $position, $weight, $height, $nationality, $kitNumber, $teamId)
    {

        // SQL query and prepared statement
        $sql = "INSERT INTO PLAYER (FNAME, MNAME, LNAME, DOB, POSITION, WEIGHT, HEIGHT, NATIONALITY, KIT_NUMBER, TEAM_ID)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);

        // Bind parameters
        mysqli_stmt_bind_param($stmt, 'sssssiisii', $fname, $mname, $lname, $dob, $position, $weight, $height, $nationality, $kitNumber, $teamId);

        if (mysqli_stmt_execute($stmt)) {
            return true;
        } else {
            echo "Error: " . mysqli_error($this->conn);
            return false;
        }
    }
}
