// 蒙太奇主JavaScript文件
(function (global) {
    'use strict';

    /* ================================================================
       移动端菜单：事件委托 —— app.js 一执行即绑定，不依赖 DOMContentLoaded
       ----------------------------------------------------------------
       背景：PWA/手机端每次进入都是全新页面加载；本页 HTML 尾部常驻大量
       同步内联脚本（布局引导脚本 + 各页面数千行内联业务 JS），而汉堡按钮
       的旧实现要等 DOMContentLoaded（＝整个文档解析完成）才绑定事件，
       于是出现「菜单已可见、但点击无反应，必须等页面加载/请求完成才恢复」
       的窗口期。
       修复：在 document 上事件委托。app.js 在 body 末尾执行时汉堡按钮
       必定已存在于 DOM，监听器立即生效；且委托不依赖目标节点在绑定瞬间
       存在，后续 DOM 变化也不会丢事件。
       ================================================================ */
    /* ================================================================
       触屏设备识别（iPad 等触屏平板/大屏手机）：
       纯触屏且无法悬停时，宽屏（≥768px）会命中 PC 版悬停菜单，
       而触屏没有 hover，导致"识别成 PC 版菜单、子菜单点不开"。
       解决方案：识别为触屏设备的，强制启用移动端汉堡菜单（配合
       app.blade.php 中的 CSS：隐藏 .desktop-nav-wrap、显示汉堡按钮）。
       iPad 接入鼠标/触控板时 hover:hover 命中，保持桌面菜单不变。
       ================================================================ */
    function detectTouchDevice() {
        try {
            if (window.matchMedia) {
                if (window.matchMedia('(hover: none)').matches) { return true; }
                if (window.matchMedia('(hover: hover)').matches) { return false; }
            }
        } catch (e) { /* 忽略，走下方兜底逻辑 */ }
        /* 兜底：老浏览器不支持 hover 媒体查询时，有触屏即视为触屏设备 */
        return ('ontouchstart' in window) || (navigator.maxTouchPoints || 0) > 0;
    }

    var IS_TOUCH_DEVICE = detectTouchDevice();

    if (IS_TOUCH_DEVICE) {
        document.documentElement.classList.add('touch-device');
    }

    var mobileMenuButton = null;
    var mobileMenu = null;

    function cacheMobileNodes() {
        if (!mobileMenuButton) {
            mobileMenuButton = document.getElementById('mobileMenuButton');
        }
        if (!mobileMenu) {
            mobileMenu = document.getElementById('mobileMenu');
        }
    }

    function openMobileMenu() {
        cacheMobileNodes();
        if (!mobileMenu) { return; }
        mobileMenu.classList.remove('hidden');
        /* 触屏宽屏设备（iPad）上 #mobileMenu 仍带 md:hidden 类，需内联样式确保展开 */
        mobileMenu.style.display = 'block';
        document.body.classList.add('overflow-hidden');
    }

    function closeMobileMenu() {
        cacheMobileNodes();
        if (!mobileMenu) { return; }
        mobileMenu.classList.add('hidden');
        mobileMenu.style.display = 'none';
        document.body.classList.remove('overflow-hidden');
    }

    function toggleMobileMenu() {
        cacheMobileNodes();
        if (!mobileMenu) { return; }
        if (mobileMenu.classList.contains('hidden')) {
            openMobileMenu();
        } else {
            closeMobileMenu();
        }
    }

    /* 核心子菜单切换：关闭其它已展开的子菜单，再切换当前子菜单 */
    function toggleMobileSubmenuCore(submenu, icon) {
        if (!submenu) { return; }

        var allSubmenus = document.querySelectorAll('[id^="mobileSubmenu-"]');
        var allIcons = document.querySelectorAll('.mobile-menu-icon');

        allSubmenus.forEach(function (sm) {
            if (sm !== submenu && !sm.classList.contains('hidden')) {
                sm.classList.add('hidden');
            }
        });
        allIcons.forEach(function (ic) {
            if (ic !== icon && ic.classList.contains('rotate-90')) {
                ic.classList.remove('rotate-90');
            }
        });

        submenu.classList.toggle('hidden');
        if (icon) {
            icon.classList.toggle('rotate-90');
        }
    }

    /* 委托路径：由 .mobile-menu-item 反查子菜单与图标再切换 */
    function toggleMobileSubmenuByItem(item) {
        var index = item.getAttribute('data-index');
        var submenu = document.getElementById('mobileSubmenu-' + index);
        var icon = item.querySelector('.mobile-menu-icon');
        toggleMobileSubmenuCore(submenu, icon);
    }

    /* ---------- 移动端菜单全局委托（点击即生效） ---------- */
    document.addEventListener('click', function (e) {
        var target = e.target;
        if (!target || typeof target.closest !== 'function') { return; }
        cacheMobileNodes();

        /* 汉堡按钮：开合移动端菜单 */
        if (target.closest('#mobileMenuButton')) {
            e.stopPropagation();
            toggleMobileMenu();
            return;
        }

        /* 移动端菜单处于展开状态时才处理内部/外部点击 */
        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
            /* 主菜单项：展开/收起子菜单 */
            var item = target.closest('.mobile-menu-item');
            if (item) {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileSubmenuByItem(item);
                return;
            }

            /* 子菜单链接：点击反馈（不阻止默认跳转） */
            var subLink = target.closest('.mobile-submenu-link');
            if (subLink) {
                e.stopPropagation();
                subLink.classList.add('bg-gray-100');
                setTimeout(function () {
                    subLink.classList.remove('bg-gray-100');
                }, 200);
                return;
            }

            /* 点击菜单外部：收起移动端菜单 */
            if (!target.closest('#mobileMenu') && !target.closest('#mobileMenuButton')) {
                closeMobileMenu();
            }
        }
    }, false);

    /* ================================================================
       桌面端下拉菜单：BUTTON 型触发器点击开合（事件委托，同样提前生效）
       悬停展开逻辑仍在 DOMContentLoaded 中初始化（见 initDesktopDropdowns）
       ================================================================ */
    document.addEventListener('click', function (e) {
        var target = e.target;
        if (!target || typeof target.closest !== 'function') { return; }

        var dropdown = target.closest('.dropdown');
        if (!dropdown) { return; }

        var button = dropdown.querySelector('a, button');
        var menu = dropdown.querySelector('.dropdown-menu');
        if (!button || !menu || button.tagName !== 'BUTTON') { return; }

        /* 点击菜单内部（如通知项/用户菜单项）不切换 */
        if (target.closest('.dropdown-menu')) { return; }

        e.stopPropagation();
        if (menu.classList.contains('hidden')) {
            showDropdownMenu(menu);
        } else {
            hideDropdownMenu(menu);
        }
    }, false);

    /****************************************************************
     * DOMContentLoaded 后初始化（非关键交互路径；每步独立 try/catch，
     * 避免任一初始化异常导致后续初始化被跳过）
     ****************************************************************/
    document.addEventListener('DOMContentLoaded', function () {
        try { initCSRFToken(); } catch (err) { console.error('initCSRFToken error', err); }
        try { initDesktopDropdowns(); } catch (err) { console.error('initDesktopDropdowns error', err); }
        try { initGlobalEventListeners(); } catch (err) { console.error('initGlobalEventListeners error', err); }
    });

    /**
     * 初始化CSRF令牌
     */
    function initCSRFToken() {
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
    }

    /**
     * 初始化桌面端下拉菜单（悬停展开；BUTTON 点击开合已由上方委托处理）
     */
    function initDesktopDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown');

        dropdowns.forEach(dropdown => {
            const button = dropdown.querySelector('a, button');
            const menu = dropdown.querySelector('.dropdown-menu');

            if (button && menu) {
                // 鼠标悬停显示菜单
                dropdown.addEventListener('mouseenter', () => {
                    showDropdownMenu(menu);
                });

                dropdown.addEventListener('mouseleave', () => {
                    hideDropdownMenu(menu);
                });
            }
        });
    }

    /**
     * 显示下拉菜单
     */
    function showDropdownMenu(menu) {
        menu.classList.remove('hidden');
        setTimeout(() => {
            menu.classList.add('show');
        }, 10);
    }

    /**
     * 隐藏下拉菜单
     */
    function hideDropdownMenu(menu) {
        menu.classList.remove('show');
        setTimeout(() => {
            if (!menu.classList.contains('show')) {
                menu.classList.add('hidden');
            }
        }, 200);
    }

    /**
     * 初始化全局事件监听器
     * 说明：移动端菜单的开合已由顶部事件委托统一处理，
     *       此处保留桌面下拉收起、ESC、窗口尺寸变化等逻辑。
     */
    function initGlobalEventListeners() {
        // 点击其他地方关闭桌面端下拉菜单
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    hideDropdownMenu(menu);
                });
            }
        });

        // ESC键关闭菜单
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // 关闭桌面端下拉菜单
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    hideDropdownMenu(menu);
                });

                // 关闭移动端菜单
                const mobileMenuEl = document.getElementById('mobileMenu');
                if (mobileMenuEl && !mobileMenuEl.classList.contains('hidden')) {
                    mobileMenuEl.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            }
        });

        // 窗口大小变化时重置菜单状态
        window.addEventListener('resize', () => {
            // 如果窗口从移动端切换到桌面端，关闭移动端菜单
            if (window.innerWidth >= 768) {
                const mobileMenuEl = document.getElementById('mobileMenu');
                if (mobileMenuEl && !mobileMenuEl.classList.contains('hidden')) {
                    mobileMenuEl.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }

                // 关闭所有子菜单
                const allSubmenus = document.querySelectorAll('[id^="mobileSubmenu-"]');
                const allIcons = document.querySelectorAll('.mobile-menu-icon');

                allSubmenus.forEach(sm => {
                    sm.classList.add('hidden');
                });

                allIcons.forEach(ic => {
                    ic.classList.remove('rotate-90');
                });
            }
        });
    }

    /**
     * 添加页面加载动画
     */
    function addPageTransition() {
        // 添加淡入效果到主要内容
        const mainContent = document.querySelector('main');
        if (mainContent) {
            mainContent.classList.add('fade-in');
        }
    }

    // 页面加载完成后添加过渡效果
    window.addEventListener('load', addPageTransition);

    // 导出函数供其他脚本使用（如果需要）——保持旧签名兼容
    window.MontageUI = {
        showDropdownMenu,
        hideDropdownMenu,
        toggleMobileSubmenu: toggleMobileSubmenuCore,
        toggleMobileMenu
    };
})(window);