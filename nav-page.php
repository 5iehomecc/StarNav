<?php
/**
 * StarNav - 个人导航页面
 * 
 * @version 2.3.0
 * @author StarNav Team
 * 
 * 功能：
 * - 路由处理：API请求转发到nav-api.php
 * - 页面渲染：服务端渲染书签和分组
 * - 主题切换：基于系统时间自动判断（6:00-18:00为白天模式）
 * - 管理员模式：登录后显示编辑/删除按钮
 */

// ═══════════════════════════════════════════════════════════════
// 路由处理
// ═══════════════════════════════════════════════════════════════
// 如果是API请求，转发到nav-api.php处理
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'nav-api.php') !== false) {
    require __DIR__ . '/nav-api.php';
    return;
}

// ═══════════════════════════════════════════════════════════════
// 初始化
// ═══════════════════════════════════════════════════════════════
session_start();

// Gzip压缩输出（提升加载速度）
$enableGzip = isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
if ($enableGzip) {
    ob_start(function($content) {
        $encoded = gzencode($content, 6);
        return $encoded ? $encoded : $content;
    });
    header('Content-Encoding: gzip');
} else {
    ob_start();
}

// 安全响应头
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// ═══════════════════════════════════════════════════════════════
// 数据加载函数
// ═══════════════════════════════════════════════════════════════
$dataFile = __DIR__ . '/nav-data.json';

/**
 * 加载导航数据
 */
function loadData() {
    global $dataFile;
    if (file_exists($dataFile)) {
        $data = json_decode(file_get_contents($dataFile), true);
        if ($data && is_array($data['groups'] ?? null)) {
            return $data;
        }
    }
    // 返回默认数据结构
    return [
        'adminHash' => '44f61792d66021c0030fa37dca5162871345c525f61984b88fa1af16d8117672',
        'siteName' => 'E家导航',
        'siteDesc' => 'E家导航 - 最实用的经验，分享最需要的你',
        'groups' => [['id' => 'default', 'name' => '默认', 'emoji' => '📌', 'bookmarks' => []]]
    ];
}

/**
 * 检查是否为管理员模式
 */
function isAdmin() {
    return !empty($_SESSION['admin']);
}

/**
 * HTML转义（防止XSS）
 */
function esc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// 加载数据和检查管理员状态
$data = loadData();
$adminMode = isAdmin();
?>
<!DOCTYPE html><html lang="zh-CN"><head><script>(function(){var h=new Date().getHours();var t=(h>=6&&h<18)?'light':'dark';document.documentElement.setAttribute('data-theme',t)})()</script><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=esc($data['siteName'])?></title><meta name="application-name" content="<?=esc($data['siteName'])?>"><meta name="theme-color" id="metaTC" content="#ffffff"><link rel="icon" href="https://www.5iehome.cc/favicon.ico"><link rel="dns-prefetch" href="https://t0.gstatic.cn"><style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
:root{
  --radius-xs:5px;--radius-sm:8px;--radius-md:11px;--radius-lg:18px;--radius-xl:22px;--radius-pill:980px;
  --font-body:-apple-system,BlinkMacSystemFont,"SF Pro Text","SF Pro Display","Helvetica Neue","PingFang SC",sans-serif;
  --font-display:-apple-system,BlinkMacSystemFont,"SF Pro Display","Helvetica Neue","PingFang SC",sans-serif;
  --font-mono:"SF Mono","JetBrains Mono",ui-monospace,monospace;
  --ease:cubic-bezier(0.4,0,0.6,1);--ease-out:cubic-bezier(0.4,0,0.2,1);
  --transition-fast:0.12s var(--ease);--transition:0.24s var(--ease);--transition-slow:0.32s var(--ease)
}
/* ── Dark Theme ── */
[data-theme="dark"]{
  --bg-primary:#000000;--bg-secondary:#161617;
  --bg-card:rgba(28,28,30,0.72);--bg-card-hover:rgba(44,44,46,0.8);
  --border-subtle:rgba(255,255,255,0.08);--border-regular:rgba(255,255,255,0.12);
  --text-primary:#f5f5f7;--text-secondary:#a1a1a6;--text-tertiary:#6e6e73;
  --accent:#2997ff;--accent-hover:#4db2ff;--accent-surface:rgba(41,151,255,0.12);
  --danger:#ff453a;--danger-surface:rgba(255,69,58,0.12);
  --success:#30d158;--success-surface:rgba(48,209,88,0.12);
  --shadow-card:0 2px 12px rgba(0,0,0,0.3);--shadow-float:0 12px 40px rgba(0,0,0,0.5);
  --cell-bg:rgba(255,255,255,0.04);--cell-hover:rgba(255,255,255,0.08);
  --input-bg:rgba(255,255,255,0.06);--input-border:rgba(255,255,255,0.1);
  --favicon-bg:rgba(255,255,255,0.06);
  --overlay-bg:rgba(0,0,0,0.5);
  --scrollbar-thumb:rgba(255,255,255,0.15)
}
/* ── Light Theme ── */
[data-theme="light"]{
  --bg-primary:#f5f5f7;--bg-secondary:#ffffff;
  --bg-card:rgba(255,255,255,0.8);--bg-card-hover:rgba(255,255,255,0.95);
  --border-subtle:rgba(0,0,0,0.06);--border-regular:rgba(0,0,0,0.1);
  --text-primary:#1d1d1f;--text-secondary:#6e6e73;--text-tertiary:#a1a1a6;
  --accent:#0066cc;--accent-hover:#0077e6;--accent-surface:rgba(0,102,204,0.08);
  --danger:#ff3b30;--danger-surface:rgba(255,59,48,0.08);
  --success:#34c759;--success-surface:rgba(52,199,89,0.08);
  --shadow-card:0 1px 4px rgba(0,0,0,0.06);--shadow-float:0 12px 40px rgba(0,0,0,0.12);
  --cell-bg:rgba(0,0,0,0.03);--cell-hover:rgba(0,0,0,0.06);
  --input-bg:rgba(0,0,0,0.03);--input-border:rgba(0,0,0,0.1);
  --favicon-bg:rgba(0,0,0,0.04);
  --overlay-bg:rgba(0,0,0,0.3);
  --scrollbar-thumb:rgba(0,0,0,0.15)
}
body{font-family:var(--font-body);background:var(--bg-primary);color:var(--text-primary);min-height:100vh;overflow-x:hidden;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;transition:background .4s var(--ease),color .4s var(--ease);font-size:18px;line-height:1.5;letter-spacing:-0.4px}
/* ── Scrollbar ── */
::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:var(--scrollbar-thumb);border-radius:3px}

