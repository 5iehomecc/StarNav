<?php
/**
 * StarNav API 后端接口
 * 
 * 功能：处理所有管理员操作（登录、分组管理、书签管理）
 * 数据格式：JSON
 * 认证方式：PHP Session
 * 
 * @version 2.3.0
 * @author StarNav Team
 */

// ═══════════════════════════════════════════════════════════════
// 会话配置
// ═══════════════════════════════════════════════════════════════
// 仅在会话未启动时配置并启动（防止从nav-page.php引入时重复调用）
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,      // 会话有效期：24小时
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),  // HTTPS环境下启用安全Cookie
        'httponly' => true,       // 防止JavaScript访问Cookie
        'samesite' => 'Lax'       // 防止CSRF攻击
    ]);
    session_start();
}

// ═══════════════════════════════════════════════════════════════
// 全局变量
// ═══════════════════════════════════════════════════════════════
$dataFile = __DIR__ . '/nav-data.json';

// ═══════════════════════════════════════════════════════════════
// 核心函数
// ═══════════════════════════════════════════════════════════════

/**
 * 加载数据文件
 * 如果文件不存在或格式错误，返回默认数据结构
 */
if (!function_exists('loadData')) {
    function loadData() {
        global $dataFile;
        if (file_exists($dataFile)) {
            $d = json_decode(file_get_contents($dataFile), true);
            if ($d && is_array($d['groups'] ?? null)) {
                return $d;
            }
        }
        return defaultData();
    }
}

/**
 * 返回默认数据结构
 */
if (!function_exists('defaultData')) {
    function defaultData() {
        return [
            'adminHash' => '44f61792d66021c0030fa37dca5162871345c525f61984b88fa1af16d8117672',
            'siteName' => 'E家导航',
            'siteDesc' => 'E家导航 - 最实用的经验，分享最需要的你',
            'groups' => [
                ['id' => 'default', 'name' => '默认', 'emoji' => '📌', 'bookmarks' => []]
            ]
        ];
    }
}

/**
 * 保存数据到JSON文件
 * 使用文件锁确保并发安全
 */
if (!function_exists('saveData')) {
    function saveData($d) {
        global $dataFile;
        file_put_contents(
            $dataFile,
            json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX  // 独占锁，防止并发写入
        );
    }
}

/**
 * 返回JSON响应并退出
 * @param bool $ok 操作是否成功
 * @param string $msg 错误消息（失败时）
 */
if (!function_exists('resp')) {
    function resp($ok, $msg = '') {
        echo json_encode($ok ? ['success' => true] : ['success' => false, 'message' => $msg]);
        exit;
    }
}

// ═══════════════════════════════════════════════════════════════
// 响应头设置
// ═══════════════════════════════════════════════════════════════
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate');

// ═══════════════════════════════════════════════════════════════
// 路由处理
// ═══════════════════════════════════════════════════════════════
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ───────────────────────────────────────────────────────────────
// 公开接口（无需登录）
// ───────────────────────────────────────────────────────────────

/**
 * 管理员登录
 * 验证密码哈希，设置Session
 */
if ($action === 'login') {
    $pw = $_POST['password'] ?? '';
    $d = loadData();
    $expectedHash = $d['adminHash'] ?? defaultData()['adminHash'];
    
    if ($pw && $pw === $expectedHash) {
        $_SESSION['admin'] = true;
        resp(true);
    } else {
        resp(false, '密码错误');
    }
}

/**
 * 管理员登出
 * 清除Session并重定向
 */
if ($action === 'logout') {
    unset($_SESSION['admin']);
    header('Location: nav-page.php');
    exit;
}

// ───────────────────────────────────────────────────────────────
// 管理员接口（需要登录验证）
// ───────────────────────────────────────────────────────────────

// 检查管理员权限
if (!($_SESSION['admin'] ?? false)) {
    resp(false, '未登录');
}

/**
 * 获取分组信息
 * GET参数：groupId
 */
if ($action === 'getGroup') {
    $gid = $_GET['groupId'] ?? '';
    foreach (loadData()['groups'] as $g) {
        if ($g['id'] === $gid) {
            echo json_encode([
                'success' => true,
                'id' => $g['id'],
                'name' => $g['name'],
                'emoji' => $g['emoji']
            ]);
            exit;
        }
    }
    resp(false, '分组不存在');
}

/**
 * 获取书签信息
 * GET参数：groupId, bookmarkId
 */
if ($action === 'getBookmark') {
    $gid = $_GET['groupId'] ?? '';
    $bid = $_GET['bookmarkId'] ?? '';
    
    foreach (loadData()['groups'] as $g) {
        if ($g['id'] === $gid) {
            foreach ($g['bookmarks'] as $b) {
                if ($b['id'] === $bid) {
                    echo json_encode([
                        'success' => true,
                        'id' => $b['id'],
                        'name' => $b['name'],
                        'url' => $b['url'],
                        'desc' => $b['desc'] ?? '',
                        'favicon' => $b['favicon'] ?? ''
                    ]);
                    exit;
                }
            }
        }
    }
    resp(false, '书签不存在');
}

