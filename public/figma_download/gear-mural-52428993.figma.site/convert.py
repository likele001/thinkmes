import os
import json

# 创建输出目录
os.makedirs('static', exist_ok=True)

# 读取原始 index.html
with open('index.html', 'r', encoding='utf-8') as f:
    html_content = f.read()

# 页面配置
pages = [
    {'route': '', 'json': '_index.json', 'title': '首页'},
    {'route': 'attendance', 'json': 'attendance.json', 'title': '考勤'},
    {'route': 'piece-wage', 'json': 'piece-wage.json', 'title': '计件工资'},
    {'route': 'profile', 'json': 'profile.json', 'title': '个人中心'},
    {'route': 'work-report', 'json': 'work-report.json', 'title': '工作报告'},
]

for page in pages:
    # 生成文件名
    if page['route'] == '':
        filename = 'index.html'
    else:
        filename = f"{page['route']}.html"
    
    # 修改 HTML 内容
    new_html = html_content
    new_html = new_html.replace('<title>用户中心前端设计</title>', f'<title>{page["title"]} - MES用户中心</title>')
    
    # 修改 JSON 预加载路径
    old_json_path = '_json/7f69830d-54c9-4241-811e-989e7282b4d6/_index.json'
    new_json_path = f'_json/7f69830d-54c9-4241-811e-989e7282b4d6/{page["json"]}'
    new_html = new_html.replace(f'href="{old_json_path}"', f'href="{new_json_path}"')
    
    # 写入文件
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(new_html)
    
    print(f'创建: {filename}')

print('\n转换完成！')