/* ══════════════════════════════════════════════════════════════
   LAYOUT: Startab-style horizontal bookmark rows
   ══════════════════════════════════════════════════════════════ */

/* ── Page Container ── */
.page{width:100%;min-height:100vh;position:relative;z-index:1}

/* ── Header ── */
.page-header{display:flex;align-items:center;justify-content:center;padding:16px 24px;position:relative;z-index:10}
.site-name{font-family:var(--font-display);font-size:18px;font-weight:600;letter-spacing:0.15em;color:var(--text-secondary);transition:color .3s var(--ease);display:flex;align-items:center;gap:10px}
.site-logo{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:var(--accent);color:#fff;font-size:16px;font-weight:700;letter-spacing:-0.02em;flex-shrink:0;transition:transform .2s var(--ease)}
.site-name:hover .site-logo{transform:scale(1.05)}
.site-name:hover{color:var(--text-primary)}

/* ── Top-right controls ── */
.top-controls{position:absolute;right:24px;top:50%;transform:translateY(-50%);display:flex;gap:8px;flex-shrink:0}
.top-ctrl-btn{width:38px;height:38px;border-radius:50%;background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-tertiary);transition:var(--transition-fast)}
.top-ctrl-btn:hover{background:var(--cell-hover);color:var(--text-secondary)}
.top-ctrl-btn svg{width:20px;height:20px}

/* ── Theme toggle ── */
.theme-toggle{position:relative;width:20px;height:20px;flex-shrink:0}
.theme-toggle .sun-icon,.theme-toggle .moon-icon{position:absolute;inset:0;transition:opacity .3s var(--ease)}
[data-theme="dark"] .theme-toggle .sun-icon{opacity:1}[data-theme="dark"] .theme-toggle .moon-icon{opacity:0}
[data-theme="light"] .theme-toggle .sun-icon{opacity:0}[data-theme="light"] .theme-toggle .moon-icon{opacity:1}

/* ── Main Content ── */
.main-content{width:100%;max-width:1200px;margin:0 auto;padding:32px 24px 100px}

/* ── Group Section ── */
.group-section{scroll-margin-top:24px;margin-bottom:40px;animation:fadeUp .5s var(--ease-out) both}
.group-section.visible{animation:none;opacity:1;transform:translateY(0)}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

.group-label{display:flex;align-items:center;gap:12px;margin-bottom:32px;padding-bottom:12px;border-bottom:1px solid var(--border-regular)}
.group-label-icon{font-size:22px;line-height:1}
.group-label-text{font-size:19px;font-weight:600;letter-spacing:-0.02em;color:var(--text-secondary)}
.group-actions-inline{display:flex;gap:4px;margin-left:auto;flex-shrink:0}
.group-actions-inline button{background:none;border:none;cursor:pointer;width:30px;height:30px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--text-tertiary);transition:var(--transition-fast);font-size:14px;opacity:0}
.group-section:hover .group-actions-inline button{opacity:1}
.group-actions-inline button:hover{background:var(--cell-hover);color:var(--text-secondary)}

/* ── Bookmark Row ── */
.bookmarks-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(max(150px,calc((100% - 16px)/5)),1fr));gap:8px;align-items:center}

/* ── Bookmark Item ── */
.bookmark-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;text-decoration:none;color:var(--text-secondary);font-size:16px;transition:background .15s var(--ease-out),color .15s var(--ease-out),transform .1s var(--ease);white-space:nowrap;overflow:hidden;position:relative;cursor:pointer;font-weight:400;letter-spacing:-0.1px;min-width:0}
.bookmark-item:hover{background:var(--cell-hover);color:var(--text-primary)}
.bookmark-item:active{transform:scale(0.96);background:var(--cell-hover);transition-duration:.06s}
.bookmark-favicon{width:26px;height:26px;border-radius:5px;background:var(--favicon-bg);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
.bookmark-favicon img{width:22px;height:22px;object-fit:contain}
.bookmark-favicon .fallback-icon{font-size:14px;color:var(--text-tertiary)}
.bookmark-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;transition:color .15s var(--ease)}

/* ── Admin bookmark actions ── */
.bookmark-edit,.bookmark-delete{position:absolute;top:4px;width:22px;height:22px;border-radius:50%;background:var(--bg-card);border:none;cursor:pointer;display:none;align-items:center;justify-content:center;color:var(--text-tertiary);font-size:11px;transition:var(--transition-fast);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);z-index:2}
.bookmark-edit{right:4px}.bookmark-delete{right:28px}
body.admin-mode .bookmark-item:hover .bookmark-edit,body.admin-mode .bookmark-item:hover .bookmark-delete{display:flex}
.bookmark-edit:hover{background:var(--accent);color:#fff}.bookmark-delete:hover{background:var(--danger);color:#fff}

/* ── FAB - hover to show TOC ── */
.fab-wrap{position:fixed;bottom:32px;right:32px;z-index:50;display:flex;flex-direction:column;align-items:center;gap:10px}
.fab{width:52px;height:52px;border-radius:50%;background:var(--accent);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;transition:var(--transition);box-shadow:0 4px 16px rgba(0,0,0,0.25)}
.fab:hover{transform:scale(1.06);box-shadow:0 6px 24px rgba(0,0,0,0.35)}
.fab svg{width:24px;height:24px;transition:transform .3s var(--ease)}
.fab:hover svg{transform:scale(1.1)}
.fab-toc{display:flex;flex-direction:column;gap:4px;align-items:flex-end;opacity:0;visibility:hidden;transform:translateY(8px);transition:all .25s var(--ease-out);pointer-events:none}
.fab-wrap:hover .fab-toc{opacity:1;visibility:visible;transform:translateY(0);pointer-events:auto}
.fab-toc-item{display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:var(--radius-pill);background:var(--bg-secondary);border:1px solid var(--border-subtle);color:var(--text-secondary);font-size:15px;cursor:pointer;transition:var(--transition-fast);white-space:nowrap;letter-spacing:-0.1px;box-shadow:var(--shadow-card);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);text-decoration:none}
.fab-toc-item:hover{background:var(--bg-card-hover);color:var(--text-primary);border-color:var(--border-regular)}
.fab-toc-item.active{background:var(--accent-surface);color:var(--accent)}
.fab-toc-item svg{width:18px;height:18px;flex-shrink:0}
.fab-toc-divider{height:1px;width:100%;background:var(--border-subtle);margin:4px 0}

