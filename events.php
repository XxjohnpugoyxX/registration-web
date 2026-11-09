<?php
require 'db.php';

$blank = ['evCode' => '', 'evName' => '', 'evDate' => '', 'evVenue' => '', 'evRFee' => ''];
$f   = $blank;
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    foreach ($f as $k => $_) { $f[$k] = trim($_POST[$k] ?? ''); }

    $exists = function ($code) use ($pdo) {
        $s = $pdo->prepare("SELECT * FROM events WHERE evCode = ?");
        $s->execute([$code]);
        return $s->fetch();
    };

    /* 1. ADD an event */
    if ($action === 'add') {
        if ($f['evName'] === '' || $f['evDate'] === '' || $f['evVenue'] === '' || $f['evRFee'] === '') {
            $msg = "PLEASE FILL IN ALL FIELDS (Event Code is automatic)";
        } else {
            $pdo->prepare("INSERT INTO events (evName, evDate, evVenue, evRFee) VALUES (?,?,?,?)")
                ->execute([$f['evName'], $f['evDate'], $f['evVenue'], (float)$f['evRFee']]);
            $msg = "EVENT SUCCESSFULLY ADDED";
            $f = $blank;
        }
    }

    /* 2 & 3. SEARCH (used before update or delete) */
    elseif ($action === 'search') {
        $row = $f['evCode'] !== '' ? $exists($f['evCode']) : false;
        if ($row) { $f = $row; $msg = "EVENT FOUND - you may now update or delete it"; }
        else      { $f = $blank; $msg = "EVENT NOT FOUND"; }
    }

    /* 2. UPDATE (after search) */
    elseif ($action === 'update') {
        if ($f['evCode'] === '' || !$exists($f['evCode'])) {
            $msg = "SEARCH FOR AN EVENT FIRST";
        } else {
            $pdo->prepare("UPDATE events SET evName=?, evDate=?, evVenue=?, evRFee=? WHERE evCode=?")
                ->execute([$f['evName'], $f['evDate'], $f['evVenue'], (float)$f['evRFee'], $f['evCode']]);
            $msg = "EVENT SUCCESSFULLY UPDATED";
            $f = $blank;
        }
    }

    /* 3. DELETE (after search) */
    elseif ($action === 'delete') {
        if ($f['evCode'] === '' || !$exists($f['evCode'])) {
            $msg = "SEARCH FOR AN EVENT FIRST";
        } else {
            try {
                $pdo->prepare("DELETE FROM events WHERE evCode = ?")->execute([$f['evCode']]);
                $msg = "EVENT SUCCESSFULLY DELETED";
                $f = $blank;
            } catch (PDOException $ex) {
                $msg = "CANNOT DELETE: participants are still registered under this event";
            }
        }
    }
}

/* 4. VIEW all events */
$events = $pdo->query("SELECT * FROM events ORDER BY evCode")->fetchAll();

pageHeader("Events");
?>
<h2>Events Management</h2>

<form method="post">
    <label>Event Code:</label>
    <input type="number" name="evCode" value="<?= e($f['evCode']) ?>" placeholder="needed for search/update/delete"><br>
    <label>Event Name:</label>
    <input type="text" name="evName" value="<?= e($f['evName']) ?>"><br>
    <label>Event Date:</label>
    <input type="date" name="evDate" value="<?= e($f['evDate']) ?>"><br>
    <label>Event Venue:</label>
    <input type="text" name="evVenue" value="<?= e($f['evVenue']) ?>"><br>
    <label>Registration Fee:</label>
    <input type="number" step="0.01" name="evRFee" value="<?= e($f['evRFee']) ?>"><br>

    <button name="action" value="add">ADD</button>
    <button name="action" value="search">SEARCH</button>
    <button name="action" value="update">UPDATE</button>
    <button name="action" value="delete" onclick="return confirm('Delete this event?')">DELETE</button>
</form>

<?php if ($msg): ?><div class="msg"><?= e($msg) ?></div><?php endif; ?>

<table>
    <tr><th>Code</th><th>Name</th><th>Date</th><th>Venue</th><th>Reg. Fee</th></tr>
    <?php foreach ($events as $ev): ?>
    <tr>
        <td><?= e($ev['evCode']) ?></td>
        <td><?= e($ev['evName']) ?></td>
        <td><?= e($ev['evDate']) ?></td>
        <td><?= e($ev['evVenue']) ?></td>
        <td><?= number_format($ev['evRFee'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php pageFooter(); ?>
