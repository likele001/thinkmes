<?php
declare(strict_types=1);

namespace app\admin\controller\hr;

use app\admin\controller\Backend;
use app\admin\model\hr\HrAttendanceModel;
use app\admin\model\hr\HrEmployeeModel;
use think\facade\View;
use think\Response;

class Attendance extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            $tenantId = $this->getTenantId();
            View::assign('employeeList', HrEmployeeModel::where('tenant_id', $tenantId)->where('status', 1)->order('no')->select());
            View::assign('title', '考勤打卡');
            return $this->fetchWithLayout('hr/attendance/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = HrAttendanceModel::with(['employee'])->order('day', 'desc')->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }
        $employeeId = $this->request->get('employee_id');
        if ($employeeId !== '' && $employeeId !== null) {
            $query->where('employee_id', (int) $employeeId);
        }
        $dayStart = $this->request->get('day_start');
        $dayEnd = $this->request->get('day_end');
        if ($dayStart !== '' && $dayStart !== null) {
            $query->where('day', '>=', $dayStart);
        }
        if ($dayEnd !== '' && $dayEnd !== null) {
            $query->where('day', '<=', $dayEnd);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['employee_name'] = $item['employee']['name'] ?? '-';
            $item['clock_in_text'] = $item['clock_in'] ? date('H:i', $item['clock_in']) : '-';
            $item['clock_out_text'] = $item['clock_out'] ? date('H:i', $item['clock_out']) : '-';
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }
}
