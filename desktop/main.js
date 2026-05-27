const { app, BrowserWindow, Tray, Menu, nativeImage, ipcMain, Notification } = require('electron');
const path = require('path');
const Store = require('electron-store');
const FloatingWindow = require('./floating-window');
const ReminderManager = require('./reminder-manager');

// 初始化配置存储
const store = new Store({
  defaults: {
    apiBaseUrl: 'https://pretask.congcong.us/api/v2',
    userToken: null,
    windowState: {
      width: 1200,
      height: 800,
      x: null,
      y: null
    },
    settings: {
      autoStart: false,
      minimizeToTray: true,
      notifications: true,
      pomodoroSound: true,
      theme: 'light'
    }
  }
});

let mainWindow;
let tray = null;
let floatingWindow = null;
let reminderManager = null;
let isQuitting = false;

// 创建主窗口
function createWindow() {
  const windowState = store.get('windowState');
  
  mainWindow = new BrowserWindow({
    width: windowState.width || 1200,
    height: windowState.height || 800,
    x: windowState.x || undefined,
    y: windowState.y || undefined,
    minWidth: 800,
    minHeight: 600,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false,
      enableRemoteModule: false
    },
    icon: path.join(__dirname, 'assets/icons/icon.png'),
    titleBarStyle: process.platform === 'darwin' ? 'hiddenInset' : 'default',
    show: false
  });

  // 初始化悬浮窗
  floatingWindow = new FloatingWindow(mainWindow);
  
  // 初始化提醒管理器
  reminderManager = new ReminderManager();

  // 加载应用界面
  if (process.argv.includes('--dev')) {
    mainWindow.loadURL('http://localhost:3000');
    mainWindow.webContents.openDevTools();
  } else {
    mainWindow.loadFile(path.join(__dirname, 'renderer/index.html'));
  }

  // 窗口就绪后显示
  mainWindow.once('ready-to-show', () => {
    mainWindow.show();
  });

  // 保存窗口状态
  mainWindow.on('close', (event) => {
    if (!isQuitting && store.get('settings.minimizeToTray')) {
      event.preventDefault();
      mainWindow.hide();
      return false;
    }
    
    // 保存窗口位置和大小
    const bounds = mainWindow.getBounds();
    store.set('windowState', {
      width: bounds.width,
      height: bounds.height,
      x: bounds.x,
      y: bounds.y
    });
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });

  // 处理未捕获的异常
  mainWindow.webContents.on('crashed', () => {
    console.error('Window crashed!');
  });
  
  // 监听来自渲染进程的状态请求
  mainWindow.webContents.on('did-finish-load', () => {
    mainWindow.webContents.on('ipc-message', (event, channel, ...args) => {
      if (channel === 'request-floating-status') {
        sendFloatingWindowStatus();
      }
    });
  });
}

// 创建系统托盘
function createTray() {
  if (process.platform === 'linux') {
    return; // Linux下可选禁用托盘
  }

  const iconPath = path.join(__dirname, 'assets/icons/tray-icon.png');
  tray = new Tray(iconPath);

  const contextMenu = Menu.buildFromTemplate([
    {
      label: '显示主窗口',
      click: () => {
        if (mainWindow) {
          mainWindow.show();
          mainWindow.focus();
        }
      }
    },
    {
      label: '开始番茄钟',
      click: () => {
        mainWindow.webContents.send('start-pomodoro');
      }
    },
    { type: 'separator' },
    {
      label: '设置',
      click: () => {
        if (mainWindow) {
          mainWindow.show();
          mainWindow.webContents.send('navigate-to-settings');
        }
      }
    },
    { type: 'separator' },
    {
      label: '退出',
      click: () => {
        isQuitting = true;
        app.quit();
      }
    }
  ]);

  tray.setToolTip('Montage GTD - 高效生产力工具');
  tray.setContextMenu(contextMenu);

  // 双击托盘图标显示主窗口
  tray.on('double-click', () => {
    if (mainWindow) {
      if (mainWindow.isVisible()) {
        mainWindow.hide();
      } else {
        mainWindow.show();
        mainWindow.focus();
      }
    }
  });
}

