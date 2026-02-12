<?php
declare(strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * 可选的基础模型，供模型继承以便统一放置未来公共逻辑。
 * 目前不强制自动注入 tenant 过滤，避免破坏现有行为。
 */
abstract class BaseModel extends Model
{
    use TenantScope;

    // 未来可在此处扩展全局行为
}
