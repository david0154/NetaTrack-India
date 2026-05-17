<?php $page_title = $leader ? 'Edit Leader' : 'Add Leader'; ?>
<div class="form-page">
    <h2><?= $leader ? 'Edit: '.e($leader['name']) : 'Add New Leader' ?></h2>

    <form method="POST" action="<?= url($leader ? 'admin/leaders/'.$leader['id'].'/update' : 'admin/leaders/create') ?>" class="admin-form">
        <?= csrf_field() ?>

        <!-- Basic Info -->
        <section class="form-section">
            <h3>Basic Information</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?= e($leader['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Slug (auto-generated if empty)</label>
                    <input type="text" name="slug" value="<?= e($leader['slug'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Designation</label>
                    <input type="text" name="designation" value="<?= e($leader['designation'] ?? '') ?>" placeholder="e.g. Chief Minister, MP">
                </div>
                <div class="form-group">
                    <label>Constituency</label>
                    <input type="text" name="constituency" value="<?= e($leader['constituency'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>State</label>
                    <select name="state_id">
                        <option value="">-- Select State --</option>
                        <?php foreach ($states as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($leader['state_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Party</label>
                    <select name="party_id">
                        <option value="">-- Select Party --</option>
                        <?php foreach ($parties as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($leader['party_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" value="<?= e($leader['dob'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <?php foreach (['','Male','Female','Other'] as $g): ?>
                        <option value="<?= $g ?>" <?= ($leader['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?: '-- Select --' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" rows="4"><?= e($leader['bio'] ?? '') ?></textarea>
            </div>
        </section>

        <!-- Scores -->
        <section class="form-section">
            <h3>Performance Scores (0–100)</h3>
            <div class="form-grid-4">
                <?php
                $scoreFields = [
                    'score_promise_completion'    => 'Promise Completion',
                    'score_project_delivery'      => 'Project Delivery',
                    'score_transparency'          => 'Transparency',
                    'score_public_satisfaction'   => 'Public Satisfaction',
                    'score_attendance'            => 'Attendance',
                    'score_criminal_record'       => 'Criminal Record',
                    'score_assets_declared'       => 'Assets Declared',
                    'score_social_media_activity' => 'Social Media',
                ];
                foreach ($scoreFields as $field => $label): $val = $leader[$field] ?? 50; ?>
                <div class="form-group">
                    <label><?= $label ?> <span class="score-live" id="lv_<?= $field ?>"><?= $val ?></span></label>
                    <input type="range" name="<?= $field ?>" min="0" max="100"
                           value="<?= $val ?>"
                           oninput="document.getElementById('lv_<?= $field ?>').textContent=this.value">
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Meta -->
        <section class="form-section">
            <h3>Criminal Record &amp; Assets</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Criminal Cases</label>
                    <input type="number" name="criminal_cases" min="0" value="<?= e($leader['criminal_cases'] ?? 0) ?>">
                </div>
                <div class="form-group">
                    <label>Assets Declared (₹ Crore)</label>
                    <input type="number" step="0.01" name="assets_declared" value="<?= e($leader['assets_declared'] ?? 0) ?>">
                </div>
            </div>
        </section>

        <!-- Status -->
        <section class="form-section">
            <h3>Status</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['active','inactive','draft'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($leader['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:28px">
                    <input type="checkbox" name="is_verified" id="is_verified" value="1" <?= !empty($leader['is_verified']) ? 'checked' : '' ?>>
                    <label for="is_verified" style="margin:0">Verified Leader &#10003;</label>
                </div>
            </div>
        </section>

        <div class="form-actions">
            <a href="<?= url('admin/leaders') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><?= $leader ? 'Update Leader' : 'Create Leader' ?></button>
        </div>
    </form>
</div>
