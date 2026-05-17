<section class="panel form-panel">
    <div class="panel__head"><h2>Edit Leader</h2></div>
    <form method="post" class="form-grid">
        <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
        <label><span>Name</span><input name="name" value="<?= htmlspecialchars($leader['name'] ?? '') ?>" required></label>
        <label><span>Party</span><select name="party_id"><option value="">Select</option><?php foreach($parties as $party): ?><option value="<?= (int)$party['id'] ?>" <?= ((int)($leader['party_id'] ?? 0)===(int)$party['id'])?'selected':'' ?>><?= htmlspecialchars($party['name']) ?></option><?php endforeach; ?></select></label>
        <label><span>State</span><select name="state_id"><option value="">Select</option><?php foreach($states as $state): ?><option value="<?= (int)$state['id'] ?>" <?= ((int)($leader['state_id'] ?? 0)===(int)$state['id'])?'selected':'' ?>><?= htmlspecialchars($state['name']) ?></option><?php endforeach; ?></select></label>
        <label><span>Constituency</span><input name="constituency" value="<?= htmlspecialchars($leader['constituency'] ?? '') ?>"></label>
        <label><span>Position</span><input name="position" value="<?= htmlspecialchars($leader['position'] ?? '') ?>"></label>
        <label class="col-2"><span>Bio</span><textarea name="bio" rows="4"><?= htmlspecialchars($leader['bio'] ?? '') ?></textarea></label>
        <label><span>Promise Completion</span><input type="number" step="0.01" name="promise_completion_rate" value="<?= htmlspecialchars((string)($leader['promise_completion_rate'] ?? 0)) ?>"></label>
        <label><span>Project Delivery</span><input type="number" step="0.01" name="project_delivery_rate" value="<?= htmlspecialchars((string)($leader['project_delivery_rate'] ?? 0)) ?>"></label>
        <label><span>Budget Efficiency</span><input type="number" step="0.01" name="budget_efficiency" value="<?= htmlspecialchars((string)($leader['budget_efficiency'] ?? 0)) ?>"></label>
        <label><span>Public Satisfaction</span><input type="number" step="0.01" name="public_satisfaction" value="<?= htmlspecialchars((string)($leader['public_satisfaction'] ?? 0)) ?>"></label>
        <label><span>Transparency</span><input type="number" step="0.01" name="transparency_score" value="<?= htmlspecialchars((string)($leader['transparency_score'] ?? 0)) ?>"></label>
        <label><span>Verification Trust</span><input type="number" step="0.01" name="verification_trust" value="<?= htmlspecialchars((string)($leader['verification_trust'] ?? 0)) ?>"></label>
        <label><span>Corruption Score</span><input type="number" step="0.01" name="corruption_score" value="<?= htmlspecialchars((string)($leader['corruption_score'] ?? 0)) ?>"></label>
        <label><span>Overall Score</span><input type="number" step="0.01" name="overall_score" value="<?= htmlspecialchars((string)($leader['overall_score'] ?? 0)) ?>"></label>
        <label><span>Rank</span><select name="rank"><?php foreach(['Excellent','Good','Average','Poor'] as $r): ?><option <?= (($leader['rank'] ?? 'Average')===$r)?'selected':'' ?>><?= $r ?></option><?php endforeach; ?></select></label>
        <label><span>Status</span><select name="status"><?php foreach(['active','inactive','deceased'] as $s): ?><option <?= (($leader['status'] ?? 'active')===$s)?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></label>
        <div class="col-2"><button class="btn btn--primary" type="submit">Update Leader</button></div>
    </form>
</section>
