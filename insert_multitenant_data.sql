-- MES 系统测试数据 - 多租户版本
-- 租户7和租户8分别测试数据

-- ============================================
-- 租户7的数据
-- ============================================

-- 租户7的供应商数据
INSERT INTO `fa_mes_supplier` (`tenant_id`, `name`, `code`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(7, '租户7-华东供应商', 'S701', '张三7', '13800138007', '上海市浦东新区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7-北方供应商', 'S702', '李四7', '13900139007', '北京市朝阳区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7-华南供应商', 'S703', '王五7', '15800139007', '广州市天河区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的仓库数据
INSERT INTO `fa_mes_warehouse` (`tenant_id`, `name`, `code`, `address`, `manager_id`, `status`, `is_default`, `create_time`, `update_time`) VALUES
(7, '租户7主仓', 'WH701', '上海市松江区', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7分仓A', 'WH702', '上海市闵行区', 1, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的物料数据
INSERT INTO `fa_mes_material` (`tenant_id`, `warehouse_id`, `name`, `code`, `unit`, `stock`, `min_stock`, `current_price`, `status`, `create_time`, `update_time`) VALUES
(7, 1, '钢板7', 'MAT701', '吨', 100, 50, 3500.00, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, '螺丝7', 'MAT702', '个', 5000, 1000, 0.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 1, '塑料颗粒7', 'MAT703', '公斤', 2000, 500, 8.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的客户数据
INSERT INTO `fa_mes_customer` (`tenant_id`, `customer_name`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(7, '租户7客户A', '周总7', '13800138887', '上海市黄浦区', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7客户B', '吴总7', '13900139887', '北京市海淀区', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的工序数据
INSERT INTO `fa_mes_process` (`tenant_id`, `name`, `code`, `status`, `sort`, `create_time`, `update_time`) VALUES
(7, '租户7-切割', 'PROC701', 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7-焊接', 'PROC702', 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7-组装', 'PROC703', 1, 97, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的产品型号数据
INSERT INTO `fa_mes_product_model` (`tenant_id`, `name`, `code`, `status`, `sort`, `create_time`, `update_time`) VALUES
(7, '租户7型A', 'MODEL701', 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '租户7型B', 'MODEL702', 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的订单数据
INSERT INTO `fa_mes_order` (`tenant_id`, `order_no`, `customer_id`, `order_name`, `total_quantity`, `production_progress`, `delivery_time`, `status`, `create_time`, `update_time`) VALUES
(7, 'ORD7-20250201', 1, '租户7订单A', 100, 0.00, UNIX_TIMESTAMP() + 86400 * 7, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 'ORD7-20250202', 2, '租户7订单B', 200, 0.00, UNIX_TIMESTAMP() + 86400 * 10, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的订单物料数据
INSERT INTO `fa_mes_order_material` (`tenant_id`, `order_id`, `material_id`, `required_quantity`, `stock_status`, `create_time`) VALUES
(7, 1, 1, 50, 1, UNIX_TIMESTAMP()),
(7, 1, 2, 500, 1, UNIX_TIMESTAMP()),
(7, 2, 1, 50, 1, UNIX_TIMESTAMP()),
(7, 2, 2, 200, 1, UNIX_TIMESTAMP());

-- 租户7的工序工价数据
INSERT INTO `fa_mes_process_price` (`tenant_id`, `process_id`, `product_model_id`, `price`, `status`, `create_time`, `update_time`) VALUES
(7, 1, 1, 15.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 2, 1, 8.50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的库存出库数据
INSERT INTO `fa_mes_stock_out` (`tenant_id`, `out_no`, `order_id`, `material_id`, `out_quantity`, `status`, `create_time`, `update_time`) VALUES
(7, 'OUT7-20250201', 1, 1, 20, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户7的库存流水数据
INSERT INTO `fa_mes_stock_log` (`tenant_id`, `material_id`, `quantity`, `business_type`, `business_id`, `operator_id`, `remark`, `create_time`, `update_time`) VALUES
(7, 1, -20, 'production_out', 0, 1, '租户7领料：20个钢板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 2, -500, 'production_out', 0, 1, '租户7领料：500个螺丝', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());


-- ============================================
-- 租户8的数据
-- ============================================

-- 租户8的供应商数据
INSERT INTO `fa_mes_supplier` (`tenant_id`, `name`, `code`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(8, '租户8-华东供应商', 'S801', '张三8', '13800138008', '上海市浦东新区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8-北方供应商', 'S802', '李四8', '13900139008', '北京市朝阳区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8-华南供应商', 'S803', '王五8', '15800139008', '广州市天河区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的仓库数据
INSERT INTO `fa_mes_warehouse` (`tenant_id`, `name`, `code`, `address`, `manager_id`, `status`, `is_default`, `create_time`, `update_time`) VALUES
(8, '租户8主仓', 'WH801', '上海市松江区', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8分仓A', 'WH802', '上海市闵行区', 1, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的物料数据
INSERT INTO `fa_mes_material` (`tenant_id`, `warehouse_id`, `name`, `code`, `unit`, `stock`, `min_stock`, `current_price`, `status`, `create_time`, `update_time`) VALUES
(8, 1, '钢板8', 'MAT801', '吨', 100, 50, 3500.00, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, '螺丝8', 'MAT802', '个', 5000, 1000, 0.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 1, '塑料颗粒8', 'MAT803', '公斤', 2000, 500, 8.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的客户数据
INSERT INTO `fa_mes_customer` (`tenant_id`, `customer_name`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(8, '租户8客户A', '周总8', '13800138888', '上海市黄浦区', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8客户B', '吴总8', '13900139888', '北京市海淀区', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的工序数据
INSERT INTO `fa_mes_process` (`tenant_id`, `name`, `code`, `status`, `sort`, `create_time`, `update_time`) VALUES
(8, '租户8-切割', 'PROC801', 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8-焊接', 'PROC802', 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8-组装', 'PROC803', 1, 97, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的产品型号数据
INSERT INTO `fa_mes_product_model` (`tenant_id`, `name`, `code`, `status`, `sort`, `create_time`, `update_time`) VALUES
(8, '租户8型A', 'MODEL801', 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '租户8型B', 'MODEL802', 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的订单数据
INSERT INTO `fa_mes_order` (`tenant_id`, `order_no`, `customer_id`, `order_name`, `total_quantity`, `production_progress`, `delivery_time`, `status`, `create_time`, `update_time`) VALUES
(8, 'ORD8-20250201', 1, '租户8订单A', 100, 0.00, UNIX_TIMESTAMP() + 86400 * 7, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 'ORD8-20250202', 2, '租户8订单B', 200, 0.00, UNIX_TIMESTAMP() + 86400 * 10, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的订单物料数据
INSERT INTO `fa_mes_order_material` (`tenant_id`, `order_id`, `material_id`, `required_quantity`, `stock_status`, `create_time`) VALUES
(8, 1, 1, 50, 1, UNIX_TIMESTAMP()),
(8, 1, 2, 500, 1, UNIX_TIMESTAMP()),
(8, 2, 1, 50, 1, UNIX_TIMESTAMP()),
(8, 2, 2, 200, 1, UNIX_TIMESTAMP());

-- 租户8的工序工价数据
INSERT INTO `fa_mes_process_price` (`tenant_id`, `process_id`, `product_model_id`, `price`, `status`, `create_time`, `update_time`) VALUES
(8, 1, 1, 15.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 2, 1, 8.50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的库存出库数据
INSERT INTO `fa_mes_stock_out` (`tenant_id`, `out_no`, `order_id`, `material_id`, `out_quantity`, `status`, `create_time`, `update_time`) VALUES
(8, 'OUT8-20250201', 1, 1, 20, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户8的库存流水数据
INSERT INTO `fa_mes_stock_log` (`tenant_id`, `material_id`, `quantity`, `business_type`, `business_id`, `operator_id`, `remark`, `create_time`, `update_time`) VALUES
(8, 1, -20, 'production_out', 0, 1, '租户8领料：20个钢板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 2, -500, 'production_out', 0, 1, '租户8领料：500个螺丝', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
