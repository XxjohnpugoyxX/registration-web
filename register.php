<?php
require 'db.php';

$msg = "";

/* All participants with their event fee, for the dropdown */
$participants = $pdo->query(
    "SELECT p.partID, p.partFName, p.partLName, p.partDRate, e.evName, e.evRFee
     FROM participants p JOIN events e ON p.evCode = e.evCode
     ORDER BY p.partLName, p.partFName"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partID  = $_POST['partID']  ?? '';
    $regDate = $_POST['regDate'] ?? '';
    $pmode   = $_POST['regPMode'] ?? '';

    if ($partID === '' || $regDate === '' || !in_array($pmode, ['Card', 'Cash'])) {
        $msg = "PLEASE SELECT A PARTICIPANT, DATE AND PAYMENT MODE";
    } else {
        // Already registered?
        $chk = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE partID = ?");
        $chk->execute([$partID]);

        if ($chk->fetchColumn() > 0) {
            $msg = "PARTICIPANT IS ALREADY REGISTERED";
        } else {
            // 2. COMPUTE fee = evRFee - partDRate (computed in the database query)
            $s = $pdo->prepare(
                "SELECT e.evRFee, p.partDRate FROM participants p
                 JOIN events e ON p.evCode = e.evCode WHERE p.partID = ?"
            );
            $s->execute([$partID]);
            $row = $s->fetch();
            $feePaid = max(0, $row['evRFee'] - $row['partDRate']);

            // 1. REGISTER the participant
            $pdo->prepare("INSERT INTO registration (partID, regDate, regFPaid, regPMode) VALUES (?,?,?,?)")
                ->execute([$partID, $regDate, $feePaid, $pmode]);

            $msg = "REGISTRATION SUCCESSFUL - Fee Paid: " . number_format($feePaid, 2);
        }
    }
}

/* 3. VIEW registration transactions, latest to oldest */
$regs = $pdo->query(
    "SELECT r.regCode, r.regDate, r.regFPaid, r.regPMode,
            CONCAT(p.partFName, ' ', p.partLName) AS fullname,
            e.evName, e.evRFee, p.partDRate
     FROM registration r
     JOIN participants p ON r.partID = p.partID
     JOIN events e ON p.evCode = e.evCode
     ORDER BY r.regDate DESC, r.regCode DESC"
)->fetchAll();

pageHeader("Registration");
?>
<h2>Participant Registration</h2>

<form method="post">
    <label>Participant:</label>
    <select name="partID" id="partID" onchange="showFee()">
        <option value="">-- select participant --</option>
        <?php foreach ($participants as $p): ?>
            <option value="<?= e($p['partID']) ?>"
                    data-fee="<?= e($p['evRFee']) ?>" data-disc="<?= e($p['partDRate']) ?>">
                <?= e($p['partLName'] . ', ' . $p['partFName'] . ' (' . $p['evName'] . ')') ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Registration Date:</label>
    <input type="date" name="regDate" value="<?= date('Y-m-d') ?>"><br>

    <label>Payment Mode:</label>
    <select name="regPMode">
        <option value="Cash">Cash</option>
        <option value="Card">Card</option>
    </select><br>

    <label>Fee to Pay:</label>
    <input type="text" id="feeBox" readonly placeholder="Event Fee - Discount"><br>

    <button type="submit">REGISTER</button>
</form>

<?php if ($msg): ?><div class="msg"><?= e($msg) ?></div><?php endif; ?>

<h3>Registration Transactions (latest to oldest)</h3>
<table>
    <tr><th>Reg Code</th><th>Date</th><th>Participant</th><th>Event</th><th>Event Fee</th>
        <th>Discount</th><th>Fee Paid</th><th>Mode</th></tr>
    <?php foreach ($regs as $r): ?>
    <tr>
        <td><?= e($r['regCode']) ?></td>
        <td><?= e($r['regDate']) ?></td>
        <td><?= e($r['fullname']) ?></td>
        <td><?= e($r['evName']) ?></td>
        <td><?= number_format($r['evRFee'], 2) ?></td>
        <td><?= number_format($r['partDRate'], 2) ?></td>
        <td><?= number_format($r['regFPaid'], 2) ?></td>
        <td><?= e($r['regPMode']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<script>
// Live preview of the computed fee (the server recomputes it on save)
function showFee() {
    var opt = document.getElementById('partID').selectedOptions[0];
    var box = document.getElementById('feeBox');
    if (!opt || !opt.dataset.fee) { box.value = ''; return; }
    box.value = Math.max(0, parseFloat(opt.dataset.fee) - parseFloat(opt.dataset.disc)).toFixed(2);
}
</script>
<?php pageFooter(); ?>
