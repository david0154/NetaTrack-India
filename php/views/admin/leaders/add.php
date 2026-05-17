<?php
/**
 * Add / Edit Leader form — states and parties populated from DB seed.
 */
use App\Seed\SeedRunner;

$states  = SeedRunner::getStates();
$parties = SeedRunner::getParties();
$editing = isset($leader);
$title   = $editing ? 'Edit Leader' : 'Add New Leader';
?>

<div style="max-width:720px">
<h2 style="color:#f8fafc;margin-bottom:20px">
    <?= $editing ? '✏️' : '➕' ?> <?= $title ?>
</h2>

<form method="POST" action="<?= url($editing ? 'admin/leaders/update/'.$leader['id'] : 'admin/leaders/store') ?>" enctype="multipart/form-data"
      style="display:flex;flex-direction:column;gap:16px">

    <!-- Name -->
    <div>
        <label style="color:#94a3b8;font-size:.85rem">Full Name *</label><br>
        <input type="text" name="name" required value="<?= e($leader['name'] ?? '') ?>"
               style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px;font-size:.95rem">
    </div>

    <!-- State -->
    <div>
        <label style="color:#94a3b8;font-size:.85rem">State / UT *</label><br>
        <select name="state_id" required
                style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
            <option value="">-- Select State/UT --</option>
            <optgroup label="🏴 States">
            <?php foreach ($states as $s): ?>
                <?php if ($s['type'] !== 'state') continue; ?>
                <option value="<?= $s['id'] ?>"
                    <?= ($leader['state_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                    <?= e($s['name']) ?> (<?= $s['code'] ?>)
                </option>
            <?php endforeach; ?>
            </optgroup>
            <optgroup label="📍 Union Territories">
            <?php foreach ($states as $s): ?>
                <?php if ($s['type'] !== 'ut') continue; ?>
                <option value="<?= $s['id'] ?>"
                    <?= ($leader['state_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                    <?= e($s['name']) ?> (<?= $s['code'] ?>)
                </option>
            <?php endforeach; ?>
            </optgroup>
        </select>
    </div>

    <!-- Party -->
    <div>
        <label style="color:#94a3b8;font-size:.85rem">Party *</label><br>
        <select name="party_id" required
                style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
            <option value="">-- Select Party --</option>
            <?php foreach ($parties as $p): ?>
            <option value="<?= $p['id'] ?>"
                    data-color="<?= e($p['color']) ?>"
                    <?= ($leader['party_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                <?= e($p['name']) ?> (<?= e($p['abbreviation']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Role/Constituency -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
            <label style="color:#94a3b8;font-size:.85rem">Constituency</label><br>
            <input type="text" name="constituency" value="<?= e($leader['constituency'] ?? '') ?>"
                   style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
        </div>
        <div>
            <label style="color:#94a3b8;font-size:.85rem">Role / Position</label><br>
            <input type="text" name="role" value="<?= e($leader['role'] ?? '') ?>"
                   placeholder="e.g. Chief Minister"
                   style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
        </div>
    </div>

    <!-- Photo URL / Upload -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
            <label style="color:#94a3b8;font-size:.85rem">Photo URL (Wikipedia/Official)</label><br>
            <input type="url" name="photo_url" value="<?= e($leader['photo_url'] ?? '') ?>"
                   placeholder="https://upload.wikimedia.org/..."
                   style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
        </div>
        <div>
            <label style="color:#94a3b8;font-size:.85rem">Upload Photo (optional)</label><br>
            <input type="file" name="photo_file" accept="image/*"
                   style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
        </div>
    </div>

    <!-- Scores -->
    <fieldset style="border:1px solid #334155;border-radius:8px;padding:16px">
        <legend style="color:#94a3b8;padding:0 8px">Scores (0–100)</legend>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
            <?php
            $score_fields = [
                'total_score'        => 'Total Score',
                'attendance_score'   => 'Attendance',
                'promise_score'      => 'Promise',
                'criminal_score'     => 'Criminal (higher=cleaner)',
                'fund_score'         => 'Fund Usage',
                'transparency_score' => 'Transparency',
            ];
            foreach ($score_fields as $field => $label):
            ?>
            <div>
                <label style="color:#94a3b8;font-size:.8rem"><?= $label ?></label><br>
                <input type="number" name="<?= $field ?>" min="0" max="100"
                       value="<?= e($leader[$field] ?? 50) ?>"
                       style="width:100%;padding:8px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
            </div>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <!-- DOB / Education -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
            <label style="color:#94a3b8;font-size:.85rem">Date of Birth</label><br>
            <input type="date" name="dob" value="<?= e($leader['dob'] ?? '') ?>"
                   style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
        </div>
        <div>
            <label style="color:#94a3b8;font-size:.85rem">Education</label><br>
            <input type="text" name="education" value="<?= e($leader['education'] ?? '') ?>"
                   style="width:100%;padding:10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
        </div>
    </div>

    <!-- Verified / Status -->
    <div style="display:flex;gap:24px;align-items:center">
        <label style="color:#f8fafc;display:flex;gap:8px;align-items:center;cursor:pointer">
            <input type="checkbox" name="verified" value="1"
                   <?= !empty($leader['verified']) ? 'checked' : '' ?>>
            ✓ Verified Leader
        </label>
        <label style="color:#f8fafc;display:flex;gap:8px;align-items:center;cursor:pointer">
            Status:
            <select name="status"
                    style="padding:6px 10px;background:#1e293b;border:1px solid #334155;color:#f8fafc;border-radius:6px">
                <option value="active"   <?= ($leader['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($leader['status'] ?? '')        === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </label>
    </div>

    <!-- Buttons -->
    <div style="display:flex;gap:12px">
        <button type="submit"
                style="padding:10px 28px;background:#3b82f6;color:#fff;border:none;border-radius:6px;font-size:.95rem;cursor:pointer;font-weight:600">
            <?= $editing ? '💾 Save Changes' : '➕ Add Leader' ?>
        </button>
        <a href="<?= url('admin/leaders') ?>"
           style="padding:10px 20px;background:#1e293b;color:#94a3b8;border-radius:6px;text-decoration:none;font-size:.95rem">
            Cancel
        </a>
        <?php if ($editing): ?>
        <button type="button" onclick="generateBio()"
                style="padding:10px 20px;background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid #3b82f6;border-radius:6px;cursor:pointer">
            🤖 Generate AI Bio
        </button>
        <?php endif; ?>
    </div>
</form>
</div>

<?php if ($editing): ?>
<script>
async function generateBio() {
    const btn = event.target;
    btn.textContent = '⏳ Generating...';
    btn.disabled = true;
    try {
        const r = await fetch('<?= url('api/ai/summarise') ?>', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({leader_id: <?= (int)($leader['id'] ?? 0) ?>})
        });
        const d = await r.json();
        if (d.summary) {
            alert('AI Bio generated and saved! Refresh to see.');
        } else {
            alert('AI not available. Add an API key in Settings.');
        }
    } catch(e) { alert('Error: ' + e.message); }
    btn.textContent = '🤖 Generate AI Bio';
    btn.disabled = false;
}
</script>
<?php endif; ?>
