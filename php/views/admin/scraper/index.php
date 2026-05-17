<?php $page_title = 'Auto Scraper — NetaTrack Admin'; ?>
<div class="page-header">
  <div class="page-title">AI Auto Scraper
    <span>Automated news scraping and political data collection</span>
  </div>
  <div style="display:flex;gap:.5rem">
    <span class="badge <?= ($settings['scraper_enabled']??'1')==='1'?'badge-success':'badge-danger' ?>" style="padding:.5rem 1rem;font-size:.8rem">
      <i class="fas fa-circle" style="font-size:.5rem"></i>
      Scraper <?= ($settings['scraper_enabled']??'1')==='1'?'Active':'Disabled' ?>
    </span>
  </div>
</div>

<div class="grid-2" style="gap:1.25rem">
  <div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-history" style="color:#3b82f6"></i> Recent Scrape Jobs</div></div>
    <?php if(empty($recentJobs)): ?>
      <p style="color:var(--text-muted);text-align:center;padding:2rem">No scrape jobs yet. Configure and run your first job below.</p>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:.5rem">
      <?php foreach($recentJobs as $job): ?>
      <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem;border-radius:8px;background:var(--bg-glass)">
        <span class="badge badge-<?= $job['status']==='success'?'success':($job['status']==='running'?'info':'danger') ?>"><?= e($job['status']) ?></span>
        <div style="flex:1">
          <div style="font-size:.82rem;font-weight:600;color:var(--text-primary)"><?= e($job['source']??'General News') ?></div>
          <div style="font-size:.7rem;color:var(--text-muted)"><?= e($job['articles_scraped']??0) ?> articles • <?= timeAgo($job['created_at']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-play-circle" style="color:#22c55e"></i> Run Manual Scrape</div></div>
    <form method="POST" action="<?= url('admin/scraper') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Source</label>
        <select name="source" class="form-control">
          <option value="all">All Sources</option>
          <option value="ndtv">NDTV</option>
          <option value="hindustantimes">Hindustan Times</option>
          <option value="thehindu">The Hindu</option>
          <option value="indianexpress">Indian Express</option>
          <option value="pib">PIB (Government Press)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Keywords (comma-separated)</label>
        <input type="text" name="keywords" class="form-control" placeholder="e.g. corruption, project delay, budget">
      </div>
      <div class="form-group">
        <label class="form-label">Max Articles</label>
        <input type="number" name="max_articles" class="form-control" value="50" min="10" max="500">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">
        <i class="fas fa-play"></i> Start Scrape Job
      </button>
    </form>
  </div>
</div>

<!-- Source Config -->
<div class="card" style="margin-top:1.25rem">
  <div class="card-header"><div class="card-title"><i class="fas fa-rss" style="color:#f97316"></i> RSS Feed Sources</div></div>
  <div class="table-wrapper">
    <table class="admin-table">
      <thead><tr><th>Source</th><th>URL</th><th>Last Scraped</th><th>Articles</th><th>Status</th></tr></thead>
      <tbody>
        <?php
          $sources = [
            ['NDTV India','https://feeds.feedburner.com/ndtvnews-top-stories','2 hours ago',1243,'active'],
            ['Hindustan Times','https://www.hindustantimes.com/feeds/rss/india-news/rssfeed.xml','4 hours ago',876,'active'],
            ['The Hindu','https://www.thehindu.com/news/national/?service=rss','3 hours ago',654,'active'],
            ['PIB India','https://pib.gov.in/RssMain.aspx?ModId=6','1 hour ago',321,'active'],
            ['India Today','https://www.indiatoday.in/rss/1206514','6 hours ago',432,'paused'],
          ];
          foreach($sources as [$name,$url,$last,$count,$status]):
        ?>
        <tr>
          <td style="font-weight:600;color:var(--text-primary)"><?= $name ?></td>
          <td style="font-size:.75rem;color:var(--text-muted);max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $url ?></td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= $last ?></td>
          <td style="font-weight:600"><?= number_format($count) ?></td>
          <td><span class="badge badge-<?= $status==='active'?'success':'warning' ?>"><?= $status ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
