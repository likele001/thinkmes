#!/usr/bin/env bash
# 工作流（wf_*）上线前自检：禁止旧 workflow 表/类引用；可选检查库中是否残留 fa_workflow* 表。
# 用法：在项目根目录执行  bash scripts/pre_release_wf_check.sh
# 可选：WF_MYSQL_CMD='mysql -h127.0.0.1 -uroot -pxxx dbname'  bash scripts/pre_release_wf_check.sh

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'
fail=0

scan_rg() {
  local label="$1"
  local pattern="$2"
  local out
  out="$(rg -l "$pattern" app --glob '*.php' 2>/dev/null || true)"
  if [[ -n "${out}" ]]; then
    echo -e "${RED}[FAIL]${NC} $label"
    echo "$out"
    fail=1
  else
    echo -e "${GREEN}[OK]${NC}   $label"
  fi
}

scan_grep() {
  local label="$1"
  local pattern="$2"
  local out
  out="$(grep -RIl --include='*.php' -E "$pattern" app 2>/dev/null || true)"
  if [[ -n "${out}" ]]; then
    echo -e "${RED}[FAIL]${NC} $label"
    echo "$out"
    fail=1
  else
    echo -e "${GREEN}[OK]${NC}   $label"
  fi
}

if command -v rg >/dev/null 2>&1; then
  scan_rg "禁止 Db::name('workflow_*')（旧表前缀）" 'Db::name\(\s*['\''"]workflow_'
  scan_rg "禁止 PHP 中出现 fa_workflow 字面量（旧表族）" 'fa_workflow'
  scan_rg "禁止引用已废弃旧工作流模型命名空间" 'app\\admin\\model\\Workflow(Definition|Instance|Node|Approval|Edge|Module|State|Transition)\b'
else
  echo -e "${YELLOW}[INFO]${NC} 未安装 ripgrep(rg)，使用 grep 降级扫描"
  scan_grep "禁止 Db::name('workflow_*')" "Db::name\\(['\"]workflow_"
  scan_grep "禁止 fa_workflow（PHP）" "fa_workflow"
  scan_grep "禁止旧工作流模型类名（use 或 new）" "(WorkflowDefinition|WorkflowInstance|WorkflowApprovalRecord|WorkflowEdge|WorkflowModule)\\b"
fi

if [[ -n "${WF_MYSQL_CMD:-}" ]]; then
  legacy="$(${WF_MYSQL_CMD} -N -e "
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name LIKE 'fa_workflow%';
  " 2>/dev/null || echo "err")"
  if [[ "$legacy" == "err" ]]; then
    echo -e "${RED}[WARN]${NC} 无法执行 MySQL 检查（请检查 WF_MYSQL_CMD）"
  elif [[ "${legacy:-0}" -gt 0 ]]; then
    echo -e "${RED}[FAIL]${NC} 当前库仍存在 fa_workflow* 表，共 ${legacy} 张（若已迁移删除请执行 migrate_drop_legacy_workflow 等脚本）"
    fail=1
  else
    echo -e "${GREEN}[OK]${NC}   数据库无 fa_workflow* 表"
  fi
else
  echo -e "${GREEN}[SKIP]${NC} 未设置 WF_MYSQL_CMD，跳过库表检查"
fi

if [[ "$fail" -ne 0 ]]; then
  echo -e "\n${RED}检查未通过，请修复后再发布。${NC}"
  exit 1
fi
echo -e "\n${GREEN}工作流 wf_* 上线前检查全部通过。${NC}"
exit 0
