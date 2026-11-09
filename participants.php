<?php
require 'db.php';

$blank = ['partID' => '', 'evCode' => '', 'partFName' => '', 'partLName' => '', 'partDRate' => ''];
$f   = $blank;
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    foreach ($f as $k => $_) { $f[$k] = trim($_POST[$k] ?? ''); }

    $exists = function ($id) use ($pdo) {
        $s = $pdo->prepare("SELECT * FROM participants WHERE partID = ?");
        $s->execute([$id]);
        return $s->fetch();
    };

    /* 1. ADD a participant */
    if ($action === 'add') {
        if ($f['evCode'] === '' || $f['partFName'] === '' || $f['partLName'] === '' || $f['partDRate'] === '') {
            $msg = "PLEASE FILL IN ALL FIELDS (Participant ID is automatic)";
        } else {
            $pdo->prepare("INSERT INTO participants (evCode, partFName, partLName, partDRate) VALUES (?,?,?,?)")
                ->execute([(int)$f['evCode'], $f['partFName'], $f['partLName'], (float)$f['partDRate']]);
            $msg = "PARTICIPANT SUCCESSFULLY ADDED";
            $f = $blank;
        }
    }

    /* 2 & 3. SEARCH */
    elseif ($action === 'search') {
        $row = $f['partID'] !== '' ? $exists($f['partID']) : false;
        if ($row) { $f = $row; $msg = "PARTICIPANT FOUND - you may now update or delete"; }
        else      { $f = $blank; $msg = "PARTICIPANT NOT FOUND"; }
    }

    /* 2. UPDATE */
    elseif ($action === 'update') {
        if ($f['partID'] === '' || !$exists($f['partID'])) {
            $msg = "SEARCH FOR A PARTICIPANT FIRST";
        } else {
            $pdo->prepare("UPDATE participants SET evCode=?, partFName=?, partLName=?, partDRate=? WHERE partID=?")
                ->execute([(int)$f['evCode'], $f['partFName'], $f['partLName'], (float)$f['partDRate'], $f['partID']]);
            $msg = "PARTICIPANT SUCCESSFULLY UPDATED";
            $f = $blank;
        }
    }

    /* 3. DELETE */
    elseif ($action === 'delete') {
        if ($f['partID'] === '' || !$exists($f['partID'])) {
            $msg = "SEARCH FOR A PARTICIPANT FIRST";
        } else {
            try {
                $pdo->prepare("DELETE FROM participants WHERE partID = ?")->execute([$f['partID']]);
                $msg = "PARTICIPANT SUCCESSFULLY DELETED";
                $f = $blank;
            } catch (PDOException $ex) {
                $msg = "CANNOT DELETE: this participant already has a registration record";
            }
        }
    }
}

/* Data for dropdown and list */
$events = $pdo->query("SELECT evCode, evName FROM events ORDER BY evName")->fetchAll();
$participants = $pdo->query(
    "SELECT p.*, e.evName FROM participants p JOIN events e ON p.evCode = e.evCode ORDER BY p.partID"
)->fetchAll();

pageHeader("Participants");
?>
<h2>Participants Management</h2>

<form method="post">
    <label>Participant ID:</label>
    <input type="number" name="partID" value="<?= e($f['partID']) ?>" placeholder="needed for search/update/delete"><br>
    <label>Event:</label>
    <select name="evCode">
        <option value="">-- select event --</option>
        <?php foreach ($events as $ev): ?>
            <option value="<?= e($ev['evCode']) ?>" <?= $f['evCode'] == $ev['evCode'] ? 'selected' : '' ?>>
                <?= e($ev['evName']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <label>First Name:</label>
    <input type="text" name="partFName" value="<?= e($f['partFName']) ?>"><br>
    <label>Last Name:</label>
    <input type="text" name="partLName" value="<?= e($f['partLName']) ?>"><br>
    <label>Discount Rate:</label>
    <input type="number" step="0.01" name="partDRate" value="<?= e($f['partDRate']) ?>"><br>

    <button name="action" value="add">ADD</button>
    <button name="action" value="search">SEARCH</button>
    <button name="action" value="update">UPDATE</button>
    <button name="action" value="delete" onclick="return confirm('Delete this participant?')">DELETE</button>
</form>

<?php if ($msg): ?><div class="msg"><?= e($msg) ?></div><?php endif; ?>

<table>
    <tr><th>ID</th><th>Event</th><th>First Name</th><th>Last Name</th><th>Discount Rate</th></tr>
    <?php foreach ($participants as $p): ?>
    <tr>
        <td><?= e($p['partID']) ?></td>
        <td><?= e($p['evName']) ?></td>
        <td><?= e($p['partFName']) ?></td>
        <td><?= e($p['partLName']) ?></td>
        <td><?= number_format($p['partDRate'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php pageFooter(); ?>
