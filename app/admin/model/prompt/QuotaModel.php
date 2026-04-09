<?php
declare(strict_types=1);
namespace app\admin\model\prompt;
use think\Model;
use think\facade\Db;

class QuotaModel extends Model {
    protected $name = 'prompt_quota';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = false;

    /**
     * 获取用户额度（不存在则自动初始化）
     */
    public static function getOrCreate(int $userId): self
    {
        $quota = static::where('user_id', $userId)->find();
        if (!$quota) {
            $freeQuota = (int) (Db::name('config')->where('name', 'prompt_free_quota')->value('value') ?: 5);
            $quota = new static();
            $quota->user_id    = $userId;
            $quota->free_quota = $freeQuota;
            $quota->paid_quota = 0;
            $quota->total_used = 0;
            $quota->create_time = time();
            $quota->update_time = time();
            $quota->save();
        }
        return $quota;
    }

    /** 总可用次数 */
    public function getTotalAvailable(): int
    {
        return (int)$this->free_quota + (int)$this->paid_quota;
    }

    /** 消耗1次额度（免费优先，不够则扣付费） */
    public function consume(): bool
    {
        if ($this->free_quota > 0) {
            $this->free_quota -= 1;
        } elseif ($this->paid_quota > 0) {
            $this->paid_quota -= 1;
        } else {
            return false;
        }
        $this->total_used += 1;
        $this->update_time = time();
        $this->save();
        return true;
    }
}