/**
 * 保存分组（新增或更新）
 * POST参数：groupId(可选), name, emoji
 */
if ($action === 'saveGroup') {
    $gid = $_POST['groupId'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $emoji = $_POST['emoji'] ?? '📌';
    
    if (!$name) {
        resp(false, '分组名称不能为空');
    }
    
    $d = loadData();
    
    if ($gid) {
        // 更新现有分组
        foreach ($d['groups'] as &$g) {
            if ($g['id'] === $gid) {
                $g['name'] = $name;
                $g['emoji'] = $emoji;
                break;
            }
        }
        unset($g);
    } else {
        // 新增分组
        $d['groups'][] = [
            'id' => uniqid('g'),
            'name' => $name,
            'emoji' => $emoji,
            'bookmarks' => []
        ];
    }
    
    saveData($d);
    resp(true);
}

/**
 * 删除分组
 * POST参数：groupId
 */
if ($action === 'deleteGroup') {
    $gid = $_POST['groupId'] ?? '';
    $d = loadData();
    
    // 过滤掉目标分组
    $d['groups'] = array_values(array_filter($d['groups'], function($g) use ($gid) {
        return $g['id'] !== $gid;
    }));
    
    saveData($d);
    resp(true);
}

/**
 * 保存书签（新增或更新）
 * POST参数：groupId, bookmarkId(可选), url, name, desc, favicon
 */
if ($action === 'saveBookmark') {
    $gid = $_POST['groupId'] ?? '';
    $bid = $_POST['bookmarkId'] ?? '';
    $url = trim($_POST['url'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['desc'] ?? '');
    $favicon = $_POST['favicon'] ?? '';
    
    // 验证必填字段
    if (!$url || !$name) {
        resp(false, '网址和名称不能为空');
    }
    
    // 自动添加协议前缀
    if (!preg_match('/^https?:\/\//i', $url)) {
        $url = 'https://' . $url;
    }
    
    $d = loadData();
    $found = false;
    
    foreach ($d['groups'] as &$g) {
        if ($g['id'] === $gid) {
            if ($bid) {
                // 更新现有书签
                foreach ($g['bookmarks'] as &$b) {
                    if ($b['id'] === $bid) {
                        $b = [
                            'id' => $b['id'],
                            'name' => $name,
                            'url' => $url,
                            'desc' => $desc,
                            'favicon' => $favicon
                        ];
                        $found = true;
                        break;
                    }
                }
                unset($b);
            } else {
                // 新增书签
                $g['bookmarks'][] = [
                    'id' => uniqid('b'),
                    'name' => $name,
                    'url' => $url,
                    'desc' => $desc,
                    'favicon' => $favicon
                ];
                $found = true;
            }
            break;
        }
    }
    unset($g);
    
    if ($found) {
        saveData($d);
        resp(true);
    }
    resp(false, '分组不存在');
}

/**
 * 删除书签
 * POST参数：groupId, bookmarkId
 */
if ($action === 'deleteBookmark') {
    $gid = $_POST['groupId'] ?? '';
    $bid = $_POST['bookmarkId'] ?? '';
    $d = loadData();
    
    foreach ($d['groups'] as &$g) {
        if ($g['id'] === $gid) {
            // 过滤掉目标书签
            $g['bookmarks'] = array_values(array_filter($g['bookmarks'], function($b) use ($bid) {
                return $b['id'] !== $bid;
            }));
            break;
        }
    }
    unset($g);
    
    saveData($d);
    resp(true);
}

/**
 * 移动书签（拖拽功能）
 * POST参数：bookmarkId, sourceGroup, targetGroup, targetBookmark(可选)
 */
if ($action === 'moveBookmark') {
    $bid = $_POST['bookmarkId'] ?? '';
    $sourceGroup = $_POST['sourceGroup'] ?? '';
    $targetGroup = $_POST['targetGroup'] ?? '';
    $targetBookmark = $_POST['targetBookmark'] ?? '';
    
    $d = loadData();
    $bookmark = null;
    
    // 1. 从源分组移除书签
    foreach ($d['groups'] as &$g) {
        if ($g['id'] === $sourceGroup) {
            foreach ($g['bookmarks'] as $i => $b) {
                if ($b['id'] === $bid) {
                    $bookmark = $b;
                    array_splice($g['bookmarks'], $i, 1);
                    break;
                }
            }
            break;
        }
    }
    unset($g);
    
    if (!$bookmark) {
        resp(false, '书签不存在');
    }
    
    // 2. 插入到目标分组
    foreach ($d['groups'] as &$g) {
        if ($g['id'] === $targetGroup) {
            if ($targetBookmark) {
                // 插入到指定书签之前
                $insertPos = count($g['bookmarks']);
                foreach ($g['bookmarks'] as $i => $b) {
                    if ($b['id'] === $targetBookmark) {
                        $insertPos = $i;
                        break;
                    }
                }
                array_splice($g['bookmarks'], $insertPos, 0, [$bookmark]);
            } else {
                // 追加到末尾
                $g['bookmarks'][] = $bookmark;
            }
            break;
        }
    }
    unset($g);
    
    saveData($d);
    resp(true);
}

// 未知操作
resp(false, '未知操作');
