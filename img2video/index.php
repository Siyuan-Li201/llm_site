<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>视频生成</title>
    <!-- 优化样式 -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f5;
            margin: 0;
            padding: 20px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .header h1 {
            color: #333;
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .input-section label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .input-section input,
        .input-section select,
        .input-section button {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .input-section button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .input-section button:hover {
            background-color: #45a049;
        }

        #task-list {
            margin-top: 20px;
        }

        .task-item {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .task-status {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>

    <!-- 动态背景画布 -->
    <canvas id="dynamic-background"></canvas>

    <div class="container">
        <!-- 标题区域 -->
        <div class="header">
            <h1>视频生成器</h1>
        </div>

        <!-- 输入区域 -->
        <div class="input-section">
            <form id="video-form" enctype="multipart/form-data">
                <label for="model">选择模型：</label>
                <select name="model" id="model">
                    <option value="luma">Luma</option>
                    <option value="keling">可灵</option> <!-- 添加可灵选项 -->
                    <option value="runway">GEN3</option> <!-- 添加 runway 选项 -->
                </select>

                <label for="api_key">API Key：</label>
                <input type="text" name="api_key" id="api_key" required>

                <label for="prompt">Prompt：</label>
                <input type="text" name="prompt" id="prompt" required>

                <label for="image">上传图片：</label>
                <input type="file" name="image" id="image" accept="image/*" required>

                <button type="submit">生成</button>
            </form>
        </div>

        <!-- 任务列表区域 -->
        <div id="task-list">
            <h2>任务列表</h2>
        </div>
    </div>

    <!-- 引入 jQuery 库 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- 您需要根据参考页面，添加相应的动态背景脚本 -->
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


    <!-- 表单提交与任务管理脚本 -->
    <script>
        $(document).ready(function() {
            // 表单提交
            $('#video-form').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                // 生成一个临时的 task_id，用于在任务列表中显示
                var tempTaskId = 'temp_' + Date.now();

                // 获取用户输入的 prompt 和模型
                var prompt = $('#prompt').val();
                var model = $('#model').val();

                // 在任务列表中添加新任务（状态为“正在提交任务...”）
                addTask(tempTaskId, prompt, model, true); // 第四个参数表示这是一个临时任务

                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(data) {
                        if (data.success && data.task_id) {
                            // 更新任务项的 ID，将临时 ID 替换为实际的 task_id
                            $('#task-' + tempTaskId).attr('id', 'task-' + data.task_id);
                            $('#task-' + data.task_id + ' .task-id').text(data.task_id);
                            $('#task-' + data.task_id + ' .task-status').html('<strong>状态：</strong> 处理中...');

                            // 开始检查任务状态
                            checkTaskStatus(data.task_id);
                        } else {
                            // 更新任务状态为失败
                            $('#task-' + tempTaskId + ' .task-status').html('<strong>状态：</strong> 任务提交失败');
                            if (data.message) {
                                $('#task-' + tempTaskId + ' .task-result').html(data.message);
                            }
                        }
                    },
                    error: function() {
                        // 更新任务状态为失败
                        $('#task-' + tempTaskId + ' .task-status').html('<strong>状态：</strong> 请求失败，请稍后重试。');
                    }
                });
            });

            // 添加任务到任务列表
            function addTask(task_id, prompt, model, isTemporary = false) {
                var taskItem = `
                    <div class="task-item" id="task-${task_id}">
                        <p><strong>任务ID：</strong> <span class="task-id">${isTemporary ? '正在获取任务ID...' : task_id}</span></p>
                        <p><strong>模型：</strong> ${model}</p>
                        <p><strong>Prompt：</strong> ${prompt}</p>
                        <p class="task-status"><strong>状态：</strong> ${isTemporary ? '正在提交任务...' : '处理中...'}</p>
                        <p class="task-result"></p>
                    </div>
                `;
                $('#task-list').append(taskItem);
            }

           // 检查任务状态
            function checkTaskStatus(task_id) {
                var interval = setInterval(function() {
                    $.ajax({
                        url: 'check_status.php',
                        type: 'GET',
                        data: { task_id: task_id },
                        dataType: 'json',
                        success: function(data) {
                            if (data.status === 'completed') {
                                clearInterval(interval);
                                $('#task-' + task_id + ' .task-status').html('<strong>状态：</strong> 已完成');
                                $('#task-' + task_id + ' .task-result').html('<a href="' + data.video_url + '" download>下载视频</a>');
                            } else if (data.status === 'failed') {
                                clearInterval(interval);
                                $('#task-' + task_id + ' .task-status').html('<strong>状态：</strong> 失败');
                                if (data.message) {
                                    $('#task-' + task_id + ' .task-result').html(data.message);
                                }
                            } else if (data.status === 'processing' || data.status === 'pending' || data.status === 'in_progress' || data.status === 'submitted' || data.status === 'running') {
                                $('#task-' + task_id + ' .task-status').html('<strong>状态：</strong> 处理中...');
                            } else {
                                $('#task-' + task_id + ' .task-status').html('<strong>状态：</strong> ' + data.status);
                            }
                        },
                        error: function() {
                            clearInterval(interval);
                            $('#task-' + task_id + ' .task-status').html('<strong>状态：</strong> 检查失败');
                        }
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>