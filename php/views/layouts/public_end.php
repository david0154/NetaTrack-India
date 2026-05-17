<?php /* NetaTrack India — Public Layout End */ ?>
</main>

<!-- Footer -->
<footer style="background:var(--bg-card);border-top:1px solid var(--border);padding:32px 24px;margin-top:48px">
  <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <div style="font-weight:700;margin-bottom:4px">🇮🇳 NetaTrack India</div>
      <div class="text-muted text-sm">Making Indian politics transparent, one data point at a time.</div>
    </div>
    <div class="text-muted text-sm">
      Built by <a href="https://github.com/david0154">David</a> &mdash; MIT License
    </div>
  </div>
</footer>

<div id="toast-container"></div>
<script src="<?= rtrim(config('app.url',''), '/') ?>/assets/js/app.js"></script>
</body>
</html>