/* ── Modal ── */
.modal-overlay{position:fixed;inset:0;z-index:1000;background:var(--overlay-bg);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);display:none;align-items:center;justify-content:center;padding:20px;animation:fadeIn .2s var(--ease)}
.modal-overlay.show{display:flex}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.modal{background:var(--bg-secondary);border:1px solid var(--border-regular);border-radius:var(--radius-xl);padding:28px 28px 24px;width:100%;max-width:440px;box-shadow:var(--shadow-float);animation:slideUp .3s var(--ease-out)}
.modal h2{font-size:24px;font-weight:700;margin-bottom:22px;letter-spacing:-0.4px;color:var(--text-primary)}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:15px;color:var(--text-secondary);margin-bottom:7px;font-weight:500;letter-spacing:-0.25px}
.form-group input{width:100%;padding:12px 16px;background:var(--input-bg);border:1px solid var(--input-border);border-radius:var(--radius-md);color:var(--text-primary);font-size:17px;transition:var(--transition);outline:none;letter-spacing:-0.25px}
.form-group input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-surface)}
.form-group input::placeholder{color:var(--text-tertiary)}
.form-group .input-row{display:flex;gap:8px}
.form-group .input-row input{flex:1}
.fetch-btn{padding:12px 16px;background:var(--accent-surface);border:1px solid rgba(41,151,255,0.2);border-radius:var(--radius-md);color:var(--accent);cursor:pointer;font-size:15px;white-space:nowrap;transition:var(--transition);letter-spacing:-0.25px;font-weight:500}
[data-theme="light"] .fetch-btn{border-color:rgba(0,102,204,0.2)}
.fetch-btn:hover{background:var(--accent);color:#fff}.fetch-btn:disabled{opacity:.5;cursor:not-allowed}
.favicon-preview{display:flex;align-items:center;gap:12px;margin-top:8px}
.favicon-preview img{width:32px;height:32px;border-radius:var(--radius-sm);background:var(--favicon-bg)}
.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:24px}
.btn{padding:12px 24px;border-radius:var(--radius-pill);font-size:17px;font-weight:500;cursor:pointer;transition:var(--transition);border:none;letter-spacing:-0.25px}
.btn-primary{background:var(--accent);color:#fff}.btn-primary:hover{background:var(--accent-hover)}
.btn-secondary{background:var(--cell-bg);color:var(--text-secondary);border:1px solid var(--border-regular)}.btn-secondary:hover{background:var(--cell-hover);color:var(--text-primary)}
.btn-danger{background:var(--danger-surface);color:var(--danger);border:1px solid transparent}.btn-danger:hover{background:var(--danger);color:#fff}
.spinner{display:inline-block;width:14px;height:14px;border:2px solid var(--text-tertiary);border-top-color:var(--accent);border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Toast ── */
.toast{position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(16px);background:var(--bg-secondary);border:1px solid var(--border-regular);border-radius:var(--radius-pill);padding:12px 28px;font-size:16px;box-shadow:var(--shadow-float);z-index:2000;opacity:0;transition:all .3s var(--ease-out);pointer-events:none;letter-spacing:-0.25px;white-space:nowrap}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast.error{border-color:rgba(255,69,58,0.3);color:var(--danger)}.toast.success{border-color:rgba(48,209,88,0.3);color:var(--success)}

/* ── Emoji Picker ── */
.emoji-picker{display:grid;grid-template-columns:repeat(8,1fr);gap:4px;margin-top:8px}
.emoji-option{width:40px;height:40px;border:none;background:none;cursor:pointer;border-radius:var(--radius-sm);font-size:20px;transition:var(--transition-fast);display:flex;align-items:center;justify-content:center}
.emoji-option:hover{background:var(--cell-hover)}.emoji-option.active{background:var(--accent-surface)}

/* ── Drag & Drop ── */
body.admin-mode .bookmark-item[draggable="true"]{cursor:grab}
body.admin-mode .bookmark-item.dragging{opacity:0.4;transform:scale(0.95)}
body.admin-mode .bookmark-item.drag-over{box-shadow:inset 2px 0 0 var(--accent);background:var(--accent-surface)}
body.admin-mode .bookmarks-row.drag-over-row{outline:2px dashed var(--accent);outline-offset:4px;border-radius:var(--radius-sm)}
body.admin-mode .group-section.drag-over-group{outline:2px dashed var(--accent);outline-offset:4px;border-radius:var(--radius-md)}

/* ── Admin / Guest ── */
.admin-only{display:none}body.admin-mode .admin-only{display:flex}
.guest-only{display:none}body:not(.admin-mode) .guest-only{display:flex}

/* ── Footer ── */
.site-footer{text-align:center;padding:32px 0 24px;font-size:14px;color:var(--text-tertiary);letter-spacing:0.04em;margin-top:16px}
.site-footer a{color:var(--text-tertiary);text-decoration:none;transition:color .2s ease}
[data-theme="light"] .site-footer a:hover{color:#001e1d}
[data-theme="dark"] .site-footer a:hover{color:#fffffe}

/* ── Empty State ── */
.empty-state{text-align:center;padding:24px;color:var(--text-tertiary);font-size:13px}

/* ── Responsive ── */
@media(max-width:768px){
  .page-header{padding:12px 16px}
  .top-controls{right:16px}
  .main-content{padding:24px 16px 100px}
  .group-section{margin-bottom:28px}
  .group-label{margin-bottom:24px}
  .bookmarks-row{grid-template-columns:repeat(auto-fill,minmax(max(120px,calc((100% - 6px)/4)),1fr))}
  .bookmark-item{font-size:13px;padding:5px 8px}
  .bookmark-favicon{width:18px;height:18px}
  .bookmark-favicon img{width:14px;height:14px}
  .bookmark-name{max-width:160px}
  .modal{padding:24px;margin:12px}
  .fab-wrap{bottom:20px;right:20px}
  .fab{width:40px;height:40px}
  .fab svg{width:18px;height:18px}
}
@media(max-width:480px){
  .page-header{padding:10px 12px}
  .top-controls{right:12px}
  .main-content{padding:20px 12px 100px}
  .bookmarks-row{grid-template-columns:repeat(auto-fill,minmax(max(100px,calc((100% - 6px)/3)),1fr))}
  .bookmark-item{font-size:13px;padding:4px 7px;gap:5px}
  .bookmark-name{max-width:120px}
  .group-label{margin-bottom:20px}
  .group-label-text{font-size:14px}
}
</style></head>
<body <?=$adminMode?'class="admin-mode"':''?>>
<!-- Page Layout -->
<div class="page">
  <!-- Header -->
  <div class="page-header">
    <div class="site-name"><span class="site-logo"><?=esc(mb_substr($data['siteName'],0,1,'UTF-8'))?></span><?=esc(mb_substr($data['siteName'],1,null,'UTF-8'))?></div>
    <div class="top-controls">
      <button class="top-ctrl-btn" onclick="toggleTheme()" title="切换主题">
        <span class="theme-toggle"><svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg><svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></span>
      </button>
      <?php if($adminMode):?>
      <a class="top-ctrl-btn" href="nav-api.php?action=logout" title="退出" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </a>
      <?php else:?>
      <button class="top-ctrl-btn" onclick="openLoginModal()" title="管理员登录">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/></svg>
      </button>
      <?php endif;?>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">
    <?php foreach($data['groups'] as $gi=>$g):?>
    <div class="group-section" id="g-<?=esc($g['id'])?>" data-group-id="<?=esc($g['id'])?>">
      <div class="group-label">
        <span class="group-label-icon"><?=esc($g['emoji'])?></span>
        <span class="group-label-text"><?=esc($g['name'])?></span>
        <div class="group-actions-inline admin-only">
          <button onclick="openBookmarkModal('<?=esc($g['id'])?>')" title="添加书签">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <button onclick="openGroupModal('<?=esc($g['id'])?>')" title="编辑分组">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button onclick="confirmDeleteGroup('<?=esc($g['id'])?>')" title="删除分组">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </div>
      </div>
      <div class="bookmarks-row">
        <?php if(empty($g['bookmarks'])):?>
        <div class="empty-state">暂无书签</div>
        <?php endif;?>
        <?php foreach($g['bookmarks'] as $bi=>$bm):?>
        <a class="bookmark-item" href="<?=esc($bm['url'])?>" target="_blank" rel="noopener" data-name="<?=esc($bm['name'])?>" data-group="<?=esc($g['id'])?>" data-bookmark-id="<?=esc($bm['id'])?>" data-url="<?=esc($bm['url'])?>" title="<?=esc($bm['name']) . ($bm['desc']?' - '.esc($bm['desc']):'')?>">
          <button class="bookmark-edit admin-only" onclick="event.preventDefault();event.stopPropagation();openBookmarkModal('<?=esc($g['id'])?>','<?=esc($bm['id'])?>')" title="编辑">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="bookmark-delete admin-only" onclick="event.preventDefault();event.stopPropagation();confirmDeleteBookmark('<?=esc($g['id'])?>','<?=esc($bm['id'])?>')" title="删除">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
          <div class="bookmark-favicon">
            <?php
            $fv=$bm['favicon']??'';
            if($fv&&preg_match('/\.(png|ico|svg|jpg|jpeg|webp|gif)$/i',$fv)){
              echo '<img src="'.esc($fv).'" alt="" loading="lazy" onerror="tryNextFavicon(this)">';
            }elseif(!empty($bm['url'])){
              $d=parse_url($bm['url'],PHP_URL_HOST);
              $d=preg_replace('/^www\./','',$d);
              $fu='https://t0.gstatic.cn/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=http://'.$d.'&size=32';
              echo '<img src="'.esc($fv?:$fu).'" alt="" loading="lazy" onerror="tryNextFavicon(this)">';
            }else{
              echo '<span class="fallback-icon">🔗</span>';
            }
            ?>
          </div>
          <span class="bookmark-name" title="<?=esc($bm['name'])?>"><?=esc($bm['name'])?></span>
        </a>
        <?php endforeach;?>
      </div>
    </div>
    <?php endforeach;?>
  </div>

  <!-- Footer -->
  <footer class="site-footer">Copyright &copy; <a href="https://www.5iehome.cc" target="_blank">E家分享</a></footer>
</div>

<!-- FAB with TOC -->
<div class="fab-wrap">
  <div class="fab-toc" id="fabToc">
    <a class="fab-toc-item" onclick="window.scrollTo({top:0,behavior:'smooth'})">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
      回到顶部
    </a>
    <div class="fab-toc-divider"></div>
    <?php foreach($data['groups'] as $g):?>
    <a class="fab-toc-item" data-group="g-<?=esc($g['id'])?>" onclick="document.getElementById('g-<?=esc($g['id'])?>').scrollIntoView({behavior:'smooth',block:'start'})">
      <span><?=esc($g['emoji'])?></span>
      <?=esc($g['name'])?>
    </a>
    <?php endforeach;?>
    <div class="fab-toc-divider"></div>
    <?php if($adminMode):?>
    <a class="fab-toc-item" onclick="openBookmarkModal('<?=esc($data['groups'][0]['id']??'default')?>')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      添加书签
    </a>
    <a class="fab-toc-item" onclick="openGroupModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      添加分组
    </a>
    <a class="fab-toc-item" href="nav-api.php?action=logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      退出登录
    </a>
    <?php endif;?>
  </div>
  <button class="fab" id="fabBtn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
</div>

<!-- Login Modal -->
<div class="modal-overlay" id="lM"><div class="modal"><h2>管理员登录</h2><div class="form-group"><label>密码</label><input type="password" id="lP" placeholder="请输入管理密码" onkeydown="if(event.key==='Enter')doLogin()"></div><div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal('lM')">取消</button><button class="btn btn-primary" onclick="doLogin()">登录</button></div></div></div>
<!-- Bookmark Modal -->
<div class="modal-overlay" id="bmM"><div class="modal"><h2 id="bmMT">添加书签</h2><div class="form-group"><label>网址</label><div class="input-row"><input type="text" id="bmU" placeholder="https://example.com" oninput="onUrlInput()"><button class="fetch-btn" id="fB" onclick="fetchSiteInfo()">自动获取</button></div></div><div class="form-group"><label>名称</label><input type="text" id="bmN" placeholder="网站名称"></div><div class="form-group"><label>描述</label><input type="text" id="bmD" placeholder="简短描述（可选）"></div><div class="form-group"><label>图标 URL</label><div class="input-row"><input type="text" id="bmF" placeholder="留空则自动获取" oninput="onFaviconInput()"><button class="fetch-btn" onclick="resetFavicon()">重置</button></div><div class="favicon-preview" id="fP"><span style="color:var(--text-tertiary)">输入网址后自动获取</span></div></div><div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal('bmM')">取消</button><button class="btn btn-primary" onclick="saveBookmark()">保存</button></div></div></div>
<!-- Group Modal -->
<div class="modal-overlay" id="gM"><div class="modal"><h2 id="gMT">添加分组</h2><div class="form-group"><label>分组名称</label><input type="text" id="gN" placeholder="输入分组名称"></div><div class="form-group"><label>图标</label><div class="emoji-picker" id="eP"></div></div><div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal('gM')">取消</button><button class="btn btn-primary" onclick="saveGroup()">保存</button></div></div></div>
<!-- Confirm Modal -->
<div class="modal-overlay" id="cfM">
  <div class="modal">
    <h2 id="cfMT">确认操作</h2>
    <p id="cfMM" style="color:var(--text-secondary);font-size:15px;margin-bottom:4px;line-height:1.5"></p>
    <div class="form-group" id="cfMI" style="display:none">
      <label>请输入 "<span id="cfME"></span>" 确认</label>
      <input type="text" id="cfMV" placeholder="输入确认文字" onkeydown="if(event.key==='Enter')handleConfirm()">
    </div>
    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="closeModal('cfM')">取消</button>
      <button class="btn btn-danger" id="cfMB" onclick="handleConfirm()">确认删除</button>
    </div>
  </div>
</div>
<!-- Toast -->
<div class="toast" id="tst"></div>
<script>
/**
 * StarNav 前端交互脚本
 * 
 * 功能模块：
 * 1. 主题切换（基于系统时间自动判断）
 * 2. 书签管理（增删改查）
 * 3. 分组管理（增删改）
 * 4. 智能信息获取（自动抓取网站标题和图标）
 * 5. 拖拽排序（管理员模式）
 * 6. 目录导航（FAB按钮）
 */

// ═══════════════════════════════════════════════════════════════
// 常量定义
// ═══════════════════════════════════════════════════════════════
const EMOJIS = ['📌','🔧','🎮','📚','💼','🎨','🎵','🌍','💡','🔥','⭐','🚀','📱','💻','🏠','❤️','🎯','🔑','☁️','📧','🛒','📷','🎬','🔬','📝','🎪','🌈','🏆','🤖','🧠','🧩','⚡','🛡️','🌐','📊','🎙️','💬','🧪','🎧','🧭','🏛️','📡','⚙️','🪄','🗂️','🧬','🤝','💰'];
const THEME_KEY = 'starnav_theme';
const CACHE_PREFIX = 'starnav_cache_';
const CACHE_EXPIRY = 604800000; // 7天（毫秒）

// ═══════════════════════════════════════════════════════════════
// 全局状态
// ═══════════════════════════════════════════════════════════════
let editingBookmark = null;  // 当前编辑的书签
let editingGroup = null;     // 当前编辑的分组
let selectedEmoji = '📌';    // 当前选中的emoji

// ═══════════════════════════════════════════════════════════════
// 工具函数
// ═══════════════════════════════════════════════════════════════

/**
 * 获取域名（去除www前缀）
 */
const getDomain = url => {
  try {
    return new URL(url).hostname.replace(/^www\./, '');
  } catch (e) {
    return url;
  }
};

/**
 * 生成Favicon URL（Google服务）
 */
const getFaviconUrl = url => {
  try {
    const domain = getDomain(url);
    return `https://t0.gstatic.cn/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=http://${domain}&size=32`;
  } catch (e) {
    return '';
  }
};

/**
 * 获取多个Favicon备选URL
 */
const getFaviconFallbacks = url => {
  try {
    const domain = getDomain(url);
    return [
      `https://t0.gstatic.cn/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=http://${domain}&size=32`,
      `https://ico.kucat.cn/get.php?url=${url}`,
      `https://icon.horse/icon/${domain}`
    ];
  } catch (e) {
    return [];
  }
};

/**
 * Favicon加载失败时的降级处理
 * 依次尝试多个图标源，最终回退到链接emoji
 */
function tryNextFavicon(img) {
  // data-url 在祖先 .bookmark-item 上
  const bookmarkItem = img.closest('.bookmark-item');
  const url = bookmarkItem ? bookmarkItem.dataset.url : '';

  if (!url || !url.startsWith('http')) {
    img.parentElement.innerHTML = '<span class="fallback-icon">🔗</span>';
    return;
  }

  const fallbacks = getFaviconFallbacks(url);
  const currentIndex = parseInt(img.dataset.index || '0');
  const nextIndex = currentIndex + 1;

  if (nextIndex < fallbacks.length) {
    img.dataset.index = nextIndex;
    img.src = fallbacks[nextIndex];
  } else {
    img.parentElement.innerHTML = '<span class="fallback-icon">🔗</span>';
  }
}

/**
 * SHA-256密码哈希（用于登录验证）
 */
async function hashPassword(password) {
  const salted = password + '_starnav_salt_2024';
  const data = new TextEncoder().encode(salted);
  const hash = await crypto.subtle.digest('SHA-256', data);
  return Array.from(new Uint8Array(hash))
    .map(b => b.toString(16).padStart(2, '0'))
    .join('');
}

// ═══════════════════════════════════════════════════════════════
// 主题切换
// ═══════════════════════════════════════════════════════════════

/**
 * 切换主题（亮色/暗色）
 */
function toggleTheme() {
  const html = document.documentElement;
  const currentTheme = html.getAttribute('data-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  
  html.setAttribute('data-theme', newTheme);
  localStorage.setItem(THEME_KEY, newTheme);
  
  // 更新meta theme-color
  const metaThemeColor = document.getElementById('metaTC');
  if (metaThemeColor) {
    metaThemeColor.content = newTheme === 'dark' ? '#000000' : '#f5f5f7';
  }
}

// ═══════════════════════════════════════════════════════════════
// 模态框管理
// ═══════════════════════════════════════════════════════════════

/**
 * 关闭模态框
 */
function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('show');
}

/**
 * 打开登录模态框
 */
function openLoginModal() {
  document.getElementById('lP').value = '';
  document.getElementById('lM').classList.add('show');
  setTimeout(() => document.getElementById('lP').focus(), 100);
}

/**
 * 显示Toast提示
 */
function showToast(message, type = '') {
  const toast = document.getElementById('tst');
  toast.textContent = message;
  toast.className = 'toast' + (type ? ' ' + type : '');
  requestAnimationFrame(() => toast.classList.add('show'));
  setTimeout(() => toast.classList.remove('show'), 2500);
}

// 点击模态框外部关闭
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) overlay.classList.remove('show');
  });
});

