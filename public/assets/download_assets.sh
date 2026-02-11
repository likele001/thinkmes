#!/bin/bash
# 从国内 BootCDN 下载静态资源到本地（避免 jsDelivr 等国外 CDN 不可用）
# 执行: bash download_assets.sh  或  cd public/assets && bash download_assets.sh
set -e
BASE="$(cd "$(dirname "$0")" && pwd)"
LIB="$BASE/lib"
mkdir -p "$LIB"/{jquery,bootstrap/css,bootstrap/js,adminlte/css,adminlte/js,bootstrap-table/css,bootstrap-table/js,layer,fontawesome/css,fontawesome/js,fontawesome/webfonts}

# 国内 BootCDN (七牛)
CDN="https://cdn.bootcdn.net/ajax/libs"

download() { if command -v curl >/dev/null 2>&1; then curl -sSL -o "$@"; else wget -q -O "$@"; fi; }

echo "Downloading jQuery..."
download "$LIB/jquery/jquery.min.js" "$CDN/jquery/3.6.0/jquery.min.js"

echo "Downloading Bootstrap..."
download "$LIB/bootstrap/css/bootstrap.min.css" "$CDN/twitter-bootstrap/5.1.3/css/bootstrap.min.css"
download "$LIB/bootstrap/js/bootstrap.bundle.min.js" "$CDN/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"

echo "Downloading AdminLTE..."
download "$LIB/adminlte/css/adminlte.min.css" "$CDN/admin-lte/3.2.0/css/adminlte.min.css"
download "$LIB/adminlte/js/adminlte.min.js" "$CDN/admin-lte/3.2.0/js/adminlte.min.js"

echo "Downloading Bootstrap Table..."
download "$LIB/bootstrap-table/css/bootstrap-table.min.css" "$CDN/bootstrap-table/1.21.4/bootstrap-table.min.css"
download "$LIB/bootstrap-table/js/bootstrap-table.min.js" "$CDN/bootstrap-table/1.21.4/bootstrap-table.min.js"

echo "Downloading Layer..."
download "$LIB/layer/layer.js" "$CDN/layer/3.5.1/layer.js"

echo "Downloading Font Awesome..."
download "$LIB/fontawesome/css/all.min.css" "$CDN/font-awesome/6.4.0/css/all.min.css"
download "$LIB/fontawesome/js/all.min.js" "$CDN/font-awesome/6.4.0/js/all.min.js"
# 可选：webfonts 若 all.min.css 内引用的是相对路径 ../webfonts/ 则需下载
for f in fa-solid-900.woff2 fa-regular-400.woff2 fa-brands-400.woff2; do
  download "$LIB/fontawesome/webfonts/$f" "$CDN/font-awesome/6.4.0/webfonts/$f" 2>/dev/null || true
done

echo "Done. Static assets in $LIB"
