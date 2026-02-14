-- MES 系统测试数据
-- 租户ID: 0 (平台)

-- 1. 供应商数据
INSERT INTO `fa_mes_supplier` (`tenant_id`, `name`, `code`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(0, '华东原料供应商', 'SUP001', '张三', '13800138000', '上海市浦东新区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '北方配件供应商', 'SUP002', '李四', '13900139000', '北京市朝阳区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '华南设备供应商', 'SUP003', '王五', '15800139000', '广州市天河区', 'inactive', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '西部物流供应商', 'SUP004', '赵六', '18600139000', '成都市高新区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '中部包装供应商', 'SUP005', '孙七', '13700139000', '武汉市洪山区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 2. 仓库数据
INSERT INTO `fa_mes_warehouse` (`tenant_id`, `name`, `code`, `address`, `manager_id`, `status`, `is_default`, `create_time`, `update_time`) VALUES
(0, '主仓库', 'WH001', '上海市松江区', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '分仓库A', 'WH002', '上海市闵行区', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '分仓库B', 'WH003', '上海市嘉定区', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 3. 物料数据
INSERT INTO `fa_mes_material` (`tenant_id`, `warehouse_id`, `name`, `code`, `spec`, `unit`, `stock`, `min_stock`, `current_price`, `status`, `create_time`, `update_time`) VALUES
(0, 1, '钢板', 'MAT001', 'Q235', '吨', 100, 50, 3500.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, '螺丝', 'MAT002', 'M8*20', '个', 5000, 1000, 0.50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, '塑料颗粒', 'MAT003', 'PE-100', '公斤', 2000, 500, 8.50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, '铜线', 'MAT004', 'CU-2.5', '米', 800, 200, 65.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 2, '橡胶垫片', 'MAT005', 'RB-20', '个', 3000, 800, 2.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 2, '铝板', 'MAT006', 'AL-5.0', '张', 1500, 300, 120.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, '不锈钢管', 'MAT007', 'SS-50', '根', 1200, 400, 85.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 4. 客户数据
INSERT INTO `fa_mes_customer` (`tenant_id`, `name`, `code`, `contact_person`, `contact_phone`, `address`, `status`, `create_time`, `update_time`) VALUES
(0, '华东贸易公司', 'CUST001', '周总', '13800138888', '上海市黄浦区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '北方制造企业', 'CUST002', '吴总', '13900139888', '北京市海淀区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '南方科技集团', 'CUST003', '郑总', '15800139888', '广州市番禺区', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 5. 工序数据
INSERT INTO `fa_mes_process` (`tenant_id`, `name`, `code`, `category`, `status`, `create_time`, `update_time`) VALUES
(0, '切割工序', 'PROC001', 'cutting', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '焊接工序', 'PROC002', 'welding', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '组装工序', 'PROC003', 'assembly', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '质检工序', 'PROC004', 'quality', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '包装工序', 'PROC005', 'packaging', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 6. 产品型号数据
INSERT INTO `fa_mes_product_model` (`tenant_id`, `name`, `code`, `status`, `create_time`, `update_time`) VALUES
(0, '标准型A', 'MODEL-A01', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '标准型B', 'MODEL-B01', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '定制型X', 'MODEL-X01', 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 7. 订单数据
INSERT INTO `fa_mes_order` (`tenant_id`, `order_no`, `customer_id`, `product_model_id`, `quantity`, `delivery_date`, `status`, `create_time`, `update_time`) VALUES
(0, 'ORD20250213001', 1, 1, 100, UNIX_TIMESTAMP() + 86400 * 7, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'ORD20250213002', 2, 2, 200, UNIX_TIMESTAMP() + 86400 * 10, 'in_production', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'ORD20250213003', 3, 3, 150, UNIX_TIMESTAMP() + 86400 * 5, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 8. 订单物料数据（关联订单和物料）
INSERT INTO `fa_mes_order_material` (`tenant_id`, `order_id`, `material_id`, `required_quantity`, `stock_status`, `create_time`, `update_time`) VALUES
(0, 1, 1, 50, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, 2, 500, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 2, 3, 300, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 2, 4, 400, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, 5, 100, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, 6, 150, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 9. 工序工价数据
INSERT INTO `fa_mes_process_price` (`tenant_id`, `process_id`, `product_model_id`, `price`, `status`, `create_time`, `update_time`) VALUES
(0, 1, 1, 15.00, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 2, 1, 8.50, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, 1, 12.00, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, 2, 18.00, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 4, 2, 10.00, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 10. 发货数据
INSERT INTO `fa_mes_shipment` (`tenant_id`, `shipment_no`, `customer_id`, `order_id`, `quantity`, `ship_date`, `status`, `create_time`, `update_time`) VALUES
(0, 'SHP20250213001', 1, 1, 50, UNIX_TIMESTAMP() + 86400 * 3, 'shipped', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'SHP20250213002', 2, 2, 100, UNIX_TIMESTAMP() + 86400 * 5, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 11. 发货明细数据
INSERT INTO `fa_mes_shipment_item` (`tenant_id`, `shipment_id`, `order_id`, `material_id`, `quantity`, `create_time`, `update_time`) VALUES
(0, 1, 1, 1, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, 1, 2, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 12. 采购入库数据
INSERT INTO `fa_mes_purchase_in` (`tenant_id`, `purchase_no`, `supplier_id`, `material_id`, `quantity`, `unit_price`, `in_date`, `status`, `create_time`, `update_time`) VALUES
(0, 'PUR20250213001', 1, 2, 500, 9.00, UNIX_TIMESTAMP() - 86400 * 5, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'PUR20250213002', 1, 3, 1000, 6.50, UNIX_TIMESTAMP() - 86400 * 3, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 13. 库存出库数据（生产领料）
INSERT INTO `fa_mes_stock_out` (`tenant_id`, `out_no`, `order_id`, `material_id`, `out_quantity`, `status`, `create_time`, `update_time`) VALUES
(0, 'OUT20250213001', 1, 1, 20, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'OUT20250213002', 2, 1, 3, 30, 'completed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 14. 库存流水数据
INSERT INTO `fa_mes_stock_log` (`tenant_id`, `material_id`, `quantity`, `business_type`, `business_id`, `operator_id`, `remark`, `create_time`, `update_time`) VALUES
(0, 2, -500, 'purchase_in', 0, 1, '采购入库：500个螺丝', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 3, -1000, 'purchase_in', 1, 1, '采购入库：1000个塑料颗粒', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 1, -20, 'production_out', 0, 1, '生产领料：20个钢板', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 4, -30, 'production_out', 1, 1, '生产领料：30个橡胶垫片', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 15. 采购请求数据
INSERT INTO `fa_mes_purchase_request` (`tenant_id`, `request_no`, `material_id`, `quantity`, `urgency_level`, `request_date`, `status`, `create_time`, `update_time`) VALUES
(0, 'REQ20250213001', 4, 500, 'normal', UNIX_TIMESTAMP() - 86400 * 2, 'pending', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'REQ20250213002', 5, 800, 'urgent', UNIX_TIMESTAMP() - 86400 * 1, 'approved', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