// ESC键关闭所有模态框
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
  }
});

// ═══════════════════════════════════════════════════════════════
// 登录功能
// ═══════════════════════════════════════════════════════════════

/**
 * 执行登录
 */
async function doLogin() {
  const password = document.getElementById('lP').value;
  
  if (!password) {
    showToast('请输入密码', 'error');
    return;
  }
  
  const hashedPassword = await hashPassword(password);
  const formData = new FormData();
  formData.append('action', 'login');
  formData.append('password', hashedPassword);
  
  try {
    const response = await fetch('nav-api.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      closeModal('lM');
      showToast('登录成功', 'success');
      setTimeout(() => location.reload(), 500);
    } else {
      showToast(result.message || '密码错误', 'error');
    }
  } catch (error) {
    console.error('登录错误:', error);
    showToast('登录失败: ' + error.message, 'error');
  }
}

// ═══════════════════════════════════════════════════════════════
// 书签管理
// ═══════════════════════════════════════════════════════════════

/**
 * 打开书签模态框（新增或编辑）
 */
function openBookmarkModal(groupId, bookmarkId) {
  editingBookmark = { groupId, bookmarkId: bookmarkId || null };
  
  if (bookmarkId) {
    // 编辑模式：加载书签数据
    fetch(`nav-api.php?action=getBookmark&groupId=${encodeURIComponent(groupId)}&bookmarkId=${encodeURIComponent(bookmarkId)}`)
      .then(r => r.json())
      .then(bm => {
        document.getElementById('bmMT').textContent = '编辑书签';
        document.getElementById('bmU').value = bm.url;
        document.getElementById('bmN').value = bm.name;
        document.getElementById('bmD').value = bm.desc || '';
        
        let favicon = bm.favicon || '';
        if (favicon && /^https?:\/\//i.test(favicon) && !/\.(png|ico|svg|jpg|jpeg|webp|gif)$/i.test(favicon)) {
          favicon = getFaviconUrl(favicon);
        }
        
        document.getElementById('bmF').value = favicon;
        updateFaviconPreview(favicon || getFaviconUrl(bm.url));
        document.getElementById('bmM').classList.add('show');
        setTimeout(() => document.getElementById('bmU').focus(), 100);
      });
  } else {
    // 新增模式：清空表单
    document.getElementById('bmMT').textContent = '添加书签';
    document.getElementById('bmU').value = '';
    document.getElementById('bmN').value = '';
    document.getElementById('bmD').value = '';
    document.getElementById('bmF').value = '';
    document.getElementById('fP').innerHTML = '<span style="color:var(--text-tertiary)">输入网址后自动获取</span>';
    document.getElementById('bmM').classList.add('show');
    setTimeout(() => document.getElementById('bmU').focus(), 100);
  }
}

