document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('customization-toggle');
    const menu = document.getElementById('customization-menu');
    const themeOptions = document.querySelectorAll('.theme-option');
    
    const savedTheme = localStorage.getItem('customTheme') || 'dark';
    applyTheme(savedTheme);
    
    toggle.addEventListener('click', function() {
        menu.classList.toggle('active');
    });
    
    document.addEventListener('click', function(e) {
        if (!toggle.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('active');
        }
    });
    
    themeOptions.forEach(option => {
        option.addEventListener('click', function() {
            const theme = this.getAttribute('data-theme');
            applyTheme(theme);
            localStorage.setItem('customTheme', theme);
            themeOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
        });
        
        if (option.getAttribute('data-theme') === savedTheme) {
            option.classList.add('active');
        }
    });
});

function applyTheme(theme) {
    document.body.className = document.body.className.replace(/theme-\w+/g, '');
    document.body.classList.add(`theme-${theme}`);
    
    const root = document.documentElement;
    
    switch(theme) {
        case 'dark':
            root.style.setProperty('--primary-color', '#00eaff');
            root.style.setProperty('--secondary-color', '#ff00ea');
            root.style.setProperty('--bg-color', '#000000');
            root.style.setProperty('--text-color', '#ffffff');
            break;
        case 'light':
            root.style.setProperty('--primary-color', '#4A90E2');
            root.style.setProperty('--secondary-color', '#FFD700');
            root.style.setProperty('--bg-color', '#E6F3FF');
            root.style.setProperty('--text-color', '#1a3a52');
            root.style.setProperty('--neon-cyan', '#4A90E2');
            root.style.setProperty('--neon-pink', '#FFD700');
            root.style.setProperty('--light-sky', '#87CEEB');
            root.style.setProperty('--light-water', '#B0E0E6');
            root.style.setProperty('--light-quartz', '#E0F6FF');
            root.style.setProperty('--light-bg', '#F0F8FF');
            break;
        case 'purple':
            root.style.setProperty('--primary-color', '#a855f7');
            root.style.setProperty('--secondary-color', '#ec4899');
            root.style.setProperty('--bg-color', '#1a0a2e');
            root.style.setProperty('--text-color', '#f3e8ff');
            break;
        case 'green':
            root.style.setProperty('--primary-color', '#10b981');
            root.style.setProperty('--secondary-color', '#34d399');
            root.style.setProperty('--bg-color', '#064e3b');
            root.style.setProperty('--text-color', '#d1fae5');
            break;
        case 'orange':
            root.style.setProperty('--primary-color', '#f97316');
            root.style.setProperty('--secondary-color', '#fb923c');
            root.style.setProperty('--bg-color', '#7c2d12');
            root.style.setProperty('--text-color', '#ffedd5');
            break;
        case 'red':
            root.style.setProperty('--primary-color', '#ef4444');
            root.style.setProperty('--secondary-color', '#f87171');
            root.style.setProperty('--bg-color', '#7f1d1d');
            root.style.setProperty('--text-color', '#fee2e2');
            break;
    }
}

