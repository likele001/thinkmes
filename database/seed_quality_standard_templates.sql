-- 多行业质检标准模板（tenant_id=0 表示系统模板，供租户选择复制后编辑）
-- 执行后可在「质检标准管理」中看到「行业模板」并点击「复制」生成到本租户

-- 清空已有系统模板（按名称前缀避免误删租户数据）
DELETE FROM `fa_mes_quality_standard` WHERE `tenant_id` = 0;

INSERT INTO `fa_mes_quality_standard` (`tenant_id`, `name`, `process_id`, `model_id`, `check_items`, `qualified_rate`, `status`, `create_time`, `update_time`) VALUES
-- ---------- 沙发厂 ----------
(0, '【沙发厂】裁皮工序-外观与尺寸', 0, 0, '[{"name":"皮革表面","standard":"无划痕、破损、色差"},{"name":"裁片尺寸","standard":"符合工艺单公差±2mm"},{"name":"裁片数量","standard":"与料单一致"},{"name":"皮纹方向","standard":"按工艺要求统一"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【沙发厂】缝纫工序-线迹与拼接', 0, 0, '[{"name":"线迹均匀","standard":"针距一致、无跳针"},{"name":"拼接对称","standard":"左右对称、缝线对齐"},{"name":"收口牢固","standard":"线头不外露、打结牢固"},{"name":"压线宽度","standard":"符合工艺要求"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【沙发厂】扪工工序-饱满与外观', 0, 0, '[{"name":"饱满度","standard":"填充均匀、无塌陷"},{"name":"扪面平整","standard":"无褶皱、无鼓包"},{"name":"扣位/装饰","standard":"位置正确、牢固"},{"name":"拉链/魔术贴","standard":"顺畅、无外露"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【沙发厂】木架工序-结构与牢度', 0, 0, '[{"name":"结构牢度","standard":"无松动、无异响"},{"name":"榫卯/连接","standard":"接合紧密、无开裂"},{"name":"打磨","standard":"无毛刺、棱角倒圆"},{"name":"尺寸","standard":"符合图纸公差"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【沙发厂】包装入库-成品与标识', 0, 0, '[{"name":"外观复检","standard":"无污渍、破损、配件齐全"},{"name":"包装防护","standard":"防尘防潮、固定牢靠"},{"name":"标识","standard":"型号、数量、日期清晰"},{"name":"合格证/说明书","standard":"随货齐全"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- ---------- 零件制作厂（机加工/冲压/焊接等） ----------
(0, '【零件厂】车削工序-尺寸与表面', 0, 0, '[{"name":"外圆/内孔尺寸","standard":"符合图纸公差"},{"name":"表面粗糙度","standard":"Ra 满足工艺要求"},{"name":"同轴度/圆度","standard":"符合图纸"},{"name":"无崩刃、毛刺","standard":"去毛刺、无磕碰"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【零件厂】铣削工序-形位与表面', 0, 0, '[{"name":"平面度/平行度","standard":"符合图纸"},{"name":"表面粗糙度","standard":"满足工艺要求"},{"name":"轮廓尺寸","standard":"符合图纸公差"},{"name":"无过切、欠切","standard":"边界清晰、无残留"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【零件厂】冲压工序-外观与尺寸', 0, 0, '[{"name":"外形尺寸","standard":"符合图纸或样件"},{"name":"毛刺","standard":"毛刺高度≤规定值"},{"name":"裂纹/拉伤","standard":"无裂纹、无异常拉伤"},{"name":"孔位/折弯","standard":"位置与角度符合要求"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【零件厂】焊接工序-焊缝与强度', 0, 0, '[{"name":"焊缝外观","standard":"无气孔、咬边、未熔合"},{"name":"焊缝尺寸","standard":"焊高/焊宽符合工艺"},{"name":"变形","standard":"变形量在允许范围内"},{"name":"探伤/强度","standard":"按工艺要求抽检"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【零件厂】电镀/表面处理', 0, 0, '[{"name":"镀层厚度","standard":"符合工艺要求"},{"name":"镀层结合力","standard":"无起皮、起泡"},{"name":"外观","standard":"均匀、无漏镀、无锈点"},{"name":"耐腐蚀","standard":"按标准抽检"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【零件厂】装配工序-配合与功能', 0, 0, '[{"name":"配合尺寸","standard":"间隙/过盈符合要求"},{"name":"紧固扭矩","standard":"按工艺要求"},{"name":"功能测试","standard":"动作正常、无异响"},{"name":"标识与防错","standard":"装配正确、无错漏装"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- ---------- 服装 ----------
(0, '【服装】裁剪工序-裁片与标记', 0, 0, '[{"name":"裁片尺寸","standard":"符合样板及公差"},{"name":"丝缕方向","standard":"按工艺要求"},{"name":"对条对格","standard":"按要求对位"},{"name":"裁片标记","standard":"刀口、钻眼清晰正确"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【服装】缝纫工序-线迹与外观', 0, 0, '[{"name":"线迹","standard":"均匀、无跳针、无浮线"},{"name":"缝份/止口","standard":"宽度一致、倒向正确"},{"name":"对称性","standard":"左右对称、长短一致"},{"name":"外观","standard":"无污渍、无抽丝"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【服装】整烫与包装', 0, 0, '[{"name":"整烫效果","standard":"平整、无烫痕、无极光"},{"name":"线头清理","standard":"无线头外露"},{"name":"吊牌/洗唛","standard":"位置正确、内容正确"},{"name":"包装","standard":"折叠规范、包装完整"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- ---------- 食品 ----------
(0, '【食品】原料验收', 0, 0, '[{"name":"感官","standard":"色泽、气味、形态正常"},{"name":"标签与证件","standard":"合格证、检疫证等齐全"},{"name":"保质期与储存","standard":"在保质期内、储存条件符合"},{"name":"农残/微生物","standard":"按标准抽检合格"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【食品】生产过程-卫生与关键控制点', 0, 0, '[{"name":"环境与人员卫生","standard":"符合GMP/SSOP要求"},{"name":"关键控制点","standard":"温度、时间、添加剂等符合工艺"},{"name":"设备清洁","standard":"无交叉污染"},{"name":"记录","standard":"关键参数记录完整"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【食品】成品检验-感官与理化', 0, 0, '[{"name":"感官","standard":"色泽、气味、滋味、组织状态正常"},{"name":"净含量","standard":"符合产品标准"},{"name":"理化指标","standard":"按产品标准检测"},{"name":"微生物","standard":"按产品标准检测"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【食品】包装与标签', 0, 0, '[{"name":"包装完整性","standard":"密封良好、无破损"},{"name":"标签内容","standard":"符合GB 7718等法规"},{"name":"生产日期与保质期","standard":"清晰、正确"},{"name":"贮存与运输","standard":"条件符合要求"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- ---------- 电子/装配（通用） ----------
(0, '【电子】PCBA 焊接与外观', 0, 0, '[{"name":"焊点","standard":"饱满、无虚焊、无连锡"},{"name":"元件极性/位号","standard":"正确、无错装漏装"},{"name":"外观","standard":"无破损、无污染"},{"name":"ICT/功能","standard":"按工艺抽检"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【电子】整机装配与功能', 0, 0, '[{"name":"装配","standard":"螺丝扭矩、卡扣到位"},{"name":"外观","standard":"无划伤、无脏污"},{"name":"功能测试","standard":"按检验规范通过"},{"name":"标识与包装","standard":"标贴正确、包装完整"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- ---------- 塑料/注塑 ----------
(0, '【注塑】外观与尺寸', 0, 0, '[{"name":"外观","standard":"无缩痕、熔接线、飞边超标"},{"name":"尺寸","standard":"符合图纸或样件"},{"name":"色差","standard":"与标准件一致"},{"name":"装配孔/配合","standard":"能正常装配"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- ---------- 通用（可作基础模板） ----------
(0, '【通用】外观检验模板', 0, 0, '[{"name":"外观缺陷","standard":"无划伤、破损、污渍"},{"name":"颜色/纹理","standard":"符合要求"},{"name":"标识","standard":"清晰、正确"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '【通用】尺寸检验模板', 0, 0, '[{"name":"关键尺寸","standard":"符合图纸或工艺要求"},{"name":"形位公差","standard":"符合图纸"},{"name":"记录","standard":"实测值记录完整"}]', 100.00, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
