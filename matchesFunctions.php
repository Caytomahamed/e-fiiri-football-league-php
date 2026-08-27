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
            $homePens = getFormData('homePens');
            $awayPens = getFormData('awayPens');

            // Process the Score Update form data
            $matches = new Matches();
            $matches->updateScore($fixtureId, $homeScore, $awayScore, $homePens, $awayPens);
            $matches->closeConnection();
            break;

        case 'league_update':
            $teamId = getFormData('teamId');
            $competitionId = getFormData('competitionId');
            $groupName = getFormData('groupName');
            $goalsFor = getFormData('goalsFor');
            $goalsAgainst = getFormData('goalsAgainst');

            // Process the league update Form data
            $matches = new Matches();
            $matches->updateTeamStats($teamId, $competitionId, $groupName !== '' ? $groupName : null, $goalsFor, $goalsAgainst);
            $matches->closeConnection();
            break;

        case 'generate_fixture':
            $startDate = getFormData('startDate');
            $competitionId = getFormData('competitionId');

            $competition = new Competition();
            $teamsInCompetition = $competition->getTeamsInCompetition($competitionId);
            $competition->closeConnection();

            $teamIdsWithGroups = array_map(function ($t) {
                return [$t['TEAM_ID'], $t['GROUP_NAME']];
            }, $teamsInCompetition);

            $stading = new StandingTable();
            $stading->resetStandings($competitionId, $teamIdsWithGroups);
            $stading->closeConnection();

            // Set a new start date (e.g., from user input or dynamic calculation)
            Config::setStartDate($startDate);

            // Create an instance of MatchSchedule
            $matchSchedule = new MatchSchedule();
            $matchSchedule->deleteExistingMatches($competitionId); // Delete existing matches for this competition
            $matchSchedule->loadTeamsForCompetition($competitionId);
            $matchSchedule->generateMatches($competitionId); // Generate new matches

            echo "<script>
                alert(' League table has been reset and new records inserted successfully and generating fixture🏆!');
            </script>";
            break;

        case 'add_player':
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

        case 'create_competition':
            $name = getFormData('name');
            $type = getFormData('type');
            $season = getFormData('season');
            $region = getFormData('region');
            if ($region === '__other__') {
                $region = getFormData('regionOther');
            }
            $divisionLevel = getFormData('divisionLevel');

            $competition = new Competition();
            $result = $competition->createCompetition($name, $type, $season, $region, $divisionLevel);
            $competition->closeConnection();

            echo "<script>alert(" . json_encode($result === true ? 'Competition created successfully🏆!' : $result) . ");</script>";
            break;

        case 'add_team_to_competition':
            $competitionId = getFormData('competitionId');
            $teamId = getFormData('teamId');
            $groupName = getFormData('groupName');

            $competition = new Competition();
            $result = $competition->addTeamToCompetition($competitionId, $teamId, $groupName);
            $competition->closeConnection();

            echo "<script>alert(" . json_encode($result === true ? 'Team added to competition successfully!' : $result) . ");</script>";
            break;

        case 'init_standings':
            $competitionId = getFormData('competitionId');

            $competition = new Competition();
            $teamsInCompetition = $competition->getTeamsInCompetition($competitionId);
            $competition->closeConnection();

            $teamIdsWithGroups = array_map(function ($t) {
                return [$t['TEAM_ID'], $t['GROUP_NAME']];
            }, $teamsInCompetition);

            $stading = new StandingTable();
            $stading->resetStandings($competitionId, $teamIdsWithGroups);
            $stading->closeConnection();

            echo "<script>
                alert('Standings initialized for this competition!');
            </script>";
            break;

        case 'add_knockout_fixture':
            $competitionId = getFormData('competitionId');
            $groupName = getFormData('groupName');
            $roundName = getFormData('roundName');
            $roundOrder = intval(getFormData('roundOrder'));
            $homeTeamId = getFormData('homeTeamId');
            $awayTeamId = getFormData('awayTeamId');
            $date = getFormData('matchDate');
            $venue = getFormData('venue');

            $matchSchedule = new MatchSchedule();
            $matchSchedule->createSingleFixture($competitionId, $groupName, $roundName, $roundOrder, $homeTeamId, $awayTeamId, $date, $venue);
            $matchSchedule->closeConnection();

            echo "<script>
                alert('Fixture added!');
            </script>";
            break;

        case 'create_team':
            $teamName = getFormData('teamName');
            $homeStadium = getFormData('homeStadium');

            $standingTable = new StandingTable();
            $result = $standingTable->createTeam($teamName, $homeStadium);
            $standingTable->closeConnection();

            echo "<script>alert(" . json_encode($result === true ? 'Team created successfully!' : $result) . ");</script>";
            break;

        case 'update_team':
            $teamId = getFormData('teamId');
            $teamName = getFormData('teamName');
            $homeStadium = getFormData('homeStadium');

            $standingTable = new StandingTable();
            $result = $standingTable->updateTeam($teamId, $teamName, $homeStadium);
            $standingTable->closeConnection();

            echo "<script>alert(" . json_encode($result === true ? 'Team updated successfully!' : $result) . ");</script>";
            break;

        case 'delete_team':
            $teamId = getFormData('teamId');

            $standingTable = new StandingTable();
            $result = $standingTable->deleteTeam($teamId);
            $standingTable->closeConnection();

            echo "<script>alert(" . json_encode($result === true ? 'Team deleted.' : $result) . ");</script>";
            break;

        case 'update_competition':
            $competitionId = getFormData('competitionId');
            $name = getFormData('name');
            $type = getFormData('type');
            $season = getFormData('season');
            $region = getFormData('region');
            if ($region === '__other__') {
                $region = getFormData('regionOther');
            }
            $divisionLevel = getFormData('divisionLevel');

            $competition = new Competition();
            $result = $competition->updateCompetition($competitionId, $name, $type, $season, $region, $divisionLevel);
            $competition->closeConnection();

            echo "<script>alert(" . json_encode($result === true ? 'Competition updated successfully!' : $result) . ");</script>";
            break;

        case 'delete_competition':
            $competitionId = getFormData('competitionId');

            $competition = new Competition();
            $result = $competition->deleteCompetition($competitionId);
            $competition->closeConnection();

            echo "<script>alert(" . json_encode($result === true ? 'Competition deleted.' : $result) . ");</script>";
            break;

        default:
            echo "Unknown form submission.";
            break;
    }
} else {
    // echo "No form was submitted.";
}
