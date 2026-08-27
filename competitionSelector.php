<?php
require_once __DIR__ . '/classes/Competition.php';

$competitionClass = new Competition();
$allCompetitions = $competitionClass->getAllCompetitions();
$competitionClass->closeConnection();

$selectedCompetitionId = isset($_GET['competition_id']) ? intval($_GET['competition_id']) : null;

if ($selectedCompetitionId === null || $selectedCompetitionId === 0) {
    $selectedCompetitionId = null;
    foreach ($allCompetitions as $c) {
        if ($c['NAME'] === 'E-Fiiri League 2024') {
            $selectedCompetitionId = intval($c['COMPETITION_ID']);
            break;
        }
    }
    if ($selectedCompetitionId === null && count($allCompetitions) > 0) {
        $selectedCompetitionId = intval($allCompetitions[0]['COMPETITION_ID']);
    }
}

$selectedCompetition = null;
foreach ($allCompetitions as $c) {
    if (intval($c['COMPETITION_ID']) === $selectedCompetitionId) {
        $selectedCompetition = $c;
        break;
    }
}
?>
<div class="competition-selector">
    <label for="competition-select">Competition:</label>
    <select id="competition-select" onchange="window.location.href = '?competition_id=' + this.value">
        <?php foreach ($allCompetitions as $c) { ?>
        <option value="<?php echo $c['COMPETITION_ID']; ?>" <?php echo (intval($c['COMPETITION_ID']) === $selectedCompetitionId) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($c['NAME']); ?>
        </option>
        <?php } ?>
    </select>
</div>
