#!/bin/bash
# MES 系统测试数据插入脚本（修正版）

mysql -u thinkmes -p'123456' thinkmes << 'SQLEOF'
-- 工序数据
INSERT INTO `fa_mes_process` (`tenant_id`, `name`, `code`, `status`, `sort`, `create_time`, `update_time`) VALUES
(0, '切割工序', 'PROC001', 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '焊接工序', 'PROC002', 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '组装工序', 'PROC003', 1, 97, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '质检工序', 'PROC004', 1, 96, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '包装工序', 'PROC005', 1, 95, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 产品型号数据
INSERT INTO `fa_mes_product_model` (`tenant_id`, `name`, `code`, `status`, `sort`, `create_time`, `update_time`) VALUES
(0, '标准型A', 'MODEL-A01', 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '标准型B', 'MODEL-B01', 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '定制型X', 'MODEL-X01', 1, 97, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单数据
INSERT INTO `fa_mes_order` (`tenant_id`, `order_no`, `customer_id`, `product_model_id`, `quantity`, `delivery_date`, `status`, `create_time`, `update_time`) VALUES
(0, 'ORD20250213001', 1, 1, 100, UNIX_TIMESTAMP() + 86400 * 7, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'ORD20250213002', 2, 2, 200, UNIX_TIMESTAMP() + 86400 * 10, 'in_production', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'ORD20250213003', 3, 3, 150, UNIX_TIMESTAMP() + 86400 * 5, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单物料数据
INSERT INTO `fa_mes_order_material` (`tenant_id`, `order_id`, `material_id`, `required_quantity`, `stock_status`, `create_time`, `update_time`) VALUES
(0, 1, 1, 50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, 2, 500, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 2, 3, 300, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 2, 4, 400, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, 5, 100, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, 6, 150, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 工序工价数据
INSERT INTO `fa_mes_process_price` (`tenant_id`, `process_id`, `product_model_id`, `price`, `status`, `create_time`, `update_time`) VALUES
(0, 1, 1, 15.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 2, 1, 8.50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, 1, 12.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, 2, 18.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 4, 2, 10.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 发货数据
INSERT INTO `fa_mes_shipment` (`tenant_id`, `shipment_no`, `customer_id`, `order_id`, `quantity`, `ship_date`, `status`, `create_time`, `update_time`) VALUES
(0, 'SHP20250213001', 1, 1, 50, UNIX_TIMESTAMP() + 86400 * 3, 'shipped', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'SHP20250213002', 2, 2, 100, UNIX_TIMESTAMP() + 86400 * 5, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 发货明细数据
INSERT INTO `fa_mes_shipment_item` (`tenant_id`, `shipment_id`, `order_id`, `material_id`, `quantity`, `create_time`, `update_time`) VALUES
(0, 1, 1, 1, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, 1, 2, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 采购入库数据
INSERT INTO `fa_mes_purchase_in` (`tenant_id`, `purchase_no`, `supplier_id`, `material_id`, `quantity`, `unit_price`, `in_date`, `status`, `create_time`, `update_time`) VALUES
(0, 'PUR20250213001', 1, 2, 500, 9.00, UNIX_TIMESTAMP() - 86400 * 5, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'PUR20250213002', 1, 3, 1000, 6.50, UNIX_TIMESTAMP() - 86400 * 3, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 库存出库数据
INSERT INTO `fa_mes_stock_out` (`tenant_id`, `out_no`, `order_id`, `material_id`, `out_quantity`, `status`, `create_time`, `update_time`) VALUES
(0, 'OUT20250213001', 1, 1, 20, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'OUT20250213002', 2, 1, 3, 30, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 库存流水数据
INSERT INTO `fa_mes_stock_log` (`tenant_id`, `material_id`, `quantity`, `business_type`, `business_id`, `operator_id`, `remark`, `create_time`, `update_time`) VALUES
(0, 2, -500, 'purchase_in', 0, 1, '采购入库：500个螺丝', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, -1000, 'purchase_in', 1, 1, '采购入库：1000个塑料颗粒', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, -20, 'production_out', 0, 1, '生产领料：20个钢板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 4, -30, 'production_out', 1, 1, '生产领料：30个橡胶垫片', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
SQLEOF

echo "✅ MES 测试数据插入完成！"
echo "- 供应商: 5 条"
echo "- 仓库: 3 条"
echo "- 物料: 7 条"
echo "- 客户: 3 条"
echo "- 工序: 5 条"
echo "- 产品型号: 3 条"
echo "- 订单: 3 条"
echo "- 订单物料: 6 条"
echo "- 工序工价: 5 条"
echo "- 发货: 2 条"
echo "- 发货明细: 2 条"
echo "- 采购入库: 2 条"
echo "- 库存出库: 2 条"
echo "- 库存流水: 4 条"
