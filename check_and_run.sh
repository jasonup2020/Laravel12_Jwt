
#!/bin/bash

# 设置 Laravel 项目根目录（替换为实际路径）
PROJECT_DIR="/www/wwwroot/cmf_laravel12.sbimghk.com"

# 生成当日日志文件名
LOG_FILE="/www/wwwroot/cmf_laravel12.sbimghk.com/storage/logs/schedule-$(date +%Y-%m-%d).log"

# 切换到项目目录
cd "$PROJECT_DIR" || exit 1

# 检查是否有同名进程正在运行
if ! pgrep -f "php artisan schedule:run" > /dev/null; then
    # 执行调度命令并记录日志
    /www/server/php/83/bin artisan schedule:run >> "$LOG_FILE" 2>&1 &
fi
