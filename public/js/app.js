// 蒙太奇主JavaScript文件
document.addEventListener('DOMContentLoaded', function() {
    // 初始化CSRF令牌
    initCSRFToken();

    // 初始化桌面端下拉菜单
    initDesktopDropdowns();

    // 初始化移动端菜单
    initMobileMenu();

    // 初始化全局事件监听器
    initGlobalEventListeners();
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
 * 初始化桌面端下拉菜单
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

            // 点击按钮处理（仅对用户菜单按钮）
            if (button.tagName === 'BUTTON') {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isHidden = menu.classList.contains('hidden');
                    if (isHidden) {
                        showDropdownMenu(menu);
                    } else {
                        hideDropdownMenu(menu);
                    }
                });
            }
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
 * 初始化移动端菜单
 */
function initMobileMenu() {
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuButton && mobileMenu) {
        // 移动端菜单按钮点击事件
        mobileMenuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            mobileMenu.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        });

        // 初始化移动端子菜单
        initMobileSubmenus();
    }
}

/**
 * 初始化移动端子菜单
 */
function initMobileSubmenus() {
    const mobileMenuItems = document.querySelectorAll('.mobile-menu-item');

    mobileMenuItems.forEach(item => {
        const index = item.getAttribute('data-index');
        const submenuId = 'mobileSubmenu-' + index;
        const submenu = document.getElementById(submenuId);
        const icon = item.querySelector('.mobile-menu-icon');

        if (submenu) {
            // 主菜单项点击事件
            item.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                toggleMobileSubmenu(submenu, icon);
            });

            // 子菜单链接点击事件
            const submenuLinks = submenu.querySelectorAll('.mobile-submenu-link');
            submenuLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // 添加点击反馈
                    link.classList.add('bg-gray-100');
                    setTimeout(() => {
                        link.classList.remove('bg-gray-100');
                    }, 200);
                });
            });
        }
    });
}

/**
 * 切换移动端子菜单显示状态
 */
function toggleMobileSubmenu(submenu, icon) {
    const isHidden = submenu.classList.contains('hidden');

    // 关闭其他打开的子菜单
    const allSubmenus = document.querySelectorAll('[id^="mobileSubmenu-"]');
    const allIcons = document.querySelectorAll('.mobile-menu-icon');

    allSubmenus.forEach(sm => {
        if (sm !== submenu && !sm.classList.contains('hidden')) {
            sm.classList.add('hidden');
        }
    });

    allIcons.forEach(ic => {
        if (ic !== icon) {
            ic.classList.remove('rotate-90');
        }
    });

    // 切换当前子菜单
    submenu.classList.toggle('hidden');

    // 切换图标
    if (icon) {
        icon.classList.toggle('rotate-90');
    }
}

/**
 * 初始化全局事件监听器
 */
function initGlobalEventListeners() {
    // 点击其他地方关闭所有下拉菜单
    document.addEventListener('click', (e) => {
        // 关闭桌面端下拉菜单
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                hideDropdownMenu(menu);
            });
        }

        // 关闭移动端菜单
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuButton = document.getElementById('mobileMenuButton');

        if (mobileMenu && mobileMenuButton &&
            !e.target.closest('#mobileMenu') &&
            !e.target.closest('#mobileMenuButton')) {
            if (!mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
    });

    // ESC键关闭所有菜单
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // 关闭桌面端下拉菜单
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                hideDropdownMenu(menu);
            });

            // 关闭移动端菜单
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
    });

    // 窗口大小变化时重置菜单状态
    window.addEventListener('resize', () => {
        // 如果窗口从移动端切换到桌面端，关闭移动端菜单
        if (window.innerWidth >= 768) {
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
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

// 导出函数供其他脚本使用（如果需要）
if (typeof window !== 'undefined') {
    window.MontageUI = {
        showDropdownMenu,
        hideDropdownMenu,
        toggleMobileSubmenu
    };
}