<?php
require 'db.php';

$events  = $pdo->query("SELECT evCode, evName FROM events ORDER BY evName")->fetchAll();
$evCode  = $_GET['evCode'] ?? '';
$event   = null;
$rows    = [];
$count   = 0;
$sumPaid = 0;
$discount = 0;

if ($evCode !== '') {
    // The selected event (gives us the header and evRFee)
    $s = $pdo->prepare("SELECT * FROM events WHERE evCode = ?");
    $s->execute([$evCode]);
    $event = $s->fetch();

    if ($event) {
        // 2. Result rows: participant full name, registration date, fee paid
        $s = $pdo->prepare(
            "SELECT CONCAT(p.partFName, ' ', p.partLName) AS fullname, r.regDate, r.regFPaid
             FROM registration r
             JOIN participants p ON r.partID = p.partID
             WHERE p.evCode = ?
             ORDER BY r.regDate DESC, r.regCode DESC"
        );
        $s->execute([$evCode]);
        $rows = $s->fetchAll();

        // 3. Aggregate functions: COUNT() and SUM()
        $s = $pdo->prepare(
            "SELECT COUNT(r.regCode) AS cnt, COALESCE(SUM(r.regFPaid), 0) AS total
             FROM registration r
             JOIN participants p ON r.partID = p.partID
             WHERE p.evCode = ?"
        );
        $s->execute([$evCode]);
        $agg = $s->fetch();

        $count    = (int)$agg['cnt'];                       // a
        $sumPaid  = (float)$agg['total'];                   // b
        $discount = ($count * $event['evRFee']) - $sumPaid; // c = (a * evRFee) - b
    }
}

pageHeader("Monitoring");
?>
<h2>Events Registration Monitoring</h2>

<!-- 1. Filter: Event's Name -->
<form method="get">
    <label>Event's Name:</label>
    <select name="evCode">
        <option value="">-- select event --</option>
        <?php foreach ($events as $ev): ?>
            <option value="<?= e($ev['evCode']) ?>" <?= $evCode == $ev['evCode'] ? 'selected' : '' ?>>
                <?= e($ev['evName']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">FILTER</button>
</form>

<?php if ($event): ?>
    <h3>Event: <?= e($event['evName']) ?></h3>   <!-- header [h] -->

    <table>
        <tr><th>Participant's Full Name</th><th>Registration Date</th><th>Registration Fee Paid</th></tr>
        <?php if (!$rows): ?>
            <tr><td colspan="3">No registration records for this event.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e($r['fullname']) ?></td>
            <td><?= e($r['regDate']) ?></td>
            <td><?= number_format($r['regFPaid'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <table class="summary">
        <tr><td>Number of Records (count)</td><td><?= $count ?></td></tr>
        <tr><td>Total Registration Fees Paid (sum)</td><td><?= number_format($sumPaid, 2) ?></td></tr>
        <tr><td>Total Amount of Discounts [(count × evRFee) − sum]</td><td><?= number_format($discount, 2) ?></td></tr>
    </table>
<?php endif; ?>
<?php pageFooter(); ?>
