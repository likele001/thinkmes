-- AI 提示词工坊 - 预设分类 + 模板种子数据
-- 使用 INSERT IGNORE 避免重复安装时插入重复数据

-- 分类
INSERT IGNORE INTO `fa_prompt_category` (`name`,`icon`,`sort`,`status`,`create_time`,`update_time`) VALUES
('写作创作', 'fas fa-pen-nib',    1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('营销文案', 'fas fa-bullhorn',   2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('编程开发', 'fas fa-code',       3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('办公效率', 'fas fa-briefcase',  4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('学习教育', 'fas fa-graduation-cap', 5, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('创意设计', 'fas fa-paint-brush', 6, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 模板（写作）
INSERT IGNORE INTO `fa_prompt_template` (`category_id`,`title`,`description`,`prompt_text`,`variables`,`system_prompt`,`sort`,`status`,`create_time`,`update_time`) VALUES
(1,'爆款标题生成','根据主题生成吸引人的标题',
'请为"{topic}"生成10个吸引眼球的标题，要求：\n1. 含有数字或对比词\n2. 引发好奇心或共鸣\n3. 简洁有力（15字以内）\n4. 适合{platform}平台',
'[{"name":"topic","label":"主题内容","placeholder":"例如：如何提高工作效率","required":true},{"name":"platform","label":"发布平台","placeholder":"微信公众号/抖音/小红书","required":false}]',
'你是一位爆款内容标题专家，擅长创作高点击率标题。', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(1,'文章大纲规划','快速生成文章框架结构',
'请为以下主题生成一篇{word_count}字的文章大纲：\n主题：{topic}\n目标读者：{audience}\n\n要求：\n- 逻辑清晰，层次分明\n- 包含引言、正文（3-5个要点）、结尾\n- 每个要点下列出2-3个子要点',
'[{"name":"topic","label":"文章主题","placeholder":"例如：职场沟通技巧","required":true},{"name":"word_count","label":"目标字数","placeholder":"1000","required":false},{"name":"audience","label":"目标读者","placeholder":"职场新人","required":false}]',
'你是一位资深内容策划专家。', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(1,'故事情节创作','AI帮你构建引人入胜的故事',
'请根据以下设定创作一个引人入胜的故事情节：\n主角：{hero}\n故事类型：{genre}\n核心冲突：{conflict}\n\n要求：故事要有开端、发展、高潮、结局四个阶段，语言生动，约{length}字。',
'[{"name":"hero","label":"主角设定","placeholder":"例如：一个失意的程序员","required":true},{"name":"genre","label":"故事类型","placeholder":"职场/科幻/悬疑/爱情","required":true},{"name":"conflict","label":"核心冲突","placeholder":"例如：面临失业的困境","required":true},{"name":"length","label":"字数","placeholder":"500","required":false}]',
'你是一位创意写作导师，擅长构建扣人心弦的故事。', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 模板（营销）
(2,'产品卖点提炼','挖掘产品核心卖点',
'请分析以下产品，提炼核心卖点并生成一段营销文案：\n产品名称：{product}\n产品特点：{features}\n目标客群：{target}\n\n要求：\n1. 提炼3-5个核心卖点\n2. 每个卖点用一句话概括（不超过20字）\n3. 最后生成一段100字左右的营销文案',
'[{"name":"product","label":"产品名称","placeholder":"例如：智能保温杯","required":true},{"name":"features","label":"产品特点","placeholder":"例如：24小时保温、触控显温、防漏设计","required":true},{"name":"target","label":"目标客群","placeholder":"例如：上班族","required":false}]',
'你是一位经验丰富的营销策划专家。', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(2,'朋友圈文案','写出高转发的朋友圈内容',
'请为以下内容写3条风格各异的朋友圈文案：\n内容主题：{topic}\n产品/活动：{product}\n\n要求每条文案：\n- 情感真实，接地气\n- 100字以内\n- 分别采用：故事型、干货型、情绪型',
'[{"name":"topic","label":"内容主题","placeholder":"例如：新品上市","required":true},{"name":"product","label":"产品或活动","placeholder":"例如：夏日限定奶茶","required":true}]',
'你是一位社交媒体营销专家，文风亲切自然。', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(2,'活动策划方案','快速生成营销活动方案',
'请为以下活动生成一份完整的策划方案：\n活动类型：{type}\n目标：{goal}\n预算：{budget}\n时间：{duration}\n\n方案需包括：活动主题、活动流程、推广渠道、预期效果、注意事项。',
'[{"name":"type","label":"活动类型","placeholder":"例如：双十一促销","required":true},{"name":"goal","label":"活动目标","placeholder":"例如：提升销量20%","required":true},{"name":"budget","label":"预算范围","placeholder":"例如：5000元","required":false},{"name":"duration","label":"活动时长","placeholder":"例如：3天","required":false}]',
'你是一位资深活动策划师。', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 模板（编程）
(3,'代码审查','专业代码检查与优化建议',
'请对以下{language}代码进行全面审查：\n\n```{language}\n{code}\n```\n\n审查要点：\n1. 代码逻辑是否正确\n2. 是否有潜在Bug\n3. 性能优化建议\n4. 代码风格与规范\n5. 安全性问题（如有）\n\n请给出具体的改进建议和修改后的代码。',
'[{"name":"language","label":"编程语言","placeholder":"例如：JavaScript/Python/PHP","required":true},{"name":"code","label":"代码内容","placeholder":"粘贴需要审查的代码","required":true}]',
'你是一位有10年经验的高级软件工程师，擅长代码审查。', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(3,'功能开发','根据需求生成代码',
'请用{language}实现以下功能：\n功能描述：{description}\n\n要求：\n1. 代码简洁易读，有必要注释\n2. 考虑边界情况和错误处理\n3. 提供使用示例\n4. 如有优化空间请说明',
'[{"name":"language","label":"编程语言","placeholder":"例如：PHP/Python/JavaScript","required":true},{"name":"description","label":"功能描述","placeholder":"例如：实现一个防抖函数","required":true}]',
'你是一位全栈开发工程师，代码风格优雅简洁。', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(3,'Bug修复','定位并修复代码Bug',
'我的{language}代码出现了以下问题：\n错误信息：{error}\n\n相关代码：\n```{language}\n{code}\n```\n\n请帮我：\n1. 分析Bug原因\n2. 提供修复方案\n3. 给出修复后的完整代码',
'[{"name":"language","label":"编程语言","placeholder":"PHP","required":true},{"name":"error","label":"错误信息","placeholder":"粘贴报错信息","required":true},{"name":"code","label":"相关代码","placeholder":"粘贴出问题的代码","required":false}]',
'你是一位调试专家，擅长快速定位和修复代码问题。', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 模板（办公）
(4,'会议纪要整理','将会议内容整理成规范纪要',
'请根据以下会议内容，整理成规范的会议纪要：\n会议主题：{meeting_topic}\n参会人员：{attendees}\n原始记录：\n{raw_notes}\n\n纪要格式：会议概况、讨论要点、决策事项、行动计划（含负责人和截止时间）、下次会议安排。',
'[{"name":"meeting_topic","label":"会议主题","placeholder":"例如：Q2营销策略讨论","required":true},{"name":"attendees","label":"参会人员","placeholder":"例如：张总、李经理、王主管","required":false},{"name":"raw_notes","label":"会议原始记录","placeholder":"粘贴会议录音转文字或手写记录","required":true}]',
'你是一位专业的行政助理，擅长整理规范的商务文档。', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(4,'工作汇报撰写','快速生成高质量工作汇报',
'请根据以下信息生成一份{report_type}工作汇报：\n汇报人：{name}\n汇报内容要点：{content}\n\n汇报结构：本期工作完成情况（量化数据）、工作亮点与成绩、遇到的问题与解决方案、下期工作计划。',
'[{"name":"report_type","label":"汇报类型","placeholder":"周报/月报/季报/年报","required":true},{"name":"name","label":"汇报人","placeholder":"你的名字或职位","required":false},{"name":"content","label":"工作要点","placeholder":"本期完成了哪些工作，列举要点","required":true}]',
'你是一位擅长商务写作的职场专家。', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 模板（学习）
(5,'知识点解析','深入浅出解释任何知识点',
'请用通俗易懂的语言解析以下知识点：\n知识点：{topic}\n我的背景：{background}\n\n解析要求：\n1. 用一句话概括核心概念\n2. 用生活中的例子类比说明\n3. 列出关键要点（3-5条）\n4. 常见误区提示\n5. 推荐进一步学习的方向',
'[{"name":"topic","label":"知识点","placeholder":"例如：量子纠缠/机器学习/期权定价","required":true},{"name":"background","label":"我的背景","placeholder":"例如：零基础/大学生/有一定基础","required":false}]',
'你是一位知识渊博的导师，擅长用简单的语言解释复杂概念。', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(5,'学习计划制定','制定个性化学习路线',
'请为我制定一份{subject}的学习计划：\n学习目标：{goal}\n可用时间：每天{time_per_day}小时，共{total_weeks}周\n当前水平：{level}\n\n请提供：学习路线图、每周学习重点、推荐资源（书籍/课程/工具）、阶段性检验方法。',
'[{"name":"subject","label":"学习科目","placeholder":"例如：Python编程/英语口语/摄影","required":true},{"name":"goal","label":"学习目标","placeholder":"例如：能独立开发小程序","required":true},{"name":"time_per_day","label":"每日时间(小时)","placeholder":"2","required":false},{"name":"total_weeks","label":"总周数","placeholder":"12","required":false},{"name":"level","label":"当前水平","placeholder":"零基础","required":false}]',
'你是一位专业的学习规划师。', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 模板（创意）
(6,'广告语创作','创作令人印象深刻的广告语',
'请为以下品牌/产品创作5条广告语：\n品牌/产品：{brand}\n核心卖点：{value}\n品牌调性：{tone}\n\n要求：\n- 每条不超过15字\n- 朗朗上口，容易记忆\n- 体现品牌价值\n- 有创意，避免俗套',
'[{"name":"brand","label":"品牌或产品","placeholder":"例如：有机茶饮品牌","required":true},{"name":"value","label":"核心卖点","placeholder":"例如：纯天然、无添加","required":true},{"name":"tone","label":"品牌调性","placeholder":"例如：清新自然、高端精致","required":false}]',
'你是一位金奖广告文案人，专注品牌语言创新。', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

(6,'创意头脑风暴','快速产生大量创意想法',
'请围绕以下主题进行头脑风暴，生成20个创意想法：\n主题：{topic}\n应用场景：{scenario}\n限制条件：{constraints}\n\n要求：\n- 想法要多元化，涵盖不同角度\n- 包含常规方案和非常规方案\n- 每条想法一句话描述\n- 最后评选出最具潜力的3个并简要说明',
'[{"name":"topic","label":"创意主题","placeholder":"例如：如何让用户留住更长时间","required":true},{"name":"scenario","label":"应用场景","placeholder":"例如：移动App","required":false},{"name":"constraints","label":"限制条件","placeholder":"例如：低成本、快速实现","required":false}]',
'你是一位创意总监，善于多维度思考和快速产出创意。', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
