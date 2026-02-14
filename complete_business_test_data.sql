-- ========================================
-- 完整的 MES 业务流程测试数据
-- 租户7和8的完整订单、派工、发货、库存流程
-- ========================================

-- ========================================
-- 租户7的完整订单流程
-- ========================================

-- 订单1（租户7）
INSERT INTO `fa_mes_order` (`tenant_id`, `order_no`, `customer_id`, `order_name`, `total_quantity`, `production_progress`, `delivery_date`, `status`, `create_time`, `update_time`) VALUES
(7, 'ORD7-001', 1, '租户7客户A', 100, 0.00, UNIX_TIMESTAMP() + 86400 * 7, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单1的物料（3个物料）
INSERT INTO `fa_mes_order_material` (`tenant_id`, `order_id`, `material_id`, `required_quantity`, `stock_status`, `create_time`) VALUES
(7, 1, 1, 50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, 2, 500, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, 3, 1000, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单1的派工分配
INSERT INTO `fa_mes_allocation` (`tenant_id`, `order_id`, `order_material_id`, `process_id`, `quantity`, `status`, `create_time`, `update_time`) VALUES
(7, 1, 1, 50, 1, 25, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, 1, 2, 30, 2, 20, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, 1, 3, 10, 1, 15, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单1的发货
INSERT INTO `fa_mes_shipment` (`tenant_id`, `shipment_no`, `customer_id`, `order_id`, `quantity`, `ship_date`, `status`, `create_time`, `update_time`) VALUES
(7, 'SHP7-001', 1, 1, 50, UNIX_TIMESTAMP() + 86400 * 3, 'shipped', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 发货明细（2个物料）
INSERT INTO `fa_mes_shipment_item` (`tenant_id`, `shipment_id`, `order_id`, `material_id`, `quantity`, `create_time`, `update_time`) VALUES
(7, 1, 1, 1, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, 1, 2, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单1的库存出库
INSERT INTO `fa_mes_stock_out` (`tenant_id`, `out_no`, `order_id`, `material_id`, `out_quantity`, `status`, `create_time`, `update_time`) VALUES
(7, 'OUT7-001', 1, 1, 1, 20, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单1的库存流水
INSERT INTO `fa_mes_stock_log` (`tenant_id`, `material_id`, `quantity`, `business_type`, `business_id`, `operator_id`, `remark`, `create_time`, `update_time`) VALUES
(7, 1, -20, 'production_out', 0, 1, '生产领料：20个钢板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 2, -500, 'purchase_in', 0, 1, '采购入库：500个螺丝', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, -50, 'production_out', 0, 1, '生产领料：50个钢板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ========================================
-- 租户8的完整订单流程
-- ========================================

-- 订单2（租户8）
INSERT INTO `fa_mes_order` (`tenant_id`, `order_no`, `customer_id`, `order_name`, `total_quantity`, `production_progress`, `delivery_date`, `status`, `create_time`, `update_time`) VALUES
(8, 'ORD8-001', 1, '租户8客户A', 100, 0.00, UNIX_TIMESTAMP() + 86400 * 7, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单2的物料（3个物料）
INSERT INTO `fa_mes_order_material` (`tenant_id`, `order_id`, `material_id`, `required_quantity`, `stock_status`, `create_time`) VALUES
(8, 1, 1, 50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, 2, 500, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, 3, 1000, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单2的派工分配
INSERT INTO `fa_mes_allocation` (`tenant_id`, `order_id`, `order_material_id`, `process_id`, `quantity`, `status`, `create_time`, `update_time`) VALUES
(8, 1, 1, 50, 1, 25, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, 1, 2, 30, 2, 20, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, 1, 3, 10, 1, 15, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单2的发货
INSERT INTO `fa_mes_shipment` (`tenant_id`, `shipment_no`, `customer_id`, `order_id`, `quantity`, `ship_date`, `status`, `create_time`, `update_time`) VALUES
(8, 'SHP8-001', 1, 1, 50, UNIX_TIMESTAMP() + 86400 * 3, 'shipped', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 发货明细（2个物料）
INSERT INTO `fa_mes_shipment_item` (`tenant_id`, `shipment_id`, `order_id`, `material_id`, `quantity`, `create_time`, `update_time`) VALUES
(8, 1, 1, 1, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, 1, 2, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单2的库存出库
INSERT INTO `fa_mes_stock_out` (`tenant_id`, `out_no`, `order_id`, `material_id`, `out_quantity`, `status`, `create_time`, `update_time`) VALUES
(8, 'OUT8-001', 1, 1, 1, 20, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 订单2的库存流水
INSERT INTO `fa_mes_stock_log` (`tenant_id`, `material_id`, `quantity`, `business_type`, `business_id`, `operator_id`, `remark`, `create_time`, `update_time`) VALUES
(8, 1, -20, 'production_out', 0, 1, '生产领料：20个钢板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 2, -500, 'purchase_in', 0, 1, '采购入库：500个螺丝', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, -50, 'production_out', 0, 1, '生产领料：50个钢板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ========================================
-- 租户7的补充工序工价数据
-- ========================================

-- 工序（租户7）
INSERT INTO `fa_mes_process` (`tenant_id`, `name`, `status`, `sort`, `create_time`, `update_time`) VALUES
(7, '租户7-切割A', 1, 91, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7-焊接A', 1, 92, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7-质检A', 1, 93, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 工序工价
INSERT INTO `fa_mes_process_price` (`tenant_id`, `process_id`, `product_model_id`, `price`, `status`, `create_time`, `update_time`) VALUES
(7, 1, 15.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 2, 15.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 3, 12.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ========================================
-- 租户8的补充工序工价数据
-- ========================================

-- 工序（租户8）
INSERT INTO `fa_mes_process` (`tenant_id`, `name`, `status`, `sort`, `create_time`, `update_time`) VALUES
(8, '租户8-切割A', 1, 91, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8-焊接A', 1, 92, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8-质检A', 1, 93, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 工序工价
INSERT INTO `fa_mes_process_price` (`tenant_id`, `process_id`, `product_model_id`, `price`, `status`, `create_time`, `update_time`) VALUES
(8, 1, 15.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 2, 15.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 3, 12.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
