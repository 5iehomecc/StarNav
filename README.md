# StarNav

> 一个轻量、美观、高度可定制的个人导航页面

## 项目简介

**StarNav** 是一个基于 PHP + JSON 的轻量级个人导航页面，支持书签分组管理、主题切换、书签拖拽移动、分组拖拽排序、数据导出等功能。无需数据库，单文件部署即可使用。

## 主要特性

### 界面设计
- **双主题切换**：深色和浅色双主题，自动记忆用户偏好
- **简洁配色**：黑白灰主色调，无多余特效，专注内容
- **响应式布局**：左侧分组导航 + 右侧书签内容，完美适配桌面和移动端
- **系统字体栈**：无需加载外部字体，秒开页面

### 书签管理
- **分组管理**：自定义分组 Emoji 图标（32 个可选）、分组重命名/删除
- **书签增删改查**：支持网址、名称、图标地址
- **智能 Favicon 抓取**：自动获取网站图标，支持手动输入直接图片 URL
- **离线缓存**：抓取的书签信息 localStorage 缓存 7 天
- **简洁卡片布局**：图标 + 名称一行展示

### 拖拽排序
- **分组拖拽**：管理员模式下可直接拖拽分组卡片改变排序
- **书签拖拽**：组内拖拽调整位置，跨组拖拽移动书签到任意分组（包括空分组）
- **两侧同步**：左侧导航栏和右侧主区域拖拽顺序完全同步
- **实时保存**：拖拽后自动同步到服务器
- **视觉反馈**：拖拽时半透明高亮、目标位置提示

### 数据导出
- **Bookmarks HTML**：浏览器收藏夹兼容格式（Netscape Bookmark），支持导入到 Chrome/Firefox/Edge
- **JSON 备份**：完整的 JSON 数据备份，包含分组、书签等全部信息

### 管理员功能
- **密码登录**：SHA-256 加密存储
- **会话管理**：PHP Session 身份验证
- **CRUD 操作**：完整的分组/书签增删改查

## 文件结构

```
.
├── nav-page.php      # 前端主页面（PHP 服务端渲染 + 前端交互脚本）
├── nav-api.php       # 后端 API 接口（登录、分组/书签 CRUD、拖拽排序）
├── nav-data.json     # 数据存储文件（JSON 格式）
├── .htaccess         # Apache 安全配置（阻止直接访问 JSON 文件）
├── .gitignore        # Git 忽略配置
└── README.md         # 本文档
```

## 快速开始

### 环境要求
- PHP >= 7.4
- 现代浏览器（Chrome 80+、Edge 80+、Firefox 75+、Safari 13+）

### 部署步骤

1. **克隆仓库**
   ```bash
   git clone https://github.com/5iehomecc/StarNav.git
   cd StarNav
   ```

2. **启动 PHP 内置服务器**（开发环境）
   ```bash
   php -S 0.0.0.0:8000
   ```

3. **访问页面**
   打开浏览器访问 `http://localhost:8000/nav-page.php`

4. **生产环境部署**
   - 将文件上传到支持 PHP 的服务器
   - 确保 `nav-data.json` 可写（权限 666 或 777）
   - 访问 `nav-page.php` 即可

