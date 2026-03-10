<?php
declare(strict_types=1);

namespace app\common\model;

class WemediaComplianceLogModel extends BaseModel
{
    protected $name = 'wemedia_compliance_log';

    protected $type = [
        'tenant_id'   => 'integer',
        'user_id'     => 'integer',
        'result'      => 'integer',
        'create_time' => 'integer',
    ];

    const RESULT_OK = 0;
    const RESULT_VIOLATION = 1;

    public static function resultText(int $v): string
    {
        return $v === self::RESULT_VIOLATION ? '违规' : '合规';
    }
}