/**
 * 保存书签
 */
async function saveBookmark() {
  const url = document.getElementById('bmU').value.trim();
  const name = document.getElementById('bmN').value.trim();
  const desc = document.getElementById('bmD').value.trim();
  const favicon = document.getElementById('bmF').value.trim();
  
  if (!url || !name) {
    showToast('请输入网址和名称', 'error');
    return;
  }
  
  // 自动添加协议前缀
  const fullUrl = /^https?:\/\//i.test(url) ? url : 'https://' + url;
  
  // 处理favicon
  let faviconUrl = favicon;
  if (faviconUrl && /^https?:\/\//i.test(faviconUrl) && !/\.(png|ico|svg|jpg|jpeg|webp|gif)$/i.test(faviconUrl)) {
    faviconUrl = getFaviconUrl(faviconUrl);
  }
  if (!faviconUrl) faviconUrl = getFaviconUrl(fullUrl);
  
  const formData = new FormData();
  formData.append('action', 'saveBookmark');
  formData.append('groupId', editingBookmark.groupId);
  formData.append('bookmarkId', editingBookmark.bookmarkId || '');
  formData.append('url', fullUrl);
  formData.append('name', name);
  formData.append('desc', desc);
  formData.append('favicon', faviconUrl);
  
  try {
    const response = await fetch('nav-api.php', { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      closeModal('bmM');
      showToast(editingBookmark.bookmarkId ? '书签已更新' : '书签已添加', 'success');
      setTimeout(() => location.reload(), 500);
    } else {
      showToast('保存失败', 'error');
    }
  } catch (error) {
    showToast('保存失败', 'error');
  }
}

/**
 * 删除书签
 */
function confirmDeleteBookmark(groupId, bookmarkId) {
  fetch(`nav-api.php?action=getBookmark&groupId=${encodeURIComponent(groupId)}&bookmarkId=${encodeURIComponent(bookmarkId)}`)
    .then(r => r.json())
    .then(bm => {
      showConfirm('删除书签', `确定要删除书签"${bm.name}"吗？此操作不可撤销。`, {
        onConfirm: () => {
          apiFetch('deleteBookmark', { groupId, bookmarkId }).then(result => {
            if (result.success) {
              showToast('书签已删除', 'success');
              setTimeout(() => location.reload(), 500);
            } else {
              showToast('删除失败', 'error');
            }
          });
        }
      });
    });
}

// ═══════════════════════════════════════════════════════════════
// 分组管理
// ═══════════════════════════════════════════════════════════════

/**
 * 打开分组模态框（新增或编辑）
 */
function openGroupModal(groupId) {
  editingGroup = groupId || null;
  renderEmojiPicker();
  
  if (groupId) {
    // 编辑模式：加载分组数据
    fetch(`nav-api.php?action=getGroup&groupId=${encodeURIComponent(groupId)}`)
      .then(r => r.json())
      .then(group => {
        document.getElementById('gMT').textContent = '编辑分组';
        document.getElementById('gN').value = group.name;
        selectedEmoji = group.emoji;
        renderEmojiPicker();
        document.getElementById('gM').classList.add('show');
        setTimeout(() => document.getElementById('gN').focus(), 100);
      });
  } else {
    // 新增模式：清空表单
    document.getElementById('gMT').textContent = '添加分组';
    document.getElementById('gN').value = '';
    selectedEmoji = '📌';
    renderEmojiPicker();
    document.getElementById('gM').classList.add('show');
    setTimeout(() => document.getElementById('gN').focus(), 100);
  }
}

/**
 * 渲染emoji选择器
 */
function renderEmojiPicker() {
  document.getElementById('eP').innerHTML = EMOJIS.map(emoji =>
    `<button class="emoji-option ${emoji === selectedEmoji ? 'active' : ''}" onclick="selectedEmoji='${emoji}';renderEmojiPicker()">${emoji}</button>`
  ).join('');
}

/**
 * 保存分组
 */
async function saveGroup() {
  const name = document.getElementById('gN').value.trim();
  
  if (!name) {
    showToast('请输入分组名称', 'error');
    return;
  }
  
  const formData = new FormData();
  formData.append('action', 'saveGroup');
  formData.append('groupId', editingGroup || '');
  formData.append('name', name);
  formData.append('emoji', selectedEmoji);
  
  try {
    const response = await fetch('nav-api.php', { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      closeModal('gM');
      showToast(editingGroup ? '分组已更新' : '分组已添加', 'success');
      setTimeout(() => location.reload(), 500);
    } else {
      showToast('保存失败', 'error');
    }
  } catch (error) {
    showToast('保存失败', 'error');
  }
}

/**
 * 删除分组
 */
function confirmDeleteGroup(groupId) {
  fetch(`nav-api.php?action=getGroup&groupId=${encodeURIComponent(groupId)}`)
    .then(r => r.json())
    .then(group => {
      showConfirm('删除分组', `确定要删除分组"${group.name}"吗？此操作不可撤销。`, {
        input: group.name,
        onConfirm: () => {
          apiFetch('deleteGroup', { groupId }).then(result => {
            if (result.success) {
              showToast('分组已删除', 'success');
              setTimeout(() => location.reload(), 500);
            } else {
              showToast('删除失败', 'error');
            }
          });
        }
      });
    });
}

// ═══════════════════════════════════════════════════════════════
// 智能信息获取
// ═══════════════════════════════════════════════════════════════

/**
 * 更新favicon预览
 */
function updateFaviconPreview(url) {
  const preview = document.getElementById('fP');
  if (url) {
    preview.innerHTML = `<img src="${url}" alt="" onerror="this.style.display='none'"><span style="display:none;color:var(--text-tertiary)">无法加载</span>`;
  }
}

/**
 * 自动获取网站信息（标题、图标）
 */
async function fetchSiteInfo() {
  const urlInput = document.getElementById('bmU');
  let url = urlInput.value.trim();
  
  if (!url) return;
  
  // 自动添加协议前缀
  if (!/^https?:\/\//i.test(url)) {
    url = 'https://' + url;
    urlInput.value = url;
  }
  
  const fetchBtn = document.getElementById('fB');
  const domain = getDomain(url);
  
  // 显示加载状态
  fetchBtn.disabled = true;
  fetchBtn.innerHTML = '<span class="spinner"></span>';
  updateFaviconPreview(getFaviconUrl(url));
  
  // 检查缓存
  const cached = localStorage.getItem(CACHE_PREFIX + domain);
  if (cached) {
    try {
      const cacheData = JSON.parse(cached);
      if (Date.now() - cacheData.timestamp < CACHE_EXPIRY && cacheData.title) {
        document.getElementById('bmN').value = cacheData.title;
        if (cacheData.favicon) updateFaviconPreview(cacheData.favicon);
        fetchBtn.disabled = false;
        fetchBtn.textContent = '自动获取';
        return;
      }
    } catch (e) {}
  }
  
  // 使用多个代理尝试获取
  const proxies = [
    'https://api.allorigins.win/raw?url=',
    'https://corsproxy.io/?url=',
    'https://api.codetabs.com/v1/proxy?quest='
  ];
  
  let bestHtml = '';
  const results = await Promise.allSettled(proxies.map(async proxy => {
    try {
      const response = await fetch(proxy + encodeURIComponent(url), {
        signal: AbortSignal.timeout(8000),
        headers: { 'Accept': 'text/html', 'Accept-Language': 'zh-CN,zh;q=0.9' }
      });
      if (response.ok) {
        const html = await response.text();
        if (html.length > 100) return html;
      }
    } catch (e) {}
    return '';
  }));
  
  for (const result of results) {
    if (result.status === 'fulfilled' && result.value && result.value.length > bestHtml.length) {
      bestHtml = result.value;
    }
  }
  
  let title = '', favicon = '';
  
  if (bestHtml.length > 100) {
    const urlObj = new URL(url);
    const parser = new DOMParser().parseFromString(bestHtml, 'text/html');
    
    // 提取favicon
    const faviconSelectors = [
      'link[rel="icon"][type="image/svg+xml"]',
      'link[rel="icon"][sizes="192x192"]',
      'link[rel="apple-touch-icon"]',
      'link[rel="icon"]'
    ];
    
    for (const selector of faviconSelectors) {
      const element = parser.querySelector(selector);
      if (element && element.getAttribute('href')) {
        let href = element.getAttribute('href');
        if (href.startsWith('//')) href = 'https:' + href;
        else if (href.startsWith('/')) href = urlObj.origin + href;
        else if (!href.startsWith('http')) href = urlObj.origin + '/' + href;
        favicon = href;
        break;
      }
    }
    
    // 提取标题
    title = (
      parser.querySelector('meta[property="og:site_name"]')?.content ||
      parser.querySelector('meta[property="og:title"]')?.content ||
      parser.querySelector('title')?.textContent || ''
    ).trim().replace(/\s+/g, ' ');
    
    // 更新预览
    const manualFavicon = document.getElementById('bmF').value.trim();
    if (favicon && !manualFavicon) {
      updateFaviconPreview(favicon);
    } else if (manualFavicon) {
      updateFaviconPreview(manualFavicon);
    }
    
    // 缓存结果
    try {
      localStorage.setItem(CACHE_PREFIX + domain, JSON.stringify({
        title,
        favicon,
        timestamp: Date.now()
      }));
    } catch (e) {}
  }
  
  document.getElementById('bmN').value = title || domain;
  fetchBtn.disabled = false;
  fetchBtn.textContent = '自动获取';
}

// 输入监听
let fetchTimer = null;

/**
 * favicon输入框变化监听
 */
function onFaviconInput() {
  const value = document.getElementById('bmF').value.trim();
  if (value) {
    if (/\.(png|ico|svg|jpg|jpeg|webp|gif)$/i.test(value)) {
      updateFaviconPreview(value);
    } else if (/^https?:\/\//i.test(value)) {
      updateFaviconPreview(getFaviconUrl(value));
    } else {
      updateFaviconPreview(value);
    }
  } else {
    const url = document.getElementById('bmU').value.trim();
    if (url && /^https?:\/\//i.test(url)) {
      updateFaviconPreview(getFaviconUrl(url));
    }
  }
}

/**
 * URL输入框变化监听
 */
function onUrlInput() {
  clearTimeout(fetchTimer);
  const url = document.getElementById('bmU').value.trim();
  const favicon = document.getElementById('bmF').value.trim();
  
  if (url && /^https?:\/\//i.test(url) && !favicon) {
    updateFaviconPreview(getFaviconUrl(url));
  }
  
  // 自动触发获取（延迟1.2秒）
  fetchTimer = setTimeout(() => {
    if (url.length > 5 && favicon.length === 0) {
      fetchSiteInfo();
    }
  }, 1200);
}

/**
 * 重置favicon
 */
function resetFavicon() {
  const url = document.getElementById('bmU').value.trim();
  if (!url) {
    document.getElementById('bmF').value = '';
    document.getElementById('fP').innerHTML = '<span style="color:var(--text-tertiary)">输入网址后自动获取</span>';
    return;
  }
  document.getElementById('bmF').value = '';
  updateFaviconPreview(getFaviconUrl(url));
}

// ═══════════════════════════════════════════════════════════════
// API调用封装
// ═══════════════════════════════════════════════════════════════

/**
 * 通用API调用函数
 */
async function apiFetch(action, params) {
  const formData = new FormData();
  formData.append('action', action);
  for (const [key, value] of Object.entries(params)) {
    formData.append(key, value);
  }
  const response = await fetch('nav-api.php', { method: 'POST', body: formData });
  return response.json();
}

// ═══════════════════════════════════════════════════════════════
// 确认对话框
// ═══════════════════════════════════════════════════════════════

let confirmCallback = null;

/**
 * 显示确认对话框
 */
function showConfirm(title, message, options) {
  document.getElementById('cfMT').textContent = title;
  document.getElementById('cfMM').textContent = message;
  
  const inputSection = document.getElementById('cfMI');
  const confirmBtn = document.getElementById('cfMB');
  
  if (options && options.input) {
    // 需要输入确认文字
    inputSection.style.display = 'block';
    document.getElementById('cfME').textContent = options.input;
    document.getElementById('cfMV').value = '';
    confirmBtn.textContent = '确认删除';
    setTimeout(() => document.getElementById('cfMV').focus(), 200);
  } else {
    // 简单确认
    inputSection.style.display = 'none';
    confirmBtn.textContent = '确认';
  }
  
  confirmCallback = options && options.onConfirm ? options.onConfirm : null;
  document.getElementById('cfM').classList.add('show');
}

/**
 * 确认按钮点击处理
 */
function handleConfirm() {
  const inputSection = document.getElementById('cfMI');
  
  if (inputSection.style.display !== 'none') {
    const inputValue = document.getElementById('cfMV').value.trim();
    const expectedText = document.getElementById('cfME').textContent;
    
    if (inputValue !== expectedText) {
      showToast('输入不匹配', 'error');
      return;
    }
  }
  
  closeModal('cfM');
  if (confirmCallback) confirmCallback();
}

// ═══════════════════════════════════════════════════════════════
// 拖拽排序（仅管理员模式）
// ═══════════════════════════════════════════════════════════════
<?php if ($adminMode): ?>
(function() {
  let draggedItem = null;
  const items = document.querySelectorAll('.bookmark-item');
  const rows = document.querySelectorAll('.bookmarks-row');
  const groups = document.querySelectorAll('.group-section');

  // 为每个书签项添加拖拽功能
  items.forEach(item => {
    if (!item.dataset.bookmarkId) return;
    
    item.setAttribute('draggable', 'true');
    
    item.addEventListener('dragstart', e => {
      draggedItem = item;
      item.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', item.dataset.bookmarkId);
    });
    
    item.addEventListener('dragend', () => {
      item.classList.remove('dragging');
      items.forEach(i => i.classList.remove('drag-over'));
      rows.forEach(r => r.classList.remove('drag-over-row'));
      groups.forEach(g => g.classList.remove('drag-over-group'));
    });
  });

  // 书签项之间的拖拽
  items.forEach(item => {
    if (!item.dataset.bookmarkId) return;
    
    item.addEventListener('dragover', e => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      if (item !== draggedItem) item.classList.add('drag-over');
    });
    
    item.addEventListener('dragleave', () => {
      item.classList.remove('drag-over');
    });
    
    item.addEventListener('drop', e => {
      e.preventDefault();
      if (!draggedItem || item === draggedItem) return;
      
      const bookmarkId = draggedItem.dataset.bookmarkId;
      const sourceGroup = draggedItem.dataset.group;
      const targetGroup = item.dataset.group;
      const targetBookmark = item.dataset.bookmarkId;
      
      const formData = new FormData();
      formData.append('action', 'moveBookmark');
      formData.append('bookmarkId', bookmarkId);
      formData.append('sourceGroup', sourceGroup);
      formData.append('targetGroup', targetGroup);
      formData.append('targetBookmark', targetBookmark);
      
      fetch('nav-api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(result => {
          if (result.success) {
            showToast('书签已移动', 'success');
            setTimeout(() => location.reload(), 500);
          } else {
            showToast('移动失败', 'error');
          }
        })
        .catch(() => showToast('移动失败', 'error'));
    });
  });

  // 行级别的拖拽（拖到空行）
  rows.forEach(row => {
    row.addEventListener('dragover', e => {
      e.preventDefault();
      if (!row.contains(draggedItem)) row.classList.add('drag-over-row');
    });
    
    row.addEventListener('dragleave', e => {
      if (!row.contains(e.relatedTarget)) row.classList.remove('drag-over-row');
    });
    
    row.addEventListener('drop', e => {
      e.preventDefault();
      if (!draggedItem || row.contains(draggedItem)) return;
      
      const bookmarkId = draggedItem.dataset.bookmarkId;
      const sourceGroup = draggedItem.dataset.group;
      const targetGroup = row.closest('.group-section').dataset.groupId;
      
      const formData = new FormData();
      formData.append('action', 'moveBookmark');
      formData.append('bookmarkId', bookmarkId);
      formData.append('sourceGroup', sourceGroup);
      formData.append('targetGroup', targetGroup);
      
      fetch('nav-api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(result => {
          if (result.success) {
            showToast('书签已移动', 'success');
            setTimeout(() => location.reload(), 500);
          } else {
            showToast('移动失败', 'error');
          }
        })
        .catch(() => showToast('移动失败', 'error'));
    });
  });
})();
<?php endif; ?>

// ═══════════════════════════════════════════════════════════════
// 目录导航（FAB按钮）
// ═══════════════════════════════════════════════════════════════

// 高亮当前可见的分组
const tocItems = document.querySelectorAll('.fab-toc-item[data-group]');
const groupSections = document.querySelectorAll('.group-section');

if (tocItems.length && groupSections.length && 'IntersectionObserver' in window) {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        tocItems.forEach(t => t.classList.remove('active'));
        const activeItem = document.querySelector(`.fab-toc-item[data-group="${entry.target.id}"]`);
        if (activeItem) activeItem.classList.add('active');
      }
    });
  }, { rootMargin: '-20% 0px -60% 0px', threshold: 0 });
  
  groupSections.forEach(section => observer.observe(section));
}

// 滚动触发的分组入场动画
if (groupSections.length && 'IntersectionObserver' in window) {
  const scrollObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        scrollObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.05, rootMargin: '0px 0px -50px 0px' });
  
  groupSections.forEach(section => scrollObserver.observe(section));
}
</script></body></html>