### Nginx 配置示例

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/StarNav;
    index nav-page.php;

    location / {
        try_files $uri $uri/ /nav-page.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## 管理员使用

### 首次使用

1. 打开页面，点击左侧底部"管理员登录"
2. 输入管理员密码
3. 登录后即可添加分组、添加书签

### 拖拽操作
- **分组排序**：在左侧导航栏或右侧分组区域直接拖拽分组
- **书签移动**：拖拽书签卡片到同组其他位置，或拖到任意分组（包括空分组）
- 两侧拖拽顺序自动同步

### 数据导出
- 管理员模式下左侧显示"数据导出"按钮
- 点击后选择导出格式（Bookmarks HTML 或 JSON）
- 点击"开始导出"即可下载文件

### 修改密码

密码以 SHA-256 哈希值存储在 `nav-data.json` 的 `adminHash` 字段中。

修改密码步骤：
1. 编辑 `nav-data.json` 中的 `adminHash` 字段
2. 使用以下 PHP 代码生成新哈希：
   ```php
   <?php
   $p = 'your_new_password';
   echo hash('sha256', $p . '_starnav_salt_2024');
   ?>
   ```
3. 将生成的哈希值替换 `adminHash` 字段

## 版本历史

| 版本 | 发布日期 | 主要更新 |
|------|----------|----------|
| v2.3.0 | 2026-06-28 | 代码架构重构、模块化JavaScript、修复Favicon降级和Session问题、性能优化、详细代码注释 |
| v2.2.0 | 2026-06-28 | 字号图标增大、主题持久化修复、拖拽功能恢复、代码架构优化 |
| v2.1.0 | 2026-06-08 | UI 设计优化（via Trae Design） |
| v2.0.0 | 2026-06-06 | 全新架构：书签拖拽移动、两侧同步、emoji 图标选择器、移除外部字体依赖 |
| v1.7.0 | 2026-06-02 | 布局重构：分组拖拽排序、书签简洁布局、数据导出（HTML/JSON） |
| v1.6.0 | 2026-06-02 | 代码架构优化、加载速度提升、管理员密码更新 |
| v1.5.0 | 2026-06-01 | 图标优先级优化、Logo 对比度增强 |
| v1.4.0 | 2026-05-31 | 配色全面优化、视觉动效增强 |
| v1.3.0 | 2026-05-30 | 应用 Happy Hues Palette #10 配色 |
| v1.2.0 | 2026-05-29 | 初始版本发布 |

### 版本回滚

```bash
# 查看所有版本标签
git tag -l

# 回滚到指定版本
git checkout v1.7.0    # 或更早版本

# 或者创建回滚分支
git checkout -b rollback-v1.7.0 v1.7.0
```

## 技术栈

- **后端**：PHP（无框架，原生 PHP）
- **存储**：JSON 文件（替代传统数据库）
- **前端**：原生 HTML + CSS + JavaScript（无任何前端框架）
- **字体**：系统字体栈（PingFang SC / Microsoft YaHei / Segoe UI）
- **图标 API**：Google Favicon Service

## 开发指南

### 本地开发

```bash
# 启动开发服务器
php -S localhost:8000

# 调试模式
# 浏览器开发者工具 → Network → 勾选 Disable cache
```

### 调试技巧

1. **查看数据**：`cat nav-data.json | python3 -m json.tool`
2. **清空数据**：删除 `nav-data.json` 文件，下次访问时自动重建

## 安全说明

- 密码使用 SHA-256 哈希存储，**不可逆**
- PHP Session 用于身份验证，**不持久化到客户端**
- 所有写操作（增删改）都通过 `nav-api.php` 进行权限校验
- HTTP 安全头部已配置：
  - `X-Content-Type-Options: nosniff` 防止 MIME 嗅探
  - `X-Frame-Options: SAMEORIGIN` 防止点击劫持
  - `Cache-Control: no-store` 防止敏感信息缓存

## 贡献指南

欢迎提交 Issue 和 Pull Request！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交改动 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建 Pull Request

## 许可证

本项目采用 MIT 许可证 - 详见 [LICENSE](LICENSE) 文件

## 致谢

- 布局参考：[Startab](https://startab.cn/123)
- 图标 API：[Google Favicon Service](https://www.google.com/s2/favicons)

## 联系方式

- 项目地址：[https://github.com/5iehomecc/StarNav](https://github.com/5iehomecc/StarNav)
- 作者主页：[https://www.5iehome.cc](https://www.5iehome.cc)

---

**StarNav v2.3.0** - Copyright © 2026 StarNav
