-- MES 系统测试数据 - 供应商
-- 租户ID: 0 (平台)

-- 1. 供应商数据
INSERT INTO `fa_mes_supplier` (`tenant_id`, `name`, `code`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(0, '华东原料供应商', 'SUP001', '张三', '13800138000', '上海市浦东新区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '北方配件供应商', 'SUP002', '李四', '13900139000', '北京市朝阳区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '华南设备供应商', 'SUP003', '王五', '15800139000', '广州市天河区', 'inactive', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '西部物流供应商', 'SUP004', '赵六', '18600139000', '成都市高新区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '中部包装供应商', 'SUP005', '孙七', '13700139000', '武汉市洪山区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
