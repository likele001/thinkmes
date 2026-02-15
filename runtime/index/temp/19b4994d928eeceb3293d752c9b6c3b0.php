<?php /*a:1:{s:56:"/www/wwwroot/thinkmes/app/index/view/layout/default.html";i:1771124706;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlentities((string) (isset($title) && ($title !== '')?$title:'会员中心')); ?> - 报工系统</title>
  <script src="/assets/lib/jquery/jquery.min.js"></script>
  <script src="/assets/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="/assets/lib/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/lib/adminlte/css/adminlte.min.css">
  <link rel="stylesheet" href="/assets/css/user.css">
  <link rel="stylesheet" href="/assets/lib/fontawesome/css/all.min.css">
</head>
<body>
  <nav class="topbar">
    <div class="container d-flex align-items-center justify-content-between">
      <a class="topbar-brand" href="/">报工系统</a>
      <ul class="nav topbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link" href="/index/user/index"><i class="fa fa-home me-1"></i>会员中心</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fa fa-user me-1"></i>账号</a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/index/user/profile"><i class="fa fa-id-card me-1"></i>个人资料</a></li>
            <li><a class="dropdown-item" href="/index/user/changepwd"><i class="fa fa-key me-1"></i>修改密码</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/index/user/logout"><i class="fa fa-sign-out-alt me-1"></i>退出</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
  <div class="container container-main">
    <?php echo $__CONTENT__; ?>
  </div>
  <div class="footer">
    Copyright © 2026 报工系统 All Rights Reserved
  </div>
</body>
</html>
