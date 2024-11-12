<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>LLM小站</title>
    <!-- 引入 Font Awesome 图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 引入 Marked.js Markdown 解析器 -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script type="text/javascript">
        window.MathJax = {
          tex: {
            inlineMath: [['$', '$'], ['\\(', '\\)'], ['(', ')']],
            displayMath: [['$$', '$$'], ['\\[', '\\]'], ['[', ']']]
          }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    
    <!-- 引入 DOMPurify 进行 HTML 消毒，防止 XSS 攻击 -->
    <script src="https://cdn.jsdelivr.net/npm/dompurify@2.4.0/dist/purify.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f5;
            margin: 0;
            padding: 20px;
            position: relative;
        }

        /* 标题样式 */
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: 36px;
            color: #333;
            margin: 0;
        }

        /* 容器样式 */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        /* 输入区域的Flex布局 */

        /* 修改回形针图标样式 */
        .file-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #555;
            width: 24px;
            height: 24px;
        }

        #fileInput {
            display: none;
        }


        .input-section, .text-input-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }

        /* 输入框和按钮样式 */
        .input-group, .select-group, .file-upload-group, /* 修改 .text-input-group */
        .text-input-group {
            display: flex;
            flex: 1 1 45%;
            align-items: center;
        }

        .select-group, .text-input-group {
            flex: 1 1 100%;
        }

        /* 新增 */
        .textarea-wrapper {
            position: relative;
            flex: 1;
        }



        .input-group input, 
        .select-group select, 
        .file-upload-group input[type="file"], 
        .text-input-group input {
            flex: 1;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s;
            background-color: #fff;
        }

        /* 修改 textarea 样式，增加右侧内边距 */
        .text-input-group textarea {
            flex: 1;
            padding: 12px 15px;
            width: calc(100% - 60px); /* Adjust the width as needed */
            padding-right: 40px; /* 为回形针图标预留空间 */
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s;
            background-color: #fff;
            resize: vertical;
            min-height: 60px;
        }

        .text-input-group textarea:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .input-group input:focus, 
        .select-group select:focus, 
        .file-upload-group input[type="file"]:focus, 
        .text-input-group input:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .input-group button, 
        .select-group button, 
        .file-upload-group button, 
        .text-input-group button {
            margin-left: 10px;
            padding: 12px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .input-group button:hover, 
        .select-group button:hover, 
        .file-upload-group button:hover, 
        .text-input-group button:hover {
            background-color: #45a049;
        }

        /* 文件上传样式特定 */
        .file-upload-group button {
            background-color: #2196F3;
        }

        .file-upload-group button:hover {
            background-color: #0b7dda;
        }

        /* 对话区域样式 */
        #conversation {
            flex: 1;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            height: 500px;
            position: relative;
            z-index: 1;
        }

        .message {
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 20px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .user {
            background-color: #daf1ff;
            align-self: flex-end;
            color: #333;
        }

        .assistant {
            background-color: #e6ffe6;
            align-self: flex-start;
            color: #333;
        }

        .debug {
            color: gray;
            font-style: italic;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        /* 调试信息容器 */
        #debug-container {
            padding: 20px;
            background-color: #fffbe6;
            border: 1px solid #ffcc00;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 250px;
            overflow-y: auto;
            position: relative;
            z-index: 1;
        }

        .debug-message {
            margin: 5px 0;
            font-size: 0.9em;
        }

        .debug-message.debug {
            color: gray;
        }

        .debug-message.error {
            color: red;
            font-weight: bold;
        }

        /* 圆形按钮样式 */
        .circle-button {
            background-color: #4CAF50; /* 绿色背景 */
            border: none;
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 24px;
            cursor: pointer;
            border-radius: 50%;
            transition: background-color 0.3s, transform 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* 移除默认的 margin 以便在 Flex 容器中正确排列 */
            display: inline-block;
        }

        .circle-button:hover {
            background-color: #45a049;
            transform: scale(1.1);
        }

        /* 挂断按钮特定样式 */
        #hang-up-button {
            background-color: #f44336; /* 红色背景 */
        }

        #hang-up-button:hover {
            background-color: #e53935; /* 深红色背景 */
        }

        /* 自定义开关按钮样式 */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            margin-left: 10px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .switch input:checked + .slider {
            background-color: #4CAF50;
        }

        .switch input:focus + .slider {
            box-shadow: 0 0 1px #4CAF50;
        }

        .switch input:checked + .slider:before {
            transform: translateX(26px);
        }


        /* 按钮行样式 */
        .button-row {
            display: flex;
            justify-content: center; /* 水平居中，可根据需要调整 */
            align-items: center;    /* 垂直居中 */
            gap: 20px;              /* 按钮之间的间距 */
            margin-top: 20px;       /* 上方的外边距，可根据需要调整 */
        }

        /* 全局响应式调整 */
        @media (max-width: 768px) {
            .input-group, 
            .file-upload-group, 
            .select-group, 
            .text-input-group {
                flex: 1 1 100%;
            }

            #conversation {
                height: 300px;
            }

            #debug-container {
                height: 200px;
            }

            /* 适应小屏幕的按钮大小 */
            .circle-button {
                padding: 10px 15px;
                font-size: 20px;
            }

            .button-row {
                gap: 10px;
                margin-top: 10px;
            }

            /* 自定义开关按钮适应小屏幕 */
            .switch {
                width: 40px;
                height: 20px;
            }

            .slider:before {
                height: 16px;
                width: 16px;
                left: 2px;
                bottom: 2px;
            }

            .switch input:checked + .slider:before {
                transform: translateX(20px);
            }

        }

        /* Markdown 内容样式优化 */
        #conversation h1, #conversation h2, #conversation h3, #conversation h4, #conversation h5, #conversation h6 {
            margin: 10px 0;
        }

        #conversation p {
            margin: 10px 0;
        }

        #conversation ul, #conversation ol {
            margin: 10px 20px;
        }

        #conversation a {
            color: #4CAF50;
            text-decoration: none;
        }

        #conversation a:hover {
            text-decoration: underline;
        }

        #conversation pre {
            background-color: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }

        #conversation code {
            background-color: #f4f4f4;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- 动态背景画布 -->
    <canvas id="dynamic-background"></canvas>

    <div class="container">
        <!-- 标题区域 -->
        <div class="header">
            <h1>LLM小站</h1>
        </div>

        <!-- 输入区域 -->
        <div class="input-section">
            <!-- API 密钥输入区域 -->
            <div class="input-group">
                <input type="password" id="api-key" placeholder="请输入您的 OpenAI API 密钥">
                <button id="save-api-key">保存密钥</button>
            </div>

            <!-- 文件上传区域 -->
            <div class="file-upload-group">
                <input type="file" id="json-file" accept=".json">
                <button id="upload-json">上传 JSON 文件</button>
            </div>

            <!-- 助手姓名输入区域 -->
            <div class="input-group">
                <input type="text" id="assistant-name" placeholder="请输入助手姓名">
                <button id="save-assistant-name">姓名</button>
            </div>

            <!-- 性别选择区域 -->
            <div class="select-group">
                <select id="gender-selection">
                    <option value="male">男</option>
                    <option value="female">女</option>
                </select>
                <button id="save-gender">性别</button>
            </div>

            <!-- 语言选择区域 -->
            <div class="select-group">
                <select id="language-selection">
                    <option value="en-US">英语</option>
                    <option value="zh-CN">中文</option>
                </select>
                <button id="save-language">语言</button>
            </div>

            <!-- 模型输入区域 -->
            <div class="select-group">
                <select id="model-name">
                    <option value="gpt-3.5-turbo">chatgpt-3.5-turbo</option>
                    <option value="claude-3-5-sonnet-latest">claude-3-5-sonnet-latest</option>
                    <option value="chatgpt-4o-latest">chatgpt-4o-latest</option>
                    <option value="o1-mini">o1-mini</option>
                    <option value="o1-preview">o1-preview</option>
                    <option value="gpt-4-all">gpt-4-all</option>
                    <option value="luma">luma</option>
                </select>
                <button id="save-model-name">模型</button>
            </div>

            <!-- 文字转语音开关 -->
            <div class="input-group">
                <label for="tts-toggle">文字转语音：</label>
                <label class="switch">
                    <input type="checkbox" id="tts-toggle" checked>
                    <span class="slider"></span>
                </label>
            </div>

        </div>

        <!-- 对话内容显示区域 -->
        <div id="conversation">
            <!-- 对话内容将显示在这里 -->
        </div>

        <!-- 文字输入区域 -->
        <div class="text-input-section">
            <div class="text-input-group">
                <div class="textarea-wrapper">
                    <textarea id="text-input" placeholder="请输入您的文本"></textarea>
                    <!-- 文件上传图标 -->
                    <i class="fas fa-paperclip file-icon" id="uploadIcon" title="上传文件"></i>
                    <input type="file" id="message-fileInput" multiple style="display: none;">
                </div>
                <button id="send-text">发送</button>
            </div>
        </div>

        <!-- 调试信息区域 -->
        <div id="debug-container">
            <strong>调试信息：</strong>
            <!-- 调试内容将显示在这里 -->
        </div>

        <!-- 语音、通话和挂断按钮 -->
        <div class="button-row">
            <button id="call-button" class="circle-button"><i class="fas fa-microphone"></i></button>
            <button id="start-call-button" class="circle-button"><i class="fas fa-phone"></i></button>
            <button id="hang-up-button" class="circle-button" style="display: none;"><i class="fas fa-phone-slash"></i></button>
        </div>
    </div>

    <!-- 动态背景脚本 -->
    <script>
        (function() {
            function getAttributeOrDefault(element, attr, defaultValue) {
                return element.getAttribute(attr) || defaultValue;
            }

            function getScripts(tag) {
                return document.getElementsByTagName(tag);
            }

            function getConfig() {
                const scripts = getScripts("script");
                const lastScript = scripts[scripts.length - 1];
                return {
                    l: scripts.length,
                    z: getAttributeOrDefault(lastScript, "zIndex", -1),
                    o: getAttributeOrDefault(lastScript, "opacity", 1),
                    c: getAttributeOrDefault(lastScript, "color", "255,0,0"),
                    n: getAttributeOrDefault(lastScript, "count", 180)
                };
            }

            function resizeCanvas() {
                canvasWidth = canvas.width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                canvasHeight = canvas.height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
            }

            function animate() {
                ctx.clearRect(0, 0, canvasWidth, canvasHeight);
                particles.forEach((particle, idx) => {
                    // 更新位置
                    particle.x += particle.xa;
                    particle.y += particle.ya;

                    // 碰到边界反弹
                    if (particle.x > canvasWidth || particle.x < 0) particle.xa *= -1;
                    if (particle.y > canvasHeight || particle.y < 0) particle.ya *= -1;

                    // 绘制粒子
                    ctx.fillRect(particle.x - 0.5, particle.y - 0.5, 1, 1);

                    // 粒子连接
                    for (let e = idx + 1; e < combinedParticles.length; e++) {
                        let other = combinedParticles[e];
                        if (other.x === null || other.y === null) continue;

                        let dx = particle.x - other.x;
                        let dy = particle.y - other.y;
                        let distance = dx * dx + dy * dy;

                        if (distance < other.max) {
                            if (other === mouseParticle && distance >= other.max / 2) {
                                particle.x -= 0.03 * dx;
                                particle.y -= 0.03 * dy;
                            }

                            let t = (other.max - distance) / other.max;
                            ctx.beginPath();
                            ctx.lineWidth = t / 2;
                            ctx.strokeStyle = `rgba(${config.c},${t + 0.2})`;
                            ctx.moveTo(particle.x, particle.y);
                            ctx.lineTo(other.x, other.y);
                            ctx.stroke();
                        }
                    }
                });
                requestAnimationFrame(animate);
            }

            let canvas = document.getElementById("dynamic-background"),
                config = getConfig(),
                ctx = canvas.getContext("2d"),
                requestAnimationFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || 
                                        window.oRequestAnimationFrame || window.msRequestAnimationFrame || 
                                        function(callback) { window.setTimeout(callback, 1000 / 45); },
                rand = Math.random,
                mouseParticle = { x: null, y: null, max: 20000 },
                particles = [],
                combinedParticles;

            canvas.id = "c_n" + config.l;
            canvas.style.cssText = `position:fixed;top:0;left:0;z-index:${config.z};opacity:${config.o};pointer-events:none`;
            resizeCanvas();
            window.onresize = resizeCanvas;

            window.onmousemove = function(e) {
                mouseParticle.x = e.clientX;
                mouseParticle.y = e.clientY;
            };

            window.onmouseout = function() {
                mouseParticle.x = null;
                mouseParticle.y = null;
            };

            for (let i = 0; i < config.n; i++) {
                let x = rand() * canvasWidth,
                    y = rand() * canvasHeight,
                    xa = 2 * rand() - 1,
                    ya = 2 * rand() - 1;
                particles.push({ x, y, xa, ya, max: 6000 });
            }

            combinedParticles = particles.concat([mouseParticle]);

            setTimeout(animate, 100);
        })();
    </script>

    <!-- 优化后的 JavaScript 代码 -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 获取调试容器
            const debugContainer = document.getElementById('debug-container');
            const conversationDiv = document.getElementById('conversation');

            // 定义一个函数用于显示调试信息
            function displayDebug(message, type = 'debug') {
                const debugMsgDiv = document.createElement('div');
                debugMsgDiv.className = `debug-message ${type}`;
                debugMsgDiv.textContent = `[${type.toUpperCase()}] ${message}`;
                debugContainer.appendChild(debugMsgDiv);
                // 自动滚动到底部
                debugContainer.scrollTop = debugContainer.scrollHeight;
            }

            // 覆盖 console.log 和 console.error 以显示调试信息在页面上
            (function() {
                const originalLog = console.log;
                const originalError = console.error;

                console.log = function(message, ...optionalParams) {
                    originalLog.apply(console, arguments);
                    displayDebug(message, 'debug');
                    if (optionalParams.length > 0) {
                        optionalParams.forEach(param => {
                            displayDebug(JSON.stringify(param), 'debug');
                        });
                    }
                };

                console.error = function(message, ...optionalParams) {
                    originalError.apply(console, arguments);
                    displayDebug(message, 'error');
                    if (optionalParams.length > 0) {
                        optionalParams.forEach(param => {
                            displayDebug(JSON.stringify(param), 'error');
                        });
                    }
                };
            })();

            let historyMessage = [];

            // 保存 API 密钥
            let openaiApiKey = '';
            document.getElementById('save-api-key').addEventListener('click', () => {
                const apiKeyInput = document.getElementById('api-key');
                openaiApiKey = apiKeyInput.value.trim();
                if (openaiApiKey) {
                    alert('API 密钥已保存！');
                    console.log('API 密钥已设置。');
                } else {
                    alert('请输入有效的 API 密钥。');
                }
            });

            // 存储上传的 JSON 字典
            let jsonDictionary = {};

            // 处理 JSON 文件上传
            document.getElementById('upload-json').addEventListener('click', () => {
                const fileInput = document.getElementById('json-file');
                const file = fileInput.files[0];

                if (!file) {
                    alert('请先选择一个 JSON 文件。');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        jsonDictionary = JSON.parse(e.target.result);
                        alert('JSON 文件已成功上传！');
                        console.log('JSON 字典已加载：', jsonDictionary);
                    } catch (error) {
                        alert('解析 JSON 文件时出错，请确保文件格式正确。');
                        console.error('解析 JSON 文件时出错：', error);
                    }
                };
                reader.onerror = (e) => {
                    alert('读取文件时出错。');
                    console.error('读取文件时出错：', e);
                };
                reader.readAsText(file);
            });

            // 保存助手姓名
            let assistantName = 'an assistant'; // 默认值
            // let assistantName = 'Thomas Alva Edison'; // 默认值
            document.getElementById('save-assistant-name').addEventListener('click', () => {
                const assistantNameInput = document.getElementById('assistant-name');
                const newName = assistantNameInput.value.trim();
                if (newName) {
                    assistantName = newName;
                    alert(`助手姓名已保存为：${assistantName}`);
                    historyMessage = [];
                    console.log('助手姓名已设置为：', assistantName);
                } else {
                    alert('请输入有效的助手姓名。');
                }
            });

            // 保存助手性别
            let assistantGender = 'male'; // 默认值
            document.getElementById('save-gender').addEventListener('click', () => {
                const genderSelect = document.getElementById('gender-selection');
                assistantGender = genderSelect.value;
                alert(`性别已保存为：${assistantGender === 'male' ? '男' : '女'}`);
                console.log('助手性别已设置为：', assistantGender);
            });

            // 保存语言选项
            let selectedLanguage = 'en-US'; // 默认值
            document.getElementById('save-language').addEventListener('click', () => {
                const languageSelect = document.getElementById('language-selection');
                selectedLanguage = languageSelect.value;
                alert(`语言已保存为：${selectedLanguage === 'en-US' ? '英语' : '中文'}`);
                console.log('语言已设置为：', selectedLanguage);
            });

            // 保存模型名称
            let modelName = 'gpt-3.5-turbo'; // 默认值
            document.getElementById('save-model-name').addEventListener('click', () => {
                const modelNameInput = document.getElementById('model-name');
                const newModel = modelNameInput.value.trim();
                if (newModel) {
                    modelName = newModel;
                    alert(`模型名称已保存为：${modelName}`);
                    historyMessage = [];
                    console.log('模型名称已设置为：', modelName);
                } else {
                    alert('请输入有效的模型名称，将使用默认值 gpt-3.5-turbo。');
                }
            });

            // 处理文件上传图标和文件输入
            let fileCount = 1; // 用于命名文件
            const uploadedFiles = []; // 存储上传的文件URL

            // 处理文件图标点击事件
            document.getElementById('uploadIcon').addEventListener('click', () => {
            document.getElementById('message-fileInput').click();
            });

            // 处理文件选择事件
            document.getElementById('message-fileInput').addEventListener('change', async (event) => {
                const files = event.target.files;
                if (files.length === 0) return;

                for (let file of files) {
                    const filename = file.name;
                    try {
                        const url = await uploadFile(file, filename);
                        uploadedFiles.push(url);
                        displayDebug(`已上传: ${url}`);
                        console.log(`文件已上传: ${url}`);
                    } catch (err) {
                        console.error('上传文件失败:', err);
                        displayDebug(`上传文件失败: ${err.message}`);
                    }
                }

                // 清空文件输入
                event.target.value = '';
            });

            // 上传文件到服务器
            async function uploadFile(file, filename) {
                const formData = new FormData();
                formData.append('file', file, filename);

                const response = await fetch('upload.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || '上传失败');
                }

                const data = await response.json();
                return data.url; // 服务器返回的文件URL
            }

            // 获取文件扩展名
            function getFileExtension(filename) {
            return filename.substring(filename.lastIndexOf('.'));
            }


            // 检查浏览器是否支持 Web Speech API
            let isRecognizing = false; // 标记是否处于识别状态
            let isSpeaking = false;    // 标记是否正在进行语音合成
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            let recognition; // 在更广泛的作用域内声明 recognition

            if (!SpeechRecognition) {
                alert("您的浏览器不支持语音识别功能。请使用最新版本的 Chrome。");
                console.error("语音识别 API 不受支持。");
            } else {
                recognition = new SpeechRecognition();
                recognition.continuous = false; // 默认不开启连续识别
                recognition.interimResults = false; // 不需要临时结果

                // let isRecognizing = false; // 标记是否处于识别状态
                let isContinuous = false;  // 标记是否为连续识别模式
                // let isSpeaking = false;    // 标记是否正在进行语音合成

                // 单次语音输入（点击麦克风图标）
                document.getElementById('call-button').addEventListener('click', () => {
                    if (isRecognizing) {
                        recognition.stop(); // 如果正在识别，则停止识别
                        return;
                    }
                    recognition.lang = selectedLanguage; // 每次启动前更新语言
                    isContinuous = false;                // 设置为单次识别
                    recognition.continuous = false;
                    recognition.start();
                    isRecognizing = true;
                    console.log('开始单次语音识别');
                });

                // 开始通话（连续语音识别）
                document.getElementById('start-call-button').addEventListener('click', () => {
                    if (isRecognizing) {
                        recognition.stop(); // 如果正在识别，则停止当前识别
                    }
                    recognition.lang = selectedLanguage;
                    isContinuous = true;                // 设置为连续识别
                    recognition.continuous = true;
                    recognition.start();
                    isRecognizing = true;

                    // 更新 UI
                    document.getElementById('start-call-button').style.display = 'none';
                    document.getElementById('hang-up-button').style.display = 'block';
                    console.log('开始连续语音识别');
                });

                // 挂断通话
                document.getElementById('hang-up-button').addEventListener('click', () => {
                    isRecognizing = false;
                    isContinuous = false;
                    recognition.stop(); // 停止识别

                    // 更新 UI
                    document.getElementById('start-call-button').style.display = 'block';
                    document.getElementById('hang-up-button').style.display = 'none';
                    console.log('已挂断，停止语音识别。');
                });

                recognition.onstart = () => {
                    console.log('语音识别已启动，请讲话。');
                };

                recognition.onresult = async (event) => {
                    const transcript = event.results[event.results.length - 1][0].transcript.trim();
                    console.log('您说：' + transcript);

                    // 在页面上显示用户输入
                    displayMessage('You', transcript, 'user');

                    isSpeaking = true; // 标记正在进行语音合成
                    recognition.stop();
                    // isRecognizing = false;

                    // // 如果是单次识别模式，停止识别
                    if (!isContinuous) {
                        isRecognizing = false;
                        // recognition.stop();
                    }

                    // 处理用户输入
                    await processInput(transcript);
                    
                    isSpeaking = false; // 标记正在进行语音合成

                    if (isContinuous) {
                        // 如果是连续识别模式，重新启动识别
                        recognition.start();
                        console.log('重新启动连续语音识别');
                    }
                    
                    // if (isRecognizing && isContinuous && !isSpeaking) {
                    //     // 在未进行语音合成时，重新启动识别
                    //     setTimeout(() => {
                    //         recognition.start();
                    //         console.log('重新启动连续语音识别');
                    //     }, 100); // 可根据需要调整延迟
                    // } else {
                    //     isRecognizing = false;
                    // }
                    // // 如果是单次识别模式，停止识别
                    // if (!isContinuous) {
                    //     isRecognizing = false;
                    //     recognition.stop();
                    // }
                };

                recognition.onend = () => {
                    console.log('语音识别已结束。');
                    if (isContinuous && !isSpeaking) {
                        // 在未进行语音合成时，重新启动识别
                        setTimeout(() => {
                            recognition.start();
                            console.log('重新启动连续语音识别');
                        }, 100); // 可根据需要调整延迟
                    } else {
                        isRecognizing = false;
                    }
                };

                recognition.onerror = (event) => {
                    console.error('识别错误：' + event.error);

                    // 根据错误类型处理
                    if (event.error === 'audio-capture' || event.error === 'not-allowed') {
                        isRecognizing = false;
                        isContinuous = false;

                        // 更新 UI（如果需要）
                        document.getElementById('start-call-button').style.display = 'block';
                        document.getElementById('hang-up-button').style.display = 'none';
                    }

                    // 对于其他错误，可以选择重新启动识别（仅在连续模式下）
                    if (isContinuous && !isSpeaking) {
                        setTimeout(() => {
                            recognition.start();
                            console.log('在错误后重新启动连续语音识别');
                        }, 500);
                    }
                };

                
            }


            // 处理发送按钮点击事件
            document.getElementById('send-text').addEventListener('click', () => {
            const textInput = document.getElementById('text-input');
            const userText = textInput.value.trim();
            if (userText === '' && uploadedFiles.length === 0) {
            alert('请输入文字或上传文件后再发送。');
            return;
            }

            // 构建 prompt，包括文本和文件 URLs
            const prompt = userText + (uploadedFiles.length > 0 ? '\n' + uploadedFiles.join('\n') : '');

            // 在页面上显示用户输入
            if (userText) {
            displayMessage('You', prompt, 'user');
            textInput.value = '';
            }


            

            // 处理输入
            processInput(prompt);
            });


            // 处理用户输入
            async function processInput(inputText) {
                // 检查 JSON 字典中是否有相似的问题
                const matchedValue = checkDictionary(inputText);

                if (matchedValue) {
                    // 如果找到相似度足够高的键，直接输出对应的值
                    console.log('从字典中获取的回复：' + matchedValue);
                    displayMessage('Assistant', matchedValue, 'assistant');

                    await speak(matchedValue);
                } else {
                    // 检查模型名称
                    if (modelName === 'luma') {
                        // 调用 sendToLuma 函数
                        await sendToLuma(inputText);
                    } else {
                        // 否则调用 OpenAI API
                        await sendToOpenAI(inputText);
                    }
                    }
            }

            // 使用 Levenshtein 距离计算相似度
            function levenshteinDistance(a, b) {
                const matrix = [];

                for (let i = 0; i <= b.length; i++) {
                    matrix[i] = [i];
                }

                for (let j = 0; j <= a.length; j++) {
                    matrix[0][j] = j;
                }

                for (let i = 1; i <= b.length; i++) {
                    for (let j = 1; j <= a.length; j++) {
                        if (b.charAt(i - 1) === a.charAt(j - 1)) {
                            matrix[i][j] = matrix[i - 1][j - 1];
                        } else {
                            matrix[i][j] = Math.min(
                                matrix[i - 1][j - 1] + 1, // 替换
                                matrix[i][j - 1] + 1,     // 插入
                                matrix[i - 1][j] + 1      // 删除
                            );
                        }
                    }
                }

                return matrix[b.length][a.length];
            }

            // 检查字典中是否有匹配的键
            function checkDictionary(inputText) {
                let highestSimilarity = 0;
                let matchedValue = null;
                const lowerInput = inputText.toLowerCase();

                for (const key in jsonDictionary) {
                    if (jsonDictionary.hasOwnProperty(key)) {
                        const lowerKey = key.toLowerCase();
                        const distance = levenshteinDistance(lowerInput, lowerKey);
                        const maxLength = Math.max(lowerInput.length, lowerKey.length);
                        const similarity = ((maxLength - distance) / maxLength) * 100;

                        console.log(`比较 "${inputText}" 和 "${key}"，相似度: ${similarity.toFixed(2)}%`);

                        if (similarity > 80 && similarity > highestSimilarity) {
                            highestSimilarity = similarity;
                            matchedValue = jsonDictionary[key];
                        }
                    }
                }

                return matchedValue;
            }

            // 发送请求到 OpenAI API
            function sendToOpenAI(text) {
                return new Promise((resolve, reject) => {
                    if (!openaiApiKey) {
                        alert('请先输入并保存您的 OpenAI API 密钥。');
                        console.error('OpenAI API 密钥未设置。');
                        reject('OpenAI API 密钥未设置。');
                        return;
                    }

                    // OpenAI API 端点
                    const url = 'https://api.openai-hub.com/v1/chat/completions';

                    // 根据选择的语言设置系统提示
                    let systemPrompt = '';

                    if (assistantName != 'an assistant'){
                        if (selectedLanguage === 'zh-CN') {
                            systemPrompt = `你是${assistantName}，请用第一人称来回答问题。我们在打电话，请用一句话简洁地回答复我。请用简单的词汇和语句回复我。`;
                        } else {
                            systemPrompt = `You are ${assistantName}, please answer the question in first person. We're on the phone, just reply to me in one concise sentence. Please reply me using simple words and sentences. `;
                        }
                    }
                    

                    // 准备请求数据

                    // if (historyMessage.length === 0){
                    //     historyMessage.push({
                    //             "role": "system",
                    //             "content": systemPrompt
                    //         });
                    // }

                    if (historyMessage.length > 10){
                        historyMessage = [{
                                "role": "system",
                                "content": systemPrompt
                            }];
                    }
                    
                    historyMessage.push({
                                "role": "system",
                                "content": systemPrompt
                            });

                    historyMessage.push({
                                "role": "user",
                                "content": text
                            });

                    var data = {
                        model: modelName,
                        messages: historyMessage
                    };

                    if (modelName === 'o1-mini' || modelName === 'o1-preview' ) {
                        data = {
                            model: modelName,
                            messages: [
                                {
                                    "role": "user",
                                    "content": systemPrompt + text
                                }
                            ]
                        };
                    }

                    console.log('发送请求到 OpenAI：', data);

                    // 发送 POST 请求
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + openaiApiKey  // 使用用户输入的 OpenAI API 密钥
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        console.log('收到响应：', response);
                        if (!response.ok) {
                            return response.json().then(errData => {
                                throw new Error(errData.error.message || '网络响应异常 ' + response.statusText);
                            });
                        }
                        return response.json();
                    })
                    .then(responseData => {
                        console.log('响应数据：', responseData);

                        // 提取助手的回复
                        const assistantReply = responseData.choices[0].message.content;
                        console.log('Assistant: ' + assistantReply);

                        // 在页面上显示助手回复
                        displayMessage('Assistant', assistantReply, 'assistant');
                        
                        historyMessage.push({
                                "role": "system",
                                "content": assistantReply
                            });

                        isSpeaking = true; // 标记正在进行语音合成

                        // 将助手的回复转换为语音
                        speak(assistantReply).then(() => {
                            resolve();
                        }).catch(error => {
                            reject(error);
                        });

                        isSpeaking = false;

                        // 删除已上传的文件并清空数组
                        deleteUploadedFiles();
                        uploadedFiles.length = 0;
                    })
                    .catch(error => {
                        console.error('请求错误：', error);
                        displayMessage('Error', error.message, 'error');
                        reject(error);
                    });
                });
            }



            // 新增的 sendToLuma 函数
            function sendToLuma(text) {
                return new Promise((resolve, reject) => {
                    if (!openaiApiKey) {
                        alert('请先输入并保存您的 OpenAI API 密钥。');
                        console.error('OpenAI API 密钥未设置。');
                        reject('OpenAI API 密钥未设置。');
                        return;
                    }

                    if (uploadedFiles.length === 0) {
                        alert('请先上传一个图片文件。');
                        console.error('未上传任何文件。');
                        reject('未上传任何文件。');
                        return;
                    }

                    // Luma API 端点
                    const url = 'https://api.openai-hub.com/luma/generations';

                    // 获取第一个上传的文件的URL
                    const imageUrl = uploadedFiles[0];

                    // 构建请求数据
                    const data = {
                        "user_prompt": text,
                        "aspect_ratio": "16:9",
                        "expand_prompt": true,
                        "loop": true,
                        "image_url": imageUrl
                    };

                    console.log('发送请求到 Luma API：', data);

                    // 发送 POST 请求
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json; charset=utf-8',
                            'Authorization': 'Bearer ' + openaiApiKey
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        console.log('收到响应：', response);
                        // 检查响应是否成功
                        if (!response.ok) {
                            throw new Error('网络响应不正确：' + response.statusText);
                        }
                        // 解析响应为 JSON
                        return response.json();
                    })
                    .then(responseData => {
                        console.log('响应数据：', responseData);

                        console.log('Assistant: ', responseData);

                        // 在页面上显示助手回复
                        displayMessage('Assistant', JSON.stringify(responseData), 'assistant');

                        // 删除已上传的文件并清空数组
                        deleteUploadedFiles();
                        uploadedFiles.length = 0;
                    })
                    .catch(error => {
                        console.error('请求错误：', error);
                        displayMessage('Error', error.message, 'error');
                    });
                });
            }


            // 删除已上传的文件
            function deleteUploadedFiles() {
                for (let url of uploadedFiles) {
                    // 提取文件名
                    const filename = url.substring(url.lastIndexOf('/') + 1);
                    fetch('delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ filename })
                    })
                    .then(response => {
                        if (!response.ok) {
                            console.error('删除文件失败:', filename);
                        } else {
                            console.log('已删除文件:', filename);
                        }
                    })
                    .catch(error => {
                        console.error('删除文件出错:', error);
                    });
                }
            }

            // 使用 OpenAI 的文本转语音接口
            function speak(text) {
                return new Promise((resolve, reject) => {

                    // // 如果正在进行语音识别，先停止识别，避免识别到合成的语音
                    // if (isRecognizing) {
                    //     recognition.stop();
                    // }

                    // isSpeaking = true; // 标记正在进行语音合成

                    // 检查文字转语音功能是否启用
                    const ttsEnabled = document.getElementById('tts-toggle').checked;
                    if (!ttsEnabled) {
                        console.log('文字转语音功能已关闭，跳过语音合成。');
                        resolve(); // 直接resolve，跳过语音合成
                        return;
                    }

                    if (!openaiApiKey) {
                        alert('请先输入并保存您的 OpenAI API 密钥。');
                        console.error('OpenAI API 密钥未设置。');
                        reject('OpenAI API 密钥未设置。');
                        return;
                    }

                    // 根据 assistantGender 选择语音
                    const voiceOptions = {
                        'male': ['alloy', 'onyx'],
                        'female': ['nova', 'echo', 'fable', 'shimmer']
                    };
                    const voices = voiceOptions[assistantGender] || ['alloy'];
                    const selectedVoice = voices[0]; // 默认选择第一个语音

                    // 准备请求数据
                    const data = {
                        "model": "tts-1",
                        "response_format": "mp3",
                        "input": text,
                        "voice": selectedVoice
                    };

                    // OpenAI TTS API 端点
                    const url = 'https://api.openai-hub.com/v1/audio/speech';

                    console.log('发送文本转语音请求：', data);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + openaiApiKey
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errData => {
                                throw new Error(errData.error.message || '网络响应异常 ' + response.statusText);
                            });
                        }
                        return response.arrayBuffer(); // 获取音频数据
                    })
                    .then(arrayBuffer => {
                        const blob = new Blob([arrayBuffer], { type: 'audio/mp3' });
                        const audioUrl = URL.createObjectURL(blob);
                        const audio = new Audio(audioUrl);
                        audio.play();
                        audio.onended = () => {
                            resolve(); // 语音播放结束，继续执行
                        };
                    })
                    .catch(error => {
                        console.error('文本转语音请求错误：', error);
                        reject(error);
                    });

                    // isSpeaking = false; // 标记正在进行语音合成
                });
            }

            // 显示消息（支持 Markdown）
            function displayMessage(sender, message, className) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${className}`;
                const senderStrong = document.createElement('strong');
                senderStrong.textContent = `${sender}: `;
                const messageContent = document.createElement('span');

                const rawHTML = marked.parse(message);
                const sanitizedHTML = DOMPurify.sanitize(rawHTML);
                messageContent.innerHTML = sanitizedHTML;
                messageDiv.appendChild(senderStrong);
                messageDiv.appendChild(messageContent);
                conversationDiv.appendChild(messageDiv);
                conversationDiv.scrollTop = conversationDiv.scrollHeight;

                // 在 MathJax 成功加载后调用
                if (window.MathJax && MathJax.typesetPromise) {
                    MathJax.typesetPromise([messageContent])
                        .catch((err) => console.error('MathJax 错误:', err)); // 捕捉 MathJax 渲染错误
                } else {
                    console.warn("MathJax 未完全加载，未调用 typesetPromise");
                }
            }
        });
    </script>
</body>
</html>