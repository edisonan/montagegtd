const { contextBridge, ipcRenderer } = require('electron');

// 暴露安全的API给渲染进程
contextBridge.exposeInMainWorld('electronAPI', {
  // 配置管理
  getConfig: (key) => ipcRenderer.invoke('get-config', key),
  setConfig: (key, value) => ipcRenderer.invoke('set-config', key, value),
  
  // 用户认证
  getUserToken: () => ipcRenderer.invoke('get-user-token'),
  setUserToken: (token) => ipcRenderer.invoke('set-user-token', token),
  
  // 通知系统
  sendNotification: (data) => ipcRenderer.invoke('send-notification', data),
  
  // 监听主进程消息
  onStartPomodoro: (callback) => {
    ipcRenderer.on('start-pomodoro', callback);
    return () => ipcRenderer.removeListener('start-pomodoro', callback);
  },
  
  onNavigateToSettings: (callback) => {
    ipcRenderer.on('navigate-to-settings', callback);
    return () => ipcRenderer.removeListener('navigate-to-settings', callback);
  },
  
  // 悬浮窗相关
  onUpdateStatus: (callback) => {
    ipcRenderer.on('update-status', callback);
    return () => ipcRenderer.removeListener('update-status', callback);
  },
  
  onShowQuickAddModal: (callback) => {
    ipcRenderer.on('show-quick-add-modal', callback);
    return () => ipcRenderer.removeListener('show-quick-add-modal', callback);
  },
  
  onNavigateToView: (callback) => {
    ipcRenderer.on('navigate-to-view', callback);
    return () => ipcRenderer.removeListener('navigate-to-view', callback);
  },
  
  hideFloatingWindow: () => ipcRenderer.send('hide-floating-window'),
  closeFloatingWindow: () => ipcRenderer.send('close-floating-window'),
  startPomodoroFromFloating: () => ipcRenderer.send('start-pomodoro-from-floating'),
  showMainWindow: () => ipcRenderer.send('show-main-window'),
  showQuickAddTask: () => ipcRenderer.send('show-quick-add-task'),
  navigateToTasks: () => ipcRenderer.send('navigate-to-tasks'),
  requestFloatingStatus: () => ipcRenderer.send('request-floating-status'),
  
  // 应用控制
  minimizeWindow: () => ipcRenderer.send('minimize-window'),
  maximizeWindow: () => ipcRenderer.send('maximize-window'),
  closeWindow: () => ipcRenderer.send('close-window')
});

// 版本信息
contextBridge.exposeInMainWorld('versions', {
  node: () => process.versions.node,
  chrome: () => process.versions.chrome,
  electron: () => process.versions.electron
});
