<?php

// Autoloader
$autoloaderPath = __DIR__ . "/AutoLoader.php";
// Include schema.php
include_once $autoloaderPath;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the type of the form
    $formType = isset($_POST['form_type']) ? $_POST['form_type'] : '';

    // Function to sanitize and retrieve form data
    function getFormData($field)
    {
        return isset($_POST[$field]) ? htmlspecialchars(trim($_POST[$field])) : '';
    }

    // Handle different form submissions based on form_type
    switch ($formType) {
        case 'score_update':
            $fixtureId = getFormData('fixtureId');
            $homeScore = getFormData('homeScore');
            $awayScore = getFormData('awayScore');

            // Process the Score Update form data
            $matches = new Matches();
            $matches->updateScore($fixtureId, $homeScore, $awayScore);
            $matches->closeConnection();
            break;

        case 'league_update':
            $teamId = getFormData('teamId');
            $goalsFor = getFormData('goalsFor');
            $goalsAgainst = getFormData('goalsAgainst');

            // Process the league update Form data
            $matches = new Matches();
            $matches->updateTeamStats($teamId, $goalsFor, $goalsAgainst);
            $matches->closeConnection();
            break;

        case 'generate_fixture':
            $startDate = getFormData('startDate');
            // Process the generate fixture form data
            $stading = new StandingTable();
            $teamIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            $stading->resetLeagueTable($teamIds);
            $stading->closeConnection();

            // Set a new start date (e.g., from user input or dynamic calculation)
            Config::setStartDate($startDate);

            // Create an instance of MatchSchedule
            $matchSchedule = new MatchSchedule();
            $matchSchedule->deleteExistingMatches(); // Delete existing matches
            $matchSchedule->generateMatches(); // Generate new matches

            echo "<script>
                alert(' League table has been reset and new records inserted successfully and generating fixture🏆!');
            </script>";
            break;

        case 'add_player';
            $fname = getFormData('fname');
            $mname = getFormData('mname');
            $lname = getFormData('lname');
            $dob = date("Y-m-d", strtotime(getFormData('dob')));
            $position = getFormData('position');
            $weight = intval(getFormData('weight'));
            $height = intval(getFormData('height'));
            $nationality = getFormData('nationality');
            $kitNumber = intval(getFormData('kit_number'));
            $teamId = intval(getFormData('team_id'));

            // Process the add player form data
            $players = new Players();
            $players->addPlayer($fname, $mname, $lname, $dob, $position, $weight, $height, $nationality, $kitNumber, $teamId);
            $players->closeConnection();

            echo "<script>
                     alert('Player added successfully🚶‍♂️!');
                    </script>";
            break;

        default:
            echo "Unknown form submission.";
            break;
    }
} else {
    // echo "No form was submitted.";
}
