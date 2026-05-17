<section class="panel">
    <div class="panel__head"><h2>Registered Users</h2></div>
    <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Credibility</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= (int)$user['credibility_score'] ?></td>
                <td><?= htmlspecialchars($user['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
