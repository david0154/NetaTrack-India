<section class="panel form-panel">
    <div class="panel__head"><h2>Create Leader</h2></div>
    <form method="post" class="form-grid">
        <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
        <label><span>Name</span><input name="name" required></label>
        <label><span>Party</span><select name="party_id"><option value="">Select</option><?php foreach($parties as $party): ?><option value="<?= (int)$party['id'] ?>"><?= htmlspecialchars($party['name']) ?></option><?php endforeach; ?></select></label>
        <label><span>State</span><select name="state_id"><option value="">Select</option><?php foreach($states as $state): ?><option value="<?= (int)$state['id'] ?>"><?= htmlspecialchars($state['name']) ?></option><?php endforeach; ?></select></label>
        <label><span>Constituency</span><input name="constituency"></label>
        <label><span>Position</span><input name="position"></label>
        <label class="col-2"><span>Bio</span><textarea name="bio" rows="4"></textarea></label>
        <label><span>Promise Completion</span><input type="number" step="0.01" name="promise_completion_rate" value="0"></label>
        <label><span>Project Delivery</span><input type="number" step="0.01" name="project_delivery_rate" value="0"></label>
        <label><span>Budget Efficiency</span><input type="number" step="0.01" name="budget_efficiency" value="0"></label>
        <label><span>Public Satisfaction</span><input type="number" step="0.01" name="public_satisfaction" value="0"></label>
        <label><span>Transparency</span><input type="number" step="0.01" name="transparency_score" value="0"></label>
        <label><span>Verification Trust</span><input type="number" step="0.01" name="verification_trust" value="0"></label>
        <label><span>Corruption Score</span><input type="number" step="0.01" name="corruption_score" value="0"></label>
        <label><span>Overall Score</span><input type="number" step="0.01" name="overall_score" value="0"></label>
        <label><span>Rank</span><select name="rank"><option>Excellent</option><option>Good</option><option selected>Average</option><option>Poor</option></select></label>
        <label><span>Status</span><select name="status"><option selected>active</option><option>inactive</option><option>deceased</option></select></label>
        <div class="col-2"><button class="btn btn--primary" type="submit">Save Leader</button></div>
    </form>
</section>
