// Avatar Rendering System
window.BetScript = window.BetScript || {};

window.BetScript.renderAvatar = function(containerId, avatar, cosmetics = []) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = '';
    container.style.position = 'relative';
    container.style.width = '200px';
    container.style.height = '200px';
    
    // Background
    if (avatar.background && cosmetics.includes(avatar.background)) {
        const bg = document.createElement('img');
        bg.src = `/assets/images/cosmetics/${avatar.background}.svg`;
        bg.style.position = 'absolute';
        bg.style.width = '100%';
        bg.style.height = '100%';
        bg.style.objectFit = 'cover';
        bg.style.borderRadius = '12px';
        container.appendChild(bg);
    } else {
        container.style.background = 'linear-gradient(135deg, #1a2c38 0%, #0f212e 100%)';
        container.style.borderRadius = '12px';
    }
    
    // Base Avatar Circle
    const base = document.createElement('div');
    base.style.position = 'absolute';
    base.style.width = '100px';
    base.style.height = '100px';
    base.style.borderRadius = '50%';
    base.style.background = 'var(--bg-tertiary)';
    base.style.border = '3px solid var(--accent-primary)';
    base.style.left = '50%';
    base.style.top = '50%';
    base.style.transform = 'translate(-50%, -50%)';
    base.style.display = 'flex';
    base.style.alignItems = 'center';
    base.style.justifyContent = 'center';
    base.style.fontSize = '48px';
    base.textContent = '👤';
    container.appendChild(base);
    
    // Hat
    if (avatar.hat && cosmetics.includes(avatar.hat)) {
        const hat = document.createElement('img');
        hat.src = `/assets/images/cosmetics/${avatar.hat}.svg`;
        hat.style.position = 'absolute';
        hat.style.width = '80px';
        hat.style.height = '80px';
        hat.style.left = '50%';
        hat.style.top = '15%';
        hat.style.transform = 'translateX(-50%)';
        container.appendChild(hat);
    }
    
    // Glasses
    if (avatar.glasses && cosmetics.includes(avatar.glasses)) {
        const glasses = document.createElement('img');
        glasses.src = `/assets/images/cosmetics/${avatar.glasses}.svg`;
        glasses.style.position = 'absolute';
        glasses.style.width = '70px';
        glasses.style.height = '70px';
        glasses.style.left = '50%';
        glasses.style.top = '50%';
        glasses.style.transform = 'translate(-50%, -50%)';
        container.appendChild(glasses);
    }
    
    // Badge
    if (avatar.badge && cosmetics.includes(avatar.badge)) {
        const badge = document.createElement('img');
        badge.src = `/assets/images/cosmetics/${avatar.badge}.svg`;
        badge.style.position = 'absolute';
        badge.style.width = '50px';
        badge.style.height = '50px';
        badge.style.right = '10px';
        badge.style.bottom = '10px';
        container.appendChild(badge);
    }
    
    // Frame (outside everything)
    if (avatar.frame && cosmetics.includes(avatar.frame)) {
        const frame = document.createElement('img');
        frame.src = `/assets/images/cosmetics/${avatar.frame}.svg`;
        frame.style.position = 'absolute';
        frame.style.width = '100%';
        frame.style.height = '100%';
        frame.style.left = '0';
        frame.style.top = '0';
        frame.style.pointerEvents = 'none';
        container.appendChild(frame);
    }
};
