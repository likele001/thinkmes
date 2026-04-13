/**
 * 客服悬浮组件
 * 在网站右下角显示悬浮聊天窗口
 */
(function() {
    'use strict';
    
    // 配置
    const config = {
        apiBase: '/api',
        position: 'right',
        primaryColor: '#1890ff',
        autoOpen: false,
        welcomeMessage: '您好！我是智能客服助手，有什么可以帮助您的吗？'
    };
    
    // 会话ID
    let sessionId = localStorage.getItem('cs_widget_session_id');
    if (!sessionId) {
        sessionId = 'cs_widget_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('cs_widget_session_id', sessionId);
    }
    
    // HTML转义函数
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // 创建组件HTML
    function createWidget() {
        const widgetHtml = '<div id="cs-widget" style="position: fixed; bottom: 20px; ' + config.position + ': 20px; z-index: 9999; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">' +
            '<button id="cs-toggle-btn" onclick="toggleChat()" style="width: 60px; height: 60px; border-radius: 50%; background: ' + config.primaryColor + '; color: white; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s; display: flex; align-items: center; justify-content: center;">' +
            '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>' +
            '</svg>' +
            '</button>' +
            '<div id="cs-chat-window" style="display: none; position: absolute; bottom: 70px; ' + config.position + ': 0; width: 380px; height: 550px; background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); overflow: hidden; flex-direction: column;">' +
            '<div style="padding: 1rem; background: linear-gradient(135deg, ' + config.primaryColor + ' 0%, #40a9ff 100%); color: white;">' +
            '<div style="display: flex; justify-content: space-between; align-items: center;">' +
            '<div>' +
            '<h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">智能客服</h3>' +
            '<p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">在线 · 通常几分钟内回复</p>' +
            '</div>' +
            '<button onclick="toggleChat()" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.5rem; padding: 0; line-height: 1;">&times;</button>' +
            '</div>' +
            '</div>' +
            '<div style="padding: 1rem; border-bottom: 1px solid #f0f0f0; display: flex; gap: 0.5rem; flex-wrap: wrap;">' +
            '<button onclick="quickAsk(\'如何开始使用系统？\')" style="padding: 0.4rem 0.8rem; background: #f5f5f5; border: 1px solid #e8e8e8; border-radius: 4px; cursor: pointer; font-size: 0.85rem; transition: all 0.2s;">快速入门</button>' +
            '<button onclick="quickAsk(\'常见问题有哪些？\')" style="padding: 0.4rem 0.8rem; background: #f5f5f5; border: 1px solid #e8e8e8; border-radius: 4px; cursor: pointer; font-size: 0.85rem; transition: all 0.2s;">常见问题</button>' +
            '<button onclick="showFullPage()" style="padding: 0.4rem 0.8rem; background: #f5f5f5; border: 1px solid #e8e8e8; border-radius: 4px; cursor: pointer; font-size: 0.85rem; transition: all 0.2s;">完整版</button>' +
            '</div>' +
            '<div id="cs-messages" style="flex: 1; overflow-y: auto; padding: 1rem; background: #fafafa;">' +
            '<div style="background: white; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; display: inline-block; max-width: 80%;">' +
            escapeHtml(config.welcomeMessage) +
            '</div>' +
            '</div>' +
            '<div style="padding: 1rem; background: white; border-top: 1px solid #f0f0f0;">' +
            '<div style="display: flex; gap: 0.5rem;">' +
            '<input type="text" id="cs-input" placeholder="输入您的问题..." onkeypress="handleKeyPress(event)" style="flex: 1; padding: 0.6rem; border: 1px solid #e8e8e8; border-radius: 4px; font-size: 0.95rem;">' +
            '<button onclick="sendMessage()" id="cs-send-btn" style="padding: 0.6rem 1.5rem; background: ' + config.primaryColor + '; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.95rem; transition: all 0.2s;">发送</button>' +
            '</div>' +
            '<div style="text-align: center; margin-top: 0.5rem; font-size: 0.8rem; color: #999;">' +
            'Powered by AI · <a href="/customer-service" style="color: ' + config.primaryColor + '; text-decoration: none;">查看完整版</a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        document.body.insertAdjacentHTML('beforeend', widgetHtml);
        
        if (config.autoOpen) {
            setTimeout(function() {
                const hasOpened = sessionStorage.getItem('cs_widget_auto_opened');
                if (!hasOpened) {
                    toggleChat();
                    sessionStorage.setItem('cs_widget_auto_opened', 'true');
                }
            }, 3000);
        }
    }
    
    // 切换聊天窗口
    window.toggleChat = function() {
        const chatWindow = document.getElementById('cs-chat-window');
        const toggleBtn = document.getElementById('cs-toggle-btn');
        
        if (chatWindow.style.display === 'none') {
            chatWindow.style.display = 'flex';
            toggleBtn.style.transform = 'scale(0)';
            setTimeout(function() {
                document.getElementById('cs-input').focus();
            }, 100);
        } else {
            chatWindow.style.display = 'none';
            toggleBtn.style.transform = 'scale(1)';
        }
    };
    
    // 发送消息
    window.sendMessage = function() {
        const input = document.getElementById('cs-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        addMessage('user', message);
        input.value = '';
        
        showTyping();
        
        fetch(config.apiBase + '/chat/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                question: message,
                session_id: sessionId
            })
        })
        .then(response => response.json())
        .then(function(res) {
            hideTyping();
            if (res.code === 1) {
                addMessage('ai', res.data.answer);
            } else {
                addMessage('ai', '抱歉，我暂时无法回答这个问题。请尝试其他问题或访问完整客服中心。');
            }
        })
        .catch(function() {
            hideTyping();
            addMessage('ai', '网络连接失败，请稍后重试。');
        });
    };
    
    // 快捷提问
    window.quickAsk = function(question) {
        document.getElementById('cs-input').value = question;
        sendMessage();
    };
    
    // 显示完整版页面
    window.showFullPage = function() {
        window.location.href = '/customer-service';
    };
    
    // 添加消息
    function addMessage(type, content) {
        const messagesDiv = document.getElementById('cs-messages');
        const messageHtml = type === 'user' ? 
            '<div style="text-align: right; margin-bottom: 1rem;"><div style="background: ' + config.primaryColor + '; color: white; padding: 0.6rem 0.9rem; border-radius: 8px; display: inline-block; max-width: 80%; word-wrap: break-word;">' + escapeHtml(content) + '</div></div>' :
            '<div style="margin-bottom: 1rem;"><div style="background: white; padding: 0.6rem 0.9rem; border-radius: 8px; border: 1px solid #e8e8e8; display: inline-block; max-width: 85%; word-wrap: break-word;">' + escapeHtml(content) + '</div></div>';
        
        messagesDiv.insertAdjacentHTML('beforeend', messageHtml);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    
    // 显示输入指示器
    function showTyping() {
        const messagesDiv = document.getElementById('cs-messages');
        const typingHtml = '<div id="cs-typing" style="margin-bottom: 1rem;"><div style="background: white; padding: 0.6rem 0.9rem; border-radius: 8px; border: 1px solid #e8e8e8; display: inline-flex; gap: 4px; align-items: center;"><span style="width: 8px; height: 8px; background: #999; border-radius: 50%; animation: typing 1.4s infinite;"></span><span style="width: 8px; height: 8px; background: #999; border-radius: 50%; animation: typing 1.4s 0.2s infinite;"></span><span style="width: 8px; height: 8px; background: #999; border-radius: 50%; animation: typing 1.4s 0.4s infinite;"></span></div></div>';
        messagesDiv.insertAdjacentHTML('beforeend', typingHtml);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    
    // 隐藏输入指示器
    function hideTyping() {
        const typing = document.getElementById('cs-typing');
        if (typing) typing.remove();
    }
    
    // 回车发送
    window.handleKeyPress = function(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    };
    
    // 添加CSS动画
    const style = document.createElement('style');
    style.textContent = '@keyframes typing { 0%, 60%, 100% { transform: translateY(0); } 30% { transform: translateY(-6px); } } #cs-toggle-btn:hover { transform: scale(1.1) !important; } #cs-chat-window button:hover { opacity: 0.85; } @media (max-width: 480px) { #cs-chat-window { width: calc(100vw - 40px) !important; height: calc(100vh - 120px) !important; bottom: 70px !important; right: 20px !important; left: 20px !important; } }';
    document.head.appendChild(style);
    
    // 初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createWidget);
    } else {
        createWidget();
    }
})();
