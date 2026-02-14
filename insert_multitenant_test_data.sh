#!/bin/bash
# MES 系统多租户测试数据插入脚本

echo "开始插入租户7和租户8的测试数据..."

# 租户7的供应商
mysql -u thinkmes -p'123456' thinkmes << 'EOF'
INSERT INTO `fa_mes_supplier` (`tenant_id`, `name`, `code`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(7, '租户7-华东供应商', 'S701', '张三7', '13800138007', '上海市浦东新区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7-北方供应商', 'S702', '李四7', '13900139007', '北京市朝阳区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7-华南供应商', 'S703', '王五7', '15800139007', '广州市天河区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
EOF

# 租户8的供应商
mysql -u thinkmes -p'123456' thinkmes << 'EOF'
INSERT INTO `fa_mes_supplier` (`tenant_id`, `name`, `code`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(8, '租户8-华东供应商', 'S801', '张三8', '13800138008', '上海市浦东新区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8-北方供应商', 'S802', '李四8', '13900139008', '北京市朝阳区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8-华南供应商', 'S803', '王五8', '15800139008', '广州市天河区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
EOF

# 租户7的仓库
mysql -u thinkmes -p'123456' thinkmes << 'EOF'
INSERT INTO `fa_mes_warehouse` (`tenant_id`, `name`, `code`, `address`, `manager_id`, `status`, `is_default`, `create_time`, `update_time`) VALUES
(7, '租户7主仓', 'WH701', '上海市松江区', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7分仓A', 'WH702', '上海市闵行区', 1, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
EOF

# 租户8的仓库
mysql -u thinkmes -p'123456' thinkmes << 'EOF'
INSERT INTO `fa_mes_warehouse` (`tenant_id`, `name`, `code`, `address`, `manager_id`, `status`, `is_default`, `create_time`, `update_time`) VALUES
(8, '租户8主仓', 'WH801', '上海市松江区', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8分仓A', 'WH802', '上海市闵行区', 1, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
EOF

# 租户7的物料
mysql -u thinkmes -p'123456' thinkmes << 'EOF'
INSERT INTO `fa_mes_material` (`tenant_id`, `warehouse_id`, `name`, `code`, `unit`, `stock`, `min_stock`, `current_price`, `status`, `create_time`, `update_time`) VALUES
(7, 1, '钢板7', 'MAT701', '吨', 100, 50, 3500.00, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, '螺丝7', 'MAT702', '个', 5000, 1000, 0.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, '塑料颗粒7', 'MAT703', '公斤', 2000, 500, 8.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
EOF

# 租户8的物料
mysql -u thinkmes -p'123456' thinkmes << 'EOF'
INSERT INTO `fa_mes_material` (`tenant_id`, `warehouse_id`, `name`, `code`, `unit`, `stock`, `min_stock`, `current_price`, `status`, `create_time`, `update_time`) VALUES
(8, 1, '钢板8', 'MAT801', '吨', 100, 50, 3500.00, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, '螺丝8', 'MAT802', '个', 5000, 1000, 0.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, '塑料颗粒8', 'MAT803', '公斤', 2000, 500, 8.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
EOF

# 租户7的客户
mysql -u thinkmes -p'123456' thinkmes << 'EOF'
INSERT INTO `fa_mes_customer` (`tenant_id`, `customer_name`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(7, '租户7客户A', '周总7', '13800138887', '上海市黄浦区', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7客户B', '吴总7', '13900139887', '北京市海淀区', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
EOF

# 租户8的客户
mysql -u thinkmes -p'123456' thinkmes << 'EOF'
INSERT INTO `fa_mes_customer` (`tenant_id`, `customer_name`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(8, '租户8客户A', '周总8', '13800138888', '上海市黄浦区', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8客户B', '吴总8', '13900139888', '北京市海淀区', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
EOF

echo "✅ 多租户测试数据插入完成"
echo ""
echo "已插入数据："
echo "- 租户7：供应商3条，仓库2条，物料3条，客户2条"
echo "- 租户8：供应商3条，仓库2条，物料3条，客户2条"
echo ""
echo "现在可以使用不同租户（7和8）的账号登录测试数据隔离功能！"
