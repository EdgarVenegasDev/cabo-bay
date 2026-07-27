// animations.js - Solo animaciones visuales
class CaboBayAnimations {
    constructor() {
        this.initScrollAnimations();
        this.initFormAnimations();
        this.initButtonEffects();
    }

    initScrollAnimations() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-visible');
                }
            });
        }, observerOptions);

        // Observar elementos con clase scroll-animate
        document.querySelectorAll('.scroll-animate').forEach(el => {
            observer.observe(el);
        });
    }

    initFormAnimations() {
        // Animaciones para inputs
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });

        // Loading states en botones submit
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                }
            });
        });
    }

    initButtonEffects() {
        // Efecto ripple en botones
        document.addEventListener('click', function(e) {
            if (e.target.matches('button:not(.no-ripple)')) {
                const btn = e.target;
                const ripple = document.createElement('span');
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size/2;
                const y = e.clientY - rect.top - size/2;

                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.4);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    pointer-events: none;
                `;

                btn.style.position = 'relative';
                btn.style.overflow = 'hidden';
                btn.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            }
        });

        // Añadir estilos CSS para ripple si no existen
        if (!document.querySelector('#ripple-styles')) {
            const style = document.createElement('style');
            style.id = 'ripple-styles';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                
                .spinner {
                    display: inline-block;
                    width: 20px;
                    height: 20px;
                    border: 2px solid rgba(255,255,255,0.3);
                    border-radius: 50%;
                    border-top-color: #fff;
                    animation: spin 1s linear infinite;
                    margin-right: 8px;
                    vertical-align: middle;
                }
                
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    createParticles(selector = '.hero-grid') {
        const heroSection = document.querySelector(selector);
        if (!heroSection) return;
        
        // Eliminar partículas existentes
        const existingParticles = document.querySelector('.particles');
        if (existingParticles) existingParticles.remove();
        
        const particleContainer = document.createElement('div');
        particleContainer.className = 'particles';
        particleContainer.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        `;
        
        heroSection.style.position = 'relative';
        heroSection.appendChild(particleContainer);
        
        // Crear partículas
        for (let i = 0; i < 15; i++) {
            const particle = document.createElement('div');
            const size = Math.random() * 8 + 3;
            
            particle.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                background: linear-gradient(135deg, 
                    rgba(37, 99, 235, 0.15), 
                    rgba(124, 58, 237, 0.15));
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation: float ${Math.random() * 15 + 10}s ease-in-out ${Math.random() * 5}s infinite;
                filter: blur(${size/2}px);
            `;
            
            particleContainer.appendChild(particle);
        }
        
        // Añadir animación float si no existe
        if (!document.querySelector('#float-animation')) {
            const floatStyle = document.createElement('style');
            floatStyle.id = 'float-animation';
            floatStyle.textContent = `
                @keyframes float {
                    0%, 100% { transform: translate(0, 0) rotate(0deg); }
                    33% { transform: translate(${Math.random() * 30 - 15}px, ${Math.random() * 30 - 15}px) rotate(${Math.random() * 10 - 5}deg); }
                    66% { transform: translate(${Math.random() * 30 - 15}px, ${Math.random() * 30 - 15}px) rotate(${Math.random() * 10 - 5}deg); }
                }
            `;
            document.head.appendChild(floatStyle);
        }
    }
}

// Inicializar animaciones
document.addEventListener('DOMContentLoaded', () => {
    window.caboBayAnimations = new CaboBayAnimations();
    
    // Crear partículas solo en hero section si existe
    if (document.querySelector('.hero-grid')) {
        window.caboBayAnimations.createParticles();
    }
});