// 创建应用菜单
function createMenu() {
  if (process.platform === 'darwin') {
    const template = [
      {
        label: 'Montage GTD',
        submenu: [
          { role: 'about' },
          { type: 'separator' },
          { role: 'services' },
          { type: 'separator' },
          { role: 'hide' },
          { role: 'hideothers' },
          { role: 'unhide' },
          { type: 'separator' },
          { role: 'quit' }
        ]
      },
      {
        label: '编辑',
        submenu: [
          { role: 'undo' },
          { role: 'redo' },
          { type: 'separator' },
          { role: 'cut' },
          { role: 'copy' },
          { role: 'paste' },
          { role: 'selectall' }
        ]
      },
      {
        label: '视图',
        submenu: [
          { role: 'reload' },
          { role: 'forcereload' },
          { role: 'toggledevtools' },
          { type: 'separator' },
          { role: 'resetzoom' },
          { role: 'zoomin' },
          { role: 'zoomout' },
          { type: 'separator' },
          { role: 'togglefullscreen' }
        ]
      },
      {
        label: '窗口',
        submenu: [
          { role: 'minimize' },
          { role: 'close' }
        ]
      }
    ];

    const menu = Menu.buildFromTemplate(template);
    Menu.setApplicationMenu(menu);
  } else {
    Menu.setApplicationMenu(null);
  }
}

// 发送桌面通知
function sendNotification(title, body, icon = null) {
  if (!store.get('settings.notifications')) {
    return;
  }

  const notification = new Notification({
    title: title,
    body: body,
    icon: icon || path.join(__dirname, 'assets/icons/notification-icon.png'),
    silent: !store.get('settings.pomodoroSound')
  });

  notification.show();
}

// 发送悬浮窗状态更新
function sendFloatingWindowStatus() {
  // 这里可以从番茄钟管理器获取实时状态
  const status = {
    timer: '25:00',
    status: '准备开始',
    taskCount: 0
  };
  
  if (floatingWindow) {
    floatingWindow.updateDisplay(status);
  }
}

// IPC通信处理
ipcMain.handle('get-config', (event, key) => {
  return store.get(key);
});

ipcMain.handle('set-config', (event, key, value) => {
  store.set(key, value);
  return true;
});

ipcMain.handle('send-notification', (event, { title, body, icon }) => {
  sendNotification(title, body, icon);
  return true;
});

ipcMain.handle('get-user-token', () => {
  return store.get('userToken');
});

ipcMain.handle('set-user-token', (event, token) => {
  store.set('userToken', token);
  return true;
});

// 悬浮窗相关IPC处理
ipcMain.on('hide-floating-window', () => {
  if (floatingWindow) {
    floatingWindow.hide();
  }
});

ipcMain.on('close-floating-window', () => {
  if (floatingWindow) {
    floatingWindow.destroy();
  }
});

ipcMain.on('start-pomodoro-from-floating', () => {
  if (mainWindow && !mainWindow.isDestroyed()) {
    mainWindow.show();
    mainWindow.focus();
    mainWindow.webContents.send('start-pomodoro');
  }
});

ipcMain.on('show-main-window', () => {
  if (mainWindow) {
    if (mainWindow.isMinimized()) {
      mainWindow.restore();
    }
    mainWindow.show();
    mainWindow.focus();
  }
});

ipcMain.on('show-quick-add-task', () => {
  if (mainWindow && !mainWindow.isDestroyed()) {
    mainWindow.show();
    mainWindow.focus();
    mainWindow.webContents.send('show-quick-add-modal');
  }
});

ipcMain.on('navigate-to-tasks', () => {
  if (mainWindow && !mainWindow.isDestroyed()) {
    mainWindow.webContents.send('navigate-to-view', 'tasks');
  }
});

ipcMain.on('request-floating-status', () => {
  sendFloatingWindowStatus();
});

// 应用生命周期管理
app.whenReady().then(() => {
  createMenu();
  createWindow();
  createTray();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    } else if (mainWindow) {
      mainWindow.show();
      mainWindow.focus();
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('before-quit', () => {
  isQuitting = true;
});

// 单实例锁定
const gotTheLock = app.requestSingleInstanceLock();
if (!gotTheLock) {
  app.quit();
} else {
  app.on('second-instance', (event, commandLine, workingDirectory) => {
    if (mainWindow) {
      if (mainWindow.isMinimized()) {
        mainWindow.restore();
      }
      mainWindow.show();
      mainWindow.focus();
    }
  });
}

// 导出函数供渲染进程使用
module.exports = {
  sendNotification,
  store
};
