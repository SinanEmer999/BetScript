// BetScript Main JavaScript

window.BetScript = window.BetScript || {};

// Export showNotification globally
window.BetScript.showNotification = showNotification;

document.addEventListener('DOMContentLoaded', function() {
    console.log('BetScript loaded');
    
    // Handle flash messages
    showFlashMessages();
    
    // Handle form submissions
    setupFormHandlers();
});

// Update FIETZ Points display without reload
window.BetScript.updatePoints = async function() {
    try {
        const response = await fetch('/api/user/points');
        const data = await response.json();
        if (data.fietzPoints !== undefined) {
            const pointsElements = document.querySelectorAll('.points-value');
            pointsElements.forEach(el => {
                el.textContent = data.fietzPoints;
            });
        }
    } catch (error) {
        console.error('Failed to update points:', error);
    }
};

function showFlashMessages() {
    const params = new URLSearchParams(window.location.search);
    const success = params.get('success');
    const error = params.get('error');
    
    if (success) {
        showNotification(success, 'success');
    }
    if (error) {
        showNotification(error, 'error');
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? 'var(--success)' : 'var(--danger)'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function setupFormHandlers() {
    // Handle AJAX forms
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const data = {};
            
            // Manually build the data object from FormData
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            console.log('Form data being sent:', data); // Debug log
            
            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                });
                
                const result = await response.json();
                console.log('Server response:', result); // Debug log
                
                if (result.success) {
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        showNotification('Erfolg!', 'success');
                        form.reset();
                    }
                } else {
                    showNotification(result.error || 'Ein Fehler ist aufgetreten', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ein Fehler ist aufgetreten', 'error');
            }
        });
    });
}

// Animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Export utilities
window.BetScript = {
    showNotification,
};
