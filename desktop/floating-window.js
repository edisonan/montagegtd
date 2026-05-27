const { BrowserWindow, ipcMain } = require('electron');
const path = require('path');

class FloatingWindow {
    constructor(mainWindow) {
        this.mainWindow = mainWindow;
        this.window = null;
        this.isVisible = false;
        this.initialize();
    }

    initialize() {
        // 监听主窗口的最小化事件
        this.mainWindow.on('minimize', () => {
            this.show();
        });

        this.mainWindow.on('restore', () => {
            this.hide();
        });

        this.mainWindow.on('show', () => {
            this.hide();
        });

        // 监听来自渲染进程的状态更新
        ipcMain.handle('update-floating-window', (event, data) => {
            this.updateDisplay(data);
        });
    }

    // 创建悬浮窗口
    createWindow() {
        if (this.window) {
            return;
        }

        this.window = new BrowserWindow({
            width: 280,
            height: 180,
            frame: false,
            transparent: true,
            alwaysOnTop: true,
            skipTaskbar: true,
            resizable: false,
            movable: true,
            webPreferences: {
                nodeIntegration: false,
                contextIsolation: true,
                preload: path.join(__dirname, 'preload.js')
            }
        });

        // 加载悬浮窗界面
        this.window.loadFile(path.join(__dirname, 'renderer/floating-window.html'));

        // 设置窗口位置（右下角）
        this.setPosition();

        // 窗口关闭时清理
        this.window.on('closed', () => {
            this.window = null;
            this.isVisible = false;
        });

        // 防止窗口失去焦点时关闭
        this.window.on('blur', () => {
            // 可以选择保持显示或隐藏
            // this.hide();
        });
    }

    // 显示悬浮窗
    show() {
        if (!this.window) {
            this.createWindow();
        }
        
        if (this.window) {
            this.window.show();
            this.isVisible = true;
            this.setPosition();
            
            // 请求最新状态
            if (this.mainWindow && !this.mainWindow.isDestroyed()) {
                this.mainWindow.webContents.send('request-floating-status');
            }
        }
    }

    // 隐藏悬浮窗
    hide() {
        if (this.window && this.isVisible) {
            this.window.hide();
            this.isVisible = false;
        }
    }

    // 切换显示/隐藏
    toggle() {
        if (this.isVisible) {
            this.hide();
        } else {
            this.show();
        }
    }

    // 设置窗口位置（右下角）
    setPosition() {
        if (!this.window) return;

        const { screen } = require('electron');
        const primaryDisplay = screen.getPrimaryDisplay();
        const { width: screenWidth, height: screenHeight } = primaryDisplay.workAreaSize;
        
        const windowBounds = this.window.getBounds();
        const x = screenWidth - windowBounds.width - 20; // 右边距20px
        const y = screenHeight - windowBounds.height - 20; // 下边距20px
        
        this.window.setPosition(x, y);
    }

    // 更新显示内容
    updateDisplay(data) {
        if (this.window && !this.window.isDestroyed()) {
            this.window.webContents.send('update-status', data);
        }
    }

    // 销毁窗口
    destroy() {
        if (this.window) {
            this.window.destroy();
            this.window = null;
            this.isVisible = false;
        }
    }
}

module.exports = FloatingWindow;
