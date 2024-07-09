<?php

// / Autoloader
$autoloaderPath = __DIR__ . "/../AutoLoader.php";
// Include schema.php
include_once $autoloaderPath;

class MatchSchedule extends Database
{
    private $teams = [
        ['TEAM_ID' => 1, 'TEAM_NAME' => 'Maaliyada FC', 'HOME_STADIUM' => 'Maaliyada Stadium'],
        ['TEAM_ID' => 2, 'TEAM_NAME' => 'Asluubta GFC', 'HOME_STADIUM' => 'Asluubta Stadium'],
        ['TEAM_ID' => 3, 'TEAM_NAME' => 'Dawlaada Hoose Hargeysa FC', 'HOME_STADIUM' => 'DHH Stadium'],
        ['TEAM_ID' => 4, 'TEAM_NAME' => 'Tamarta FC', 'HOME_STADIUM' => 'Tamarta Stadium'],
        ['TEAM_ID' => 5, 'TEAM_NAME' => 'Xidigaha Cirka FC', 'HOME_STADIUM' => 'Cirka Stadium'],
        ['TEAM_ID' => 6, 'TEAM_NAME' => 'Gaashaan FC', 'HOME_STADIUM' => 'Gaashaan Stadium'],
        ['TEAM_ID' => 7, 'TEAM_NAME' => 'Goodir FC', 'HOME_STADIUM' => 'Goodir Stadium'],
        ['TEAM_ID' => 8, 'TEAM_NAME' => 'Caafimadka FC', 'HOME_STADIUM' => 'Caafimadka Stadium'],
        ['TEAM_ID' => 9, 'TEAM_NAME' => 'Waxool FC', 'HOME_STADIUM' => 'Waxool Stadium'],
        ['TEAM_ID' => 10, 'TEAM_NAME' => 'Ganacsiga FC', 'HOME_STADIUM' => 'Ganacsiga Stadium'],
    ];

    private $derbyMatches = [
        ['TEAM_ID_1' => 7, 'TEAM_ID_2' => 2], // Goodir vs Asluubta
        ['TEAM_ID_1' => 7, 'TEAM_ID_2' => 6], // Goodir vs Gaashaan
        ['TEAM_ID_1' => 7, 'TEAM_ID_2' => 3], // Goodir vs Dawlaada Hoose
        ['TEAM_ID_1' => 2, 'TEAM_ID_2' => 6], // Asluubta vs Gaashaan
        ['TEAM_ID_1' => 2, 'TEAM_ID_2' => 3], // Asluubta vs Dawlaada Hoose
        ['TEAM_ID_1' => 6, 'TEAM_ID_2' => 3], // Gaashaan vs Dawlaada Hoose
    ];

    public function deleteExistingMatches()
    {
        $sql = "TRUNCATE TABLE fixtures";
        if ($this->conn->query($sql) === true) {
            // echo "Existing matches deleted successfully.";
        } else {
            echo "Error deleting matches: " . $this->conn->error;
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

    public function generateMatches()
    {
        $startDate = Config::getStartDate();
        $thursday = $this->getNextThursday($startDate);
        $friday = $this->getNextFriday($startDate);

        $matches = [];
        $teamCount = count($this->teams);
        for ($i = 0; $i < $teamCount - 1; $i++) {
            for ($j = 0; $j < $teamCount / 2; $j++) {
                $home = ($i + $j) % ($teamCount - 1);
                $away = ($teamCount - 1 - $j + $i) % ($teamCount - 1);

                if ($j == 0) {
                    $away = $teamCount - 1;
                }

                $matches[] = [$this->teams[$home]['TEAM_ID'], $this->teams[$away]['TEAM_ID']];
                $matches[] = [$this->teams[$away]['TEAM_ID'], $this->teams[$home]['TEAM_ID']];
            }
        }

        // Shuffle to ensure we don't always have home or away for each team
        shuffle($matches);

        $matchCount = 0;
        while (!empty($matches)) {
            // Schedule matches for Thursday
            for ($i = 0; $i < 2; $i++) {
                if (empty($matches)) {
                    break;
                }

                $homeAway = array_shift($matches);
                $venue = $this->getStadium($homeAway[0]);
                $this->insertMatch($homeAway[0], $homeAway[1], $thursday, $venue);

                $matchCount++;
            }

            // Schedule matches for Friday
            for ($i = 0; $i < 3; $i++) {
                if (empty($matches)) {
                    break;
                }

                $homeAway = array_shift($matches);
                $venue = $this->getStadium($homeAway[0]);
                $this->insertMatch($homeAway[0], $homeAway[1], $friday, $venue);

                $matchCount++;
            }

            // Move to next week
            $thursday = $this->getNextThursday($thursday);
            $friday = $this->getNextFriday($friday);
        }
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

    private function insertMatch($homeTeam, $awayTeam, $date, $venue)
    {
        $sql = "INSERT INTO fixtures (HOME_TEAM_ID, AWAY_TEAM_ID, MATCH_DATE, VENUE) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $fixture = $this->teams[$homeTeam - 1]['TEAM_NAME'] . " vs " . $this->teams[$awayTeam - 1]['TEAM_NAME'];

        // Assuming you have variables containing home team ID, away team ID, and venue
        $stmt->bind_param("ssss", $homeTeam, $awayTeam, $date, $venue);

        if ($stmt->execute()) {
            // echo "Match inserted: $fixture on $date at $venue\n";
        } else {
            echo "Error inserting match: " . $stmt->error;
        }

        $stmt->close();
    }

    public function closeDatabase()
    {
        $this->closeConnection();
    }
